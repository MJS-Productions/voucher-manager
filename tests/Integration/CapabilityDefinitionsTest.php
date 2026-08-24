<?php
/**
 * Voucher Manager capability definition regression test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use VoucherManager\Admin\Capabilities;

$expected = array(
	'voucher_manager_view_dashboard',
	'voucher_manager_view_inventory',
	'voucher_manager_manage_pools',
	'voucher_manager_delete_pools',
	'voucher_manager_import_codes',
	'voucher_manager_rollback_imports',
	'voucher_manager_distribute_codes',
	'voucher_manager_view_activity',
);

if ( $expected !== Capabilities::all() ) {
	fwrite( STDERR, "Voucher Manager capability definitions do not match the stable contract.\n" );
	exit( 1 );
}

$activator_source = file_get_contents(
	dirname(__DIR__, 2) . '/src/Lifecycle/Activator.php'
);

if ( false === $activator_source ) {
	fwrite( STDERR, "Could not read Activator.php.\n" );
	exit( 1 );
}

if (
	! str_contains( $activator_source, "get_role( 'administrator' )" )
	|| ! str_contains( $activator_source, 'Capabilities::all()' )
	|| ! str_contains( $activator_source, 'add_cap( $capability )' )
) {
	fwrite( STDERR, "Administrator capability grants are not wired through the stable capability definitions.\n" );
	exit( 1 );
}

echo "Voucher Manager capability definitions OK.\n";