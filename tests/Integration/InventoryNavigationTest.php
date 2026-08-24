<?php
/**
 * Framework-free hidden Inventory navigation test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Inventory navigation assertion failed: ' . $message );
	}
};

$admin_source       = file_get_contents( $root . '/src/Admin/InventoryAdmin.php' );
$admin_assets_source = file_get_contents( $root . '/src/Admin/Admin.php' );
$pools_source       = file_get_contents( $root . '/templates/admin/pools.php' );
$composer           = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $admin_source )
	&& is_string( $admin_assets_source )
	&& str_contains( $admin_source, "add_submenu_page(\n\t\t\t'voucher-manager'," )
	&& str_contains( $admin_assets_source, 'wp_add_inline_style(' )
	&& str_contains( $admin_assets_source, 'voucher-manager-inventory' )
	&& str_contains( $admin_assets_source, 'href="admin.php?page=voucher-manager-inventory"' )
	&& ! str_contains( $admin_source, 'remove_submenu_page' ),
	'Inventory must stay registered for access and hide only its visible submenu link through the enqueued admin stylesheet.'
);

$assert(
	str_contains( $admin_source, "add_filter( 'parent_file'" )
	&& str_contains( $admin_source, "add_filter( 'submenu_file'" ),
	'Hidden Inventory must register WordPress menu-highlighting filters.'
);

$assert(
	str_contains( $admin_source, "'voucher-manager-inventory' === \$plugin_page" )
	&& str_contains( $admin_source, "? 'voucher-manager'" ),
	'Inventory must keep the Voucher Manager parent menu expanded.'
);

$assert(
	str_contains( $admin_source, "? 'voucher-manager-pools'" ),
	'Inventory must keep Pools highlighted as its logical parent section.'
);

$assert(
	is_string( $pools_source )
	&& str_contains( $pools_source, "'page'    => 'voucher-manager-inventory'" )
	&& str_contains( $pools_source, 'View inventory' ),
	'The hidden Inventory page must remain reachable from Pool cards.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:inventory-navigation' )
	&& strpos( $composer, '@test:inventory-navigation' ) < strpos( $composer, '@build' ),
	'Inventory navigation regression coverage must run before the release build.'
);

echo "Inventory navigation OK: hidden detail page keeps Voucher Manager expanded and Pools highlighted.\n";
