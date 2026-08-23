<?php
/**
 * Extension inventory read API contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Extension inventory read API assertion failed: ' . $message );
	}
};

$api      = file_get_contents( $root . '/src/Extension/InventoryReadApi.php' );
$overview = file_get_contents( $root . '/src/Admin/PoolOverviewData.php' );
$composer = file_get_contents( $root . '/composer.json' );

$assert( is_string( $api ), 'InventoryReadApi.php must exist.' );
$assert(
	str_contains( $api, 'final class InventoryReadApi' )
	&& str_contains( $api, 'public function for_pool( int $pool_id ): array' )
	&& str_contains( $api, 'public function for_pools( array $pool_ids ): array' ),
	'The public API must expose the supported inventory read operations.'
);
$assert(
	str_contains( $api, "'total'     => 0" )
	&& str_contains( $api, "'available' => 0" )
	&& str_contains( $api, "'assigned'  => 0" ),
	'The inventory contract must provide stable total, available, and assigned counters.'
);
$assert(
	! str_contains( $api, 'INSERT ' )
	&& ! str_contains( $api, 'UPDATE ' )
	&& ! str_contains( $api, 'DELETE ' ),
	'The extension inventory API must remain read-only.'
);

$assert(
	is_string( $overview )
	&& str_contains( $overview, 'use VoucherManager\Extension\InventoryReadApi;' )
	&& str_contains( $overview, 'private InventoryReadApi $inventory;' )
	&& str_contains( $overview, '$this->inventory = new InventoryReadApi();' )
	&& str_contains( $overview, '$inventories = $this->inventory->for_pools( $pool_ids );' ),
	'PoolOverviewData must use the same supported inventory read path as extensions.'
);
$assert(
	! str_contains( $overview, 'global $wpdb;' )
	&& ! str_contains( $overview, 'SELECT pool_id, status, COUNT(*) AS amount' ),
	'PoolOverviewData must no longer own direct inventory database queries.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:extension-inventory-read-api": "php tests/Integration/ExtensionInventoryReadApiTest.php"' )
	&& str_contains( $composer, '"@test:extension-inventory-read-api"' ),
	'The extension inventory read API test must be registered in the quality gate.'
);

echo "Extension inventory read API contract OK.\n";
