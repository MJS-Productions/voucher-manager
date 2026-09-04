<?php
/**
 * Inventory changed event contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Inventory changed event assertion failed: ' . $message );
	}
};

$event        = file_get_contents( $root . '/src/Extension/InventoryChangedEvent.php' );
$distribution = file_get_contents( $root . '/src/Extension/DistributionApi.php' );
$import_admin = file_get_contents( $root . '/src/Admin/ImportAdmin.php' );
$pool_admin   = file_get_contents( $root . '/src/Admin/PoolAdmin.php' );
$pool_service = file_get_contents( $root . '/src/Domain/Pool/PoolLifecycleService.php' );
$composer     = file_get_contents( $root . '/composer.json' );

$assert( is_string( $event ), 'InventoryChangedEvent.php must exist.' );
$assert(
	str_contains( $event, "public const HOOK = 'voucher_manager_inventory_changed';" )
	&& str_contains( $event, "public const REASON_DISTRIBUTION = 'distribution';" )
	&& str_contains( $event, "public const REASON_IMPORT       = 'import';" )
	&& str_contains( $event, "public const REASON_ROLLBACK     = 'rollback';" )
	&& str_contains( $event, "public const REASON_DELETION     = 'deletion';" ),
	'The public event contract must expose the stable hook and approved semantic reasons.'
);
$assert(
	str_contains( $event, 'do_action( self::HOOK, $pool_id, $reason );' )
	&& str_contains( $event, 'catch ( \Throwable $exception )' ),
	'Inventory events must use the WordPress hook system and remain failure-safe for completed operations.'
);
$assert(
	! str_contains( $event, "'available'" )
	&& ! str_contains( $event, "'total'" )
	&& ! str_contains( $event, "'assigned'" ),
	'The event must not publish duplicated inventory counts.'
);

$assert(
	is_string( $distribution )
	&& str_contains( $distribution, 'if ( $result->success() )' )
	&& str_contains( $distribution, 'InventoryChangedEvent::REASON_DISTRIBUTION' ),
	'Successful distribution must publish the semantic inventory-change event.'
);
$assert(
	is_string( $import_admin )
	&& str_contains( $import_admin, 'if ( 0 < $result->imported() )' )
	&& str_contains( $import_admin, 'InventoryChangedEvent::REASON_IMPORT' ),
	'Import must publish the event only when One-Time Codes were actually added.'
);
$assert(
	str_contains( $import_admin, 'if ( 0 < $deleted && $rollback_import instanceof ImportRecord )' )
	&& str_contains( $import_admin, 'InventoryChangedEvent::REASON_ROLLBACK' ),
	'Rollback must publish the event only when inventory was actually removed and the pool context is known.'
);
$assert(
	is_string( $pool_admin )
	&& str_contains( $pool_admin, '$deleted = $this->lifecycle->delete_available_codes( $id );' )
	&& str_contains( $pool_admin, 'if ( 0 < $deleted )' )
	&& str_contains( $pool_admin, 'InventoryChangedEvent::REASON_DELETION' ),
	'Available-code deletion must publish the semantic inventory-change event only after inventory was actually removed.'
);
$assert(
	is_string( $pool_service )
	&& str_contains( $pool_service, 'OperationalEvent::POOL_AVAILABLE_CODES_DELETED' ),
	'Existing available-code deletion activity logging must remain in place.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:inventory-changed-event": "php tests/Integration/InventoryChangedEventTest.php"' )
	&& str_contains( $composer, '"@test:inventory-changed-event"' ),
	'The inventory changed event test must be registered in the quality gate.'
);

echo "Inventory changed event contract OK.\n";
