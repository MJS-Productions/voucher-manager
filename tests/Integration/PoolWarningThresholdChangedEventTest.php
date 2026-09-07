<?php
/**
 * Pool warning-threshold changed event contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Pool warning-threshold event assertion failed: ' . $message );
	}
};

$event      = file_get_contents( $root . '/src/Extension/PoolWarningThresholdChangedEvent.php' );
$pool_admin = file_get_contents( $root . '/src/Admin/PoolAdmin.php' );
$pool       = file_get_contents( $root . '/src/Domain/Pool/Pool.php' );
$pool_api   = file_get_contents( $root . '/src/Extension/PoolReadApi.php' );
$inventory  = file_get_contents( $root . '/src/Extension/InventoryChangedEvent.php' );
$composer   = file_get_contents( $root . '/composer.json' );

$assert( is_string( $event ), 'PoolWarningThresholdChangedEvent.php must exist.' );
$assert(
	str_contains( $event, "public const HOOK = 'voucher_manager_pool_warning_threshold_changed';" )
	&& str_contains( $event, 'do_action( self::HOOK, $pool_id );' )
	&& str_contains( $event, 'catch ( \\Throwable $exception )' ),
	'The public threshold-change event must expose a stable WordPress hook and remain failure-safe.'
);
$assert(
	! str_contains( $event, '$warning_threshold' )
	&& ! str_contains( $event, '$available' )
	&& ! str_contains( $event, '$inventory' ),
	'The event must identify the pool without duplicating authoritative Free pool or inventory data.'
);

$assert(
	is_string( $pool )
	&& str_contains( $pool, 'public function warning_threshold(): int' ),
	'The authoritative Free pool entity must expose the warning threshold for supported re-evaluation.'
);
$assert(
	is_string( $pool_api )
	&& str_contains( $pool_api, 'public function find( int $pool_id ): ?Pool' ),
	'Extensions must be able to re-read the affected pool through the supported PoolReadApi.'
);

$assert(
	is_string( $pool_admin )
	&& str_contains( $pool_admin, '$existing = 0 < $id ? $this->repository->find( $id ) : null;' )
	&& str_contains( $pool_admin, 'if ( $success ) {' )
	&& str_contains( $pool_admin, '$existing->warning_threshold() !== $threshold' )
	&& str_contains( $pool_admin, 'PoolWarningThresholdChangedEvent::dispatch( $id );' ),
	'A successful update must publish the event only when the stored warning threshold actually changed.'
);
$assert(
	strpos( $pool_admin, '$existing->warning_threshold() !== $threshold' ) > strpos( $pool_admin, 'if ( $success ) {' ),
	'Threshold-change dispatch must remain inside the successful pool-update path.'
);

$assert(
	is_string( $inventory )
	&& str_contains( $inventory, "public const HOOK = 'voucher_manager_inventory_changed';" )
	&& ! str_contains( $inventory, 'warning_threshold' ),
	'Existing inventory-change semantics must remain separate from pool threshold configuration changes.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:pool-warning-threshold-event": "php tests/Integration/PoolWarningThresholdChangedEventTest.php"' )
	&& str_contains( $composer, '"@test:pool-warning-threshold-event"' ),
	'The threshold-change event regression test must be registered in the quality gate.'
);

echo "Pool warning-threshold changed event contract OK.\n";
