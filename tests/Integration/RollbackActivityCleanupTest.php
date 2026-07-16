<?php
/**
 * Framework-free VM-018 rollback Activity cleanup test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Domain\Code\CodeRepository;
use VoucherManager\Domain\Import\ImportRecord;
use VoucherManager\Domain\Import\ImportRepository;
use VoucherManager\Domain\Import\ImportService;
use VoucherManager\Domain\Log\LogRepository;
use VoucherManager\Support\CodeFileParser;

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
		throw new RuntimeException( 'Rollback Activity cleanup assertion failed: ' . $message );
	}
};

$imports = new class() implements ImportRepository {
	public bool $rolled_back = false;

	public function start( int $pool_id, string $filename, string $file_type ): int { return 1; }
	public function complete( int $id, int $total, int $imported, int $skipped, int $invalid ): bool { return true; }
	public function fail( int $id, int $total, int $imported, int $skipped, int $invalid ): bool { return true; }
	public function recent( int $limit = 20 ): array { return array(); }
	public function find( int $id ): ?ImportRecord { return null; }
	public function mark_rolled_back( int $id ): bool {
		$this->rolled_back = true;
		return true;
	}
};

$codes = new class() implements CodeRepository {
	public int $assigned = 1;
	public int $delete_calls = 0;

	public function insert_batch( int $pool_id, int $import_id, array $codes ): int { return 0; }
	public function delete_available_by_import( int $import_id ): int {
		++$this->delete_calls;
		return 3;
	}
	public function count_assigned_by_import( int $import_id ): int { return $this->assigned; }
	public function claim_next_available( int $pool_id ): ?array { return null; }
	public function count_available( int $pool_id ): int { return 0; }
};

$logs = new class() implements LogRepository {
	/** @var array<int,array{event_type:string,message:string,context:array<string,mixed>}> */
	public array $entries = array();

	public function add( string $event_type, string $message, array $context = array() ): void {
		$this->entries[] = compact( 'event_type', 'message', 'context' );
	}
};

$service = new ImportService( $imports, $codes, $logs, new CodeFileParser() );

$blocked = $service->rollback( 5 );

$assert( false === $blocked, 'Assigned codes must produce a controlled blocked result.' );
$assert( 0 === $codes->delete_calls, 'Blocked rollback must not delete available codes.' );
$assert( ! $imports->rolled_back, 'Blocked rollback must not change the import status.' );
$assert( 1 === count( $logs->entries ), 'Blocked rollback must write exactly one Activity event.' );
$assert( 'import.rollback_blocked' === $logs->entries[0]['event_type'], 'The single event must be import.rollback_blocked.' );
$assert(
	! in_array( 'admin.action_failed', array_column( $logs->entries, 'event_type' ), true ),
	'Expected rollback protection must never emit admin.action_failed.'
);

$codes->assigned = 0;
$deleted         = $service->rollback( 5 );

$assert( 3 === $deleted, 'Allowed rollback must return the deleted available-code count.' );
$assert( 1 === $codes->delete_calls, 'Allowed rollback must perform exactly one scoped deletion.' );
$assert( $imports->rolled_back, 'Allowed rollback must mark the import rolled back.' );
$assert( 'import.rolled_back' === $logs->entries[1]['event_type'], 'Allowed rollback must record import.rolled_back.' );

$service_source = file_get_contents( $root . '/src/Domain/Import/ImportService.php' );
$admin_source   = file_get_contents( $root . '/src/Admin/ImportAdmin.php' );
$composer       = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $service_source )
	&& str_contains( $service_source, 'public function rollback( int $import_id ): int|false' )
	&& str_contains( $service_source, 'OperationalEvent::IMPORT_ROLLBACK_BLOCKED->value' )
	&& str_contains( $service_source, 'return false;' )
	&& ! str_contains( $service_source, "throw new RuntimeException( 'Assigned codes prevent rollback.' )" ),
	'Expected rollback protection must be represented as a domain result rather than an exception.'
);

$assert(
	is_string( $admin_source )
	&& str_contains( $admin_source, 'if ( false === $deleted )' )
	&& str_contains( $admin_source, "array( 'vm_notice' => 'rollback_blocked' )" )
	&& str_contains( $admin_source, 'if ( null === $deleted )' )
	&& ! str_contains( $admin_source, "'Import rollback was blocked or failed.'" ),
	'Controller must separate controlled blocking from unexpected ErrorBoundary failure.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:rollback-activity-cleanup' )
	&& strpos( $composer, '@test:rollback-activity-cleanup' ) < strpos( $composer, '@build' ),
	'VM-018 regression coverage must run before build.'
);

echo "Rollback Activity cleanup OK: expected protection is yellow-only while technical failures remain red.\n";
