<?php
/**
 * Framework-free uninstall boundary test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Lifecycle\UninstallDataBoundary;

$root = dirname( __DIR__, 2 );

spl_autoload_register(
	static function ( string $class ) use ( $root ): void {
		$prefix = 'VoucherManager\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}

		$file = $root . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) . '.php' );
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Uninstall boundary assertion failed: ' . $message );
	}
};

$assert(
	array(
		'wp_vm_logs',
		'wp_vm_codes',
		'wp_vm_imports',
		'wp_vm_pools',
	) === UninstallDataBoundary::tables( 'wp_' ),
	'Destructive uninstall must own exactly four site-prefixed tables in dependency-aware order.'
);

$assert(
	array(
		'voucher_manager_settings',
		'voucher_manager_version',
		'voucher_manager_database_version',
	) === UninstallDataBoundary::options(),
	'Destructive uninstall must remove exactly the three owned options.'
);

$uninstall   = file_get_contents( $root . '/uninstall.php' );
$deactivator = file_get_contents( $root . '/src/Lifecycle/Deactivator.php' );
$settings    = file_get_contents( $root . '/src/Domain/Settings/Settings.php' );
$composer    = file_get_contents( $root . '/composer.json' );
$plugin      = file_get_contents( $root . '/voucher-manager.php' );

$assert(
	is_string( $uninstall )
	&& strpos( $uninstall, '$raw_settings = get_option' ) < strpos( $uninstall, 'delete_option' )
	&& str_contains( $uninstall, "isset( \$raw_settings['delete_data_on_uninstall'] )" )
	&& str_contains( $uninstall, 'FILTER_VALIDATE_BOOLEAN' ),
	'Uninstall must read and normalize destructive consent before deleting any option.'
);

$assert(
	str_contains( $uninstall, 'wp_clear_scheduled_hook( UninstallDataBoundary::ACTIVITY_CRON_HOOK )' ),
	'Uninstall must always clear the Activity retention hook.'
);

$assert(
	str_contains( $uninstall, 'DISTRIBUTION_INTENT_OPTION_PREFIX' )
	&& str_contains( $uninstall, 'DISTRIBUTION_RESULT_OPTION_PREFIX' )
	&& str_contains( $uninstall, 'DISTRIBUTION_RESULT_INTENT_OPTION_PREFIX' )
	&& str_contains( $uninstall, 'DELETE FROM {$wpdb->options} WHERE option_name LIKE %s' ),
	'Uninstall must always remove ephemeral Distribution intent and result options without touching preserved business settings.'
);

$assert(
	str_contains( $uninstall, 'if ( $delete_data )' )
	&& str_contains( $uninstall, 'foreach ( UninstallDataBoundary::tables( $wpdb->prefix ) as $table )' )
	&& str_contains( $uninstall, 'DROP TABLE IF EXISTS {$table}' )
	&& str_contains( $uninstall, 'foreach ( UninstallDataBoundary::options() as $option )' ),
	'Opt-in destructive uninstall must drop only allowlisted tables and remove all owned options.'
);

$assert(
	str_contains( $uninstall, 'Preserve Pools, Imports, Codes, Activity and user Settings by default' )
	&& str_contains( $uninstall, 'delete_option( UninstallDataBoundary::VERSION_OPTION )' )
	&& str_contains( $uninstall, 'delete_option( UninstallDataBoundary::DATABASE_VERSION_OPTION )' ),
	'Default uninstall must preserve business tables and Settings while removing runtime identity options.'
);

$assert(
	! str_contains( $uninstall, 'switch_to_blog' )
	&& ! str_contains( $uninstall, 'get_sites' )
	&& ! str_contains( $uninstall, 'base_prefix' ),
	'Part 4 must remain site-scoped and must not introduce unaudited network-wide deletion.'
);

$assert(
	is_string( $deactivator )
	&& ! str_contains( $deactivator, 'DROP TABLE' )
	&& ! str_contains( $deactivator, 'delete_option' )
	&& ! str_contains( $deactivator, 'UninstallDataBoundary' ),
	'Deactivation must remain completely separate from uninstall deletion.'
);

$assert(
	is_string( $settings )
	&& str_contains( $settings, 'delete_data_on_uninstall' )
	&& str_contains( $settings, 'false' ),
	'Destructive uninstall consent must continue to default OFF.'
);

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Uninstall Boundary must not introduce a database migration.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:uninstall-boundary' )
	&& strpos( $composer, '@test:uninstall-boundary' ) < strpos( $composer, '@build' ),
	'Uninstall Boundary coverage must run before the release build.'
);

echo "Uninstall boundary OK: preserved-data default, explicit consent and exact site-scoped cleanup verified.\n";
