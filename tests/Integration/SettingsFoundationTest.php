<?php
/**
 * Framework-free Settings foundation test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Admin\SettingsViewModel;
use VoucherManager\Domain\Settings\Settings;

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string {
		unset( $domain );
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
		throw new RuntimeException( 'Settings foundation assertion failed: ' . $message );
	}
};

$defaults = Settings::defaults();
$assert( 90 === $defaults->activity_retention_days(), 'Activity retention must default to 90 days.' );
$assert( ! $defaults->delete_data_on_uninstall(), 'Destructive uninstall must default OFF.' );

foreach ( array( 0, 30, 90, 180 ) as $allowed ) {
	$settings = Settings::from_array( array( 'activity_retention_days' => $allowed ) );
	$assert( $allowed === $settings->activity_retention_days(), 'Every allowlisted retention value must be preserved.' );
}

$invalid = Settings::from_array(
	array(
		'activity_retention_days'  => 365,
		'delete_data_on_uninstall' => 'not-a-boolean',
	)
);
$assert( 90 === $invalid->activity_retention_days(), 'Unknown retention values must normalize to 90 days.' );
$assert( ! $invalid->delete_data_on_uninstall(), 'Invalid destructive settings must normalize safely to OFF.' );

$enabled = Settings::from_array( array( 'delete_data_on_uninstall' => '1' ) );
$assert( $enabled->delete_data_on_uninstall(), 'Explicit boolean consent must be representable.' );

$view = new SettingsViewModel();
$options = $view->retention_options();
$assert( array( 30, 90, 180, 0 ) === array_keys( $options ), 'Settings must expose only the four approved retention choices.' );
$assert( str_contains( $view->uninstall_warning(), 'permanently delete' ), 'Destructive uninstall needs an explicit permanent-deletion warning.' );
$assert( str_contains( $view->retention_description( 90 ), '90 days' ), 'Finite retention needs understandable guidance.' );
$assert( str_contains( $view->retention_description( 0 ), 'not be removed automatically' ), 'Indefinite retention needs explicit guidance.' );

$admin_source    = file_get_contents( $root . '/src/Admin/SettingsAdmin.php' );
$repository      = file_get_contents( $root . '/src/Infrastructure/WordPress/WpSettingsRepository.php' );
$template_source = file_get_contents( $root . '/templates/admin/settings.php' );
$root_admin      = file_get_contents( $root . '/src/Admin/Admin.php' );
$uninstall       = file_get_contents( $root . '/uninstall.php' );
$plugin          = file_get_contents( $root . '/voucher-manager.php' );
$composer        = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $repository )
	&& str_contains( $repository, "OPTION_NAME = 'voucher_manager_settings'" )
	&& str_contains( $repository, "update_option( self::OPTION_NAME, \$settings->to_array(), false )" ),
	'All user-facing settings must use one owned, non-autoloaded option.'
);

$assert(
	is_string( $admin_source )
	&& str_contains( $admin_source, "current_user_can( 'manage_options' )" )
	&& str_contains( $admin_source, "check_admin_referer( 'voucher_manager_save_settings' )" )
	&& str_contains( $admin_source, "admin_post_voucher_manager_save_settings" )
	&& str_contains( $admin_source, "! \$current->delete_data_on_uninstall()" )
	&& str_contains( $admin_source, 'uninstall_confirmation_required' ),
	'Settings saves must enforce capability, nonce and explicit OFF-to-ON destructive consent.'
);

$assert(
	is_string( $template_source )
	&& str_contains( $template_source, 'Operational Activity retention' )
	&& str_contains( $template_source, 'Delete all Voucher Manager data when the plugin is uninstalled' )
	&& str_contains( $template_source, 'Deactivating Voucher Manager never deletes data' )
	&& str_contains( $template_source, 'Saving this preference does not delete Activity immediately' ),
	'Settings UI must explain both lifecycle controls and the current no-cleanup boundary.'
);

$assert(
	is_string( $root_admin ) && str_contains( $root_admin, 'SettingsAdmin' ),
	'Settings must be registered in the Voucher Manager administration.'
);

$assert(
	is_string( $uninstall )
	&& str_contains( $uninstall, 'delete_data_on_uninstall' )
	&& str_contains( $uninstall, 'UninstallDataBoundary' ),
	'Settings Foundation consent must be consumed only by the dedicated uninstall boundary.'
);

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Settings Foundation must not introduce a database migration.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:settings-foundation' )
	&& strpos( $composer, '@test:settings-foundation' ) < strpos( $composer, '@build' ),
	'Settings Foundation coverage must run before the release build.'
);

echo "Settings foundation OK: normalized retention, explicit uninstall consent and safe administration boundary verified.\n";
