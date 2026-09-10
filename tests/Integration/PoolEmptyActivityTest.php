<?php
/**
 * Pool-empty Activity and extension-safe distribution regression test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Admin\DashboardViewModel;
use VoucherManager\Domain\Code\CodeRepository;
use VoucherManager\Domain\Distribution\DistributionService;
use VoucherManager\Domain\Import\ImportRecord;
use VoucherManager\Domain\Log\LogRepository;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Pool\Pool;
use VoucherManager\Domain\Pool\PoolRepository;

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( mixed $value ): int {
		return abs( (int) $value );
	}
}

$root = dirname( __DIR__, 2 );

spl_autoload_register(
	static function ( string $class ) use ( $root ): void {
		$prefix = 'VoucherManager\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}
		$file = $root . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Pool-empty Activity assertion failed: ' . $message );
	}
};

final class PoolEmptyPoolRepository implements PoolRepository {
	public function all(): array { return array(); }
	public function find( int $id ): ?Pool {
		return 7 === $id ? new Pool( 7, 'Issue 20 Pool', 'issue-20-pool', '', 5, 'active', '', '' ) : null;
	}
	public function create( string $name, string $description, int $warning_threshold, bool $active ): int { return 0; }
	public function update( int $id, string $name, string $description, int $warning_threshold, bool $active ): bool { return false; }
	public function set_active( int $id, bool $active ): bool { return false; }
	public function delete( int $id ): bool { return false; }
	public function code_count( int $id ): int { return 0; }
}

final class PoolEmptyCodeRepository implements CodeRepository {
	/** @var array<int,array{id:int,code:string}> */
	private array $available = array();
	private int $next_id = 1;

	public function refill( string $code ): void {
		$this->available[] = array( 'id' => $this->next_id++, 'code' => $code );
	}

	public function insert_batch( int $pool_id, int $import_id, array $codes ): int { return 0; }
	public function delete_available_by_import( int $import_id ): int { return 0; }
	public function count_assigned_by_import( int $import_id ): int { return 0; }
	public function claim_next_available( int $pool_id ): ?array {
		$claimed = array_shift( $this->available );
		if ( null === $claimed ) {
			return null;
		}
		$claimed['emptied_pool'] = array() === $this->available;
		return $claimed;
	}
	public function count_available( int $pool_id ): int {
		return count( $this->available );
	}
}

final class PoolEmptyLogRepository implements LogRepository {
	/** @var array<int,array{event_type:string,message:string,context:array<string,mixed>}> */
	public array $entries = array();

	public function add( string $event_type, string $message, array $context = array() ): void {
		$this->entries[] = compact( 'event_type', 'message', 'context' );
	}

	public function clear(): void {
		$this->entries = array();
	}

	public function count_event( string $event_type ): int {
		return count(
			array_filter(
				$this->entries,
				static fn( array $entry ): bool => $event_type === $entry['event_type']
			)
		);
	}
}

$pools   = new PoolEmptyPoolRepository();
$codes   = new PoolEmptyCodeRepository();
$logs    = new PoolEmptyLogRepository();
$service = new DistributionService( $pools, $codes, $logs );

$normal_empty = $service->distribute( 7 );
$assert( ! $normal_empty->success(), 'The normal empty-Pool result must remain a failed distribution result.' );
$assert(
	1 === $logs->count_event( OperationalEvent::DISTRIBUTION_EMPTY->value ),
	'The normal distribution path must preserve existing distribution.empty Activity.'
);

$logs->clear();
$expected_empty = $service->distribute( 7, false );
$assert( ! $expected_empty->success(), 'The extension-safe empty-Pool result must still report no code.' );
$assert(
	0 === $logs->count_event( OperationalEvent::DISTRIBUTION_EMPTY->value ),
	'Expected empty-Pool results must be available without distribution.empty Activity.'
);

$codes->refill( 'FIRST-OF-TWO' );
$codes->refill( 'LAST-CODE-ONE' );
$logs->clear();
$first_code = $service->distribute( 7 );
$assert( $first_code->success(), 'A non-last available One-Time Code must still be distributed successfully.' );
$assert(
	0 === $logs->count_event( OperationalEvent::POOL_BECAME_EMPTY->value ),
	'Consuming a non-last available One-Time Code must not record a Pool-empty transition.'
);

$last_code = $service->distribute( 7 );
$assert( $last_code->success(), 'The last available One-Time Code must still be distributed successfully.' );
$assert( 0 === $last_code->remaining(), 'The last-code distribution must report zero remaining inventory.' );
$assert(
	1 === $logs->count_event( OperationalEvent::POOL_BECAME_EMPTY->value ),
	'Consuming the last available One-Time Code must record the Pool-empty transition once.'
);

$pool_empty_entries = array_values(
	array_filter(
		$logs->entries,
		static fn( array $entry ): bool => OperationalEvent::POOL_BECAME_EMPTY->value === $entry['event_type']
	)
);
$assert( 7 === ( $pool_empty_entries[0]['context']['pool_id'] ?? 0 ), 'Pool-empty Activity must contain the Pool ID.' );
$assert( 'Issue 20 Pool' === ( $pool_empty_entries[0]['context']['pool_name'] ?? '' ), 'Pool-empty Activity must contain the Pool name.' );
$assert( ! array_key_exists( 'code', $pool_empty_entries[0]['context'] ), 'Pool-empty Activity must never contain the One-Time Code.' );

$service->distribute( 7 );
$assert(
	1 === $logs->count_event( OperationalEvent::POOL_BECAME_EMPTY->value ),
	'Requests while the Pool remains empty must not repeat the Pool-empty transition.'
);

$codes->refill( 'LAST-CODE-TWO' );
$service->distribute( 7 );
$assert(
	2 === $logs->count_event( OperationalEvent::POOL_BECAME_EMPTY->value ),
	'After refill, a later transition to zero must be recorded again.'
);

$view = new DashboardViewModel();
$assert( 'Pool became empty' === $view->activity_label( 'pool.became_empty' ), 'The Activity label must use the approved wording.' );
$assert(
	'Pool: Issue 20 Pool' === $view->activity_detail(
		'pool.became_empty',
		array( 'pool_id' => 7, 'pool_name' => 'Issue 20 Pool' )
	),
	'Pool-empty Activity details must identify the Pool by name.'
);

$api_source = file_get_contents( $root . '/src/Extension/DistributionApi.php' );
$assert(
	is_string( $api_source )
	&& str_contains( $api_source, 'public function distribute_with_expected_empty_result( int $pool_id ): DistributionResult' )
	&& str_contains( $api_source, '$this->service->distribute( $pool_id, false )' ),
	'The supported extension API must expose an explicit expected-empty distribution path.'
);
$assert(
	! str_contains( $api_source, 'claim_next_available' )
	&& ! str_contains( $api_source, '$wpdb' ),
	'The extension-safe path must not duplicate claim logic or bypass Voucher Manager repositories.'
);

$composer = file_get_contents( $root . '/composer.json' );
$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:pool-empty-activity": "php tests/Integration/PoolEmptyActivityTest.php"' )
	&& str_contains( $composer, '"@test:pool-empty-activity"' ),
	'Pool-empty regression coverage must be registered in the quality gate.'
);

echo "Pool-empty Activity OK: transition semantics and expected-empty extension behavior are protected.\n";
