<?php
/**
 * Voucher Manager capability definition and standalone access regression test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Admin\Capabilities;
use VoucherManager\Authorization\AccessPolicy;
use VoucherManager\Extension\DelegatedAccessApi;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if ( ! function_exists( 'wp_get_current_user' ) ) {
	function wp_get_current_user(): object {
		global $voucher_manager_test_user;
		return $voucher_manager_test_user;
	}
}

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

$root = dirname(__DIR__, 2);

$activator_source = file_get_contents(
	$root . '/src/Lifecycle/Activator.php'
);
$plugin_source = file_get_contents(
	$root . '/src/Core/Plugin.php'
);
$delegation_source = file_get_contents(
	$root . '/src/Extension/DelegatedAccessApi.php'
);

if ( false === $activator_source || false === $plugin_source || false === $delegation_source ) {
	fwrite( STDERR, "Could not read capability lifecycle sources.\n" );
	exit( 1 );
}

if (
	! str_contains( $activator_source, "get_role( 'administrator' )" )
	|| ! str_contains( $activator_source, 'Capabilities::all()' )
	|| ! str_contains( $activator_source, 'has_cap( $capability )' )
	|| ! str_contains( $activator_source, 'add_cap( $capability )' )
	|| ! str_contains( $activator_source, 'ensure_administrator_capabilities()' )
) {
	fwrite( STDERR, "Administrator capability grants are not wired through the stable capability definitions.\n" );
	exit( 1 );
}

if ( ! str_contains( $plugin_source, 'Activator::ensure_administrator_capabilities();' ) ) {
	fwrite( STDERR, "Existing installations do not reconcile administrator capabilities during normal plugin bootstrap.\n" );
	exit( 1 );
}

if ( ! str_contains( $plugin_source, '( new AccessPolicy() )->register();' ) ) {
	fwrite( STDERR, "Standalone administrator-only access policy is not registered during plugin bootstrap.\n" );
	exit( 1 );
}

if (
	str_contains( $delegation_source, 'update_option(' )
	|| str_contains( $delegation_source, 'add_option(' )
	|| str_contains( $delegation_source, 'set_transient(' )
) {
	fwrite( STDERR, "Delegated access opt-in must remain runtime-only and non-persistent.\n" );
	exit( 1 );
}

$policy = new AccessPolicy();
$non_admin = new class() {
	public array $roles = array( 'editor' );
	public function exists(): bool {
		return true;
	}
};
$administrator = new class() {
	public array $roles = array( 'administrator' );
	public function exists(): bool {
		return true;
	}
};

$allcaps = array(
	Capabilities::VIEW_DASHBOARD => true,
	Capabilities::IMPORT_CODES   => true,
	'edit_posts'                 => true,
);

$filtered = $policy->filter_user_capabilities( $allcaps, array(), array(), $non_admin );
if (
	isset( $filtered[ Capabilities::VIEW_DASHBOARD ] )
	|| isset( $filtered[ Capabilities::IMPORT_CODES ] )
	|| ! isset( $filtered['edit_posts'] )
) {
	fwrite( STDERR, "Standalone non-administrators must lose Voucher Manager capabilities while unrelated capabilities remain untouched.\n" );
	exit( 1 );
}

$administrator_caps = $policy->filter_user_capabilities( $allcaps, array(), array(), $administrator );
if ( $allcaps !== $administrator_caps ) {
	fwrite( STDERR, "Administrators must retain their Voucher Manager capabilities.\n" );
	exit( 1 );
}

DelegatedAccessApi::enable();
$delegated_caps = $policy->filter_user_capabilities( $allcaps, array(), array(), $non_admin );
if ( $allcaps !== $delegated_caps ) {
	fwrite( STDERR, "Explicit runtime delegation must allow WordPress to evaluate assigned Voucher Manager capabilities normally.\n" );
	exit( 1 );
}

$voucher_manager_test_user = $non_admin;
if ( AccessPolicy::is_administrator() ) {
	fwrite( STDERR, "Non-administrator roles must not satisfy the strict administrator boundary.\n" );
	exit( 1 );
}

$voucher_manager_test_user = $administrator;
if ( ! AccessPolicy::is_administrator() ) {
	fwrite( STDERR, "The administrator role must satisfy the strict administrator boundary.\n" );
	exit( 1 );
}

echo "Voucher Manager capabilities OK: stable definitions, strict standalone access and runtime delegation are explicit.\n";
