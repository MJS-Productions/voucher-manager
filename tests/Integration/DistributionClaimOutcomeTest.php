<?php
/**
 * Framework-free Distribution claim outcome hardening test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Domain\Code\CodeRepository;
use VoucherManager\Domain\Distribution\DistributionService;
use VoucherManager\Domain\Import\ImportRecord;
use VoucherManager\Domain\Log\LogRepository;
use VoucherManager\Domain\Pool\Pool;
use VoucherManager\Domain\Pool\PoolRepository;

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( '_x' ) ) {
	function _x( string $text, string $context, string $domain = 'default' ): string {
		unset( $context, $domain );
		return $text;
	}
}

if ( ! function_exists( 'esc_html_x' ) ) {
	function esc_html_x( string $text, string $context, string $domain = 'default' ): string {
		unset( $context, $domain );
		return $text;
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
		throw new RuntimeException( 'Distribution claim outcome assertion failed: ' . $message );
	}
};

final class OutcomePoolRepository implements PoolRepository {
	public function all(): array { return array(); }
	public function find( int $id ): ?Pool {
		return 7 === $id ? new Pool( 7, 'Outcome', 'outcome', '', 5, 'active', '', '' ) : null;
	}
	public function create( string $name, string $description, int $warning_threshold, bool $active ): int { return 0; }
	public function update( int $id, string $name, string $description, int $warning_threshold, bool $active ): bool { return false; }
	public function set_active( int $id, bool $active ): bool { return false; }
	public function delete( int $id ): bool { return false; }
	public function code_count( int $id ): int { return 0; }
}

final class OutcomeCodeRepository implements CodeRepository {
	public bool $throw_count = false;
	public int $claims = 0;
	public function insert_batch( int $pool_id, int $import_id, array $codes ): int { return 0; }
	public function delete_available_by_import( int $import_id ): int { return 0; }
	public function count_assigned_by_import( int $import_id ): int { return 0; }
	public function claim_next_available( int $pool_id ): ?array {
		++$this->claims;
		return array( 'id' => 41, 'code' => 'KEEP-ME-SECRET' );
	}
	public function count_available( int $pool_id ): int {
		if ( $this->throw_count ) {
			throw new RuntimeException( 'Simulated remaining count failure with sensitive-looking details.' );
		}
		return 3;
	}
}

final class OutcomeLogRepository implements LogRepository {
	public bool $throw = false;
	public array $entries = array();
	public function add( string $event_type, string $message, array $context = array() ): void {
		if ( $this->throw ) {
			throw new RuntimeException( 'Simulated log failure with sensitive-looking details.' );
		}
		$this->entries[] = compact( 'event_type', 'message', 'context' );
	}
}

$pools = new OutcomePoolRepository();
$codes = new OutcomeCodeRepository();
$logs  = new OutcomeLogRepository();

$logs->throw = true;
$service     = new DistributionService( $pools, $codes, $logs );
$result      = $service->distribute( 7 );

$assert( $result->success(), 'Completed Activity failure must not turn a committed claim into failure.' );
$assert( 'KEEP-ME-SECRET' === $result->code(), 'Completed Activity failure must not hide the claimed voucher.' );
$assert( 3 === $result->remaining(), 'Known remaining inventory must still be returned when Activity logging fails.' );
$assert( 1 === $codes->claims, 'Logging failure must not retry or release the voucher claim.' );

$logs->throw       = false;
$codes->throw_count = true;
$result             = $service->distribute( 7 );

$assert( $result->success(), 'Remaining-count failure must not turn a committed claim into failure.' );
$assert( 'KEEP-ME-SECRET' === $result->code(), 'Remaining-count failure must not hide the claimed voucher.' );
$assert( null === $result->remaining(), 'Unknown remaining inventory must be represented explicitly as null.' );
$assert( 2 === $codes->claims, 'Remaining-count failure must not retry or release the voucher claim.' );

$view = new VoucherManager\Admin\DistributionViewModel();
$assert(
	'The One-Time Code was assigned successfully. Remaining inventory could not be refreshed.' === $view->remaining_message( null ),
	'Unknown remaining inventory must explain success without falsely reporting an empty Pool.'
);
$assert( 'warning' === $view->result_tone( null ), 'Unknown remaining inventory must use a cautionary result tone.' );

$service_source  = file_get_contents( $root . '/src/Domain/Distribution/DistributionService.php' );
$template_source = file_get_contents( $root . '/templates/admin/distribution.php' );
$admin_source    = file_get_contents( $root . '/src/Admin/DistributionAdmin.php' );
$repository      = file_get_contents( $root . '/src/Infrastructure/WordPress/WpdbCodeRepository.php' );
$composer        = file_get_contents( $root . '/composer.json' );
$plugin          = file_get_contents( $root . '/voucher-manager.php' );

$assert(
	is_string( $service_source )
	&& str_contains( $service_source, 'private function remaining_safely' )
	&& str_contains( $service_source, 'private function log_safely' )
	&& str_contains( $service_source, '$exception::class' )
	&& ! str_contains( $service_source, '$exception->getMessage()' ),
	'Post-claim failures must be contained with exception-class-only error reporting.'
);

$assert(
	! str_contains( $service_source, "'code' => \$claimed['code']" ),
	'Operational Activity context must never contain the raw voucher.'
);

$assert(
	is_string( $template_source )
	&& str_contains( $template_source, "isset( \$result['remaining'] ) ? absint( \$result['remaining'] ) : null" ),
	'One-time presentation must preserve unknown remaining inventory instead of coercing it to zero.'
);

$assert(
	is_string( $admin_source )
	&& str_contains( $admin_source, '$this->results->store( $intent_token, $user_id, $result, $pool_id )' )
	&& str_contains( $admin_source, "'remaining' => \$distribution_result->remaining()" ),
	'Distribution controller and result store boundary must preserve the nullable remaining state.'
);

$assert(
	is_string( $repository )
	&& str_contains( $repository, "'START TRANSACTION'" )
	&& str_contains( $repository, 'FOR UPDATE' )
	&& str_contains( $repository, "'ROLLBACK'" )
	&& str_contains( $repository, "'COMMIT'" ),
	'Claim outcome hardening must retain the atomic database claim boundary.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:distribution-claim-outcome' )
	&& strpos( $composer, '@test:distribution-claim-outcome' ) < strpos( $composer, '@build' ),
	'Distribution Claim Outcome coverage must run before build.'
);

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Claim outcome hardening must not introduce a database migration.'
);

echo "Distribution claim outcome OK: committed claims survive metadata and Activity failures without voucher leakage.\n";
