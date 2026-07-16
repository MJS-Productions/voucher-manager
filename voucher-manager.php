<?php
/**
 * Plugin Name:       Voucher Manager
 * Plugin URI:        https://github.com/mjs512/voucher-manager
 * Description:       Manage and distribute unique One-Time Codes securely in WordPress.
 * Version:           0.9.2-alpha
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            MJS-Productions e.U.
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       voucher-manager
 * Domain Path:       /languages
 *
 * @package VoucherManager
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VOUCHER_MANAGER_VERSION', '0.9.2-alpha' );
define( 'VOUCHER_MANAGER_DATABASE_VERSION', '2' );
define( 'VOUCHER_MANAGER_FILE', __FILE__ );
define( 'VOUCHER_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'VOUCHER_MANAGER_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'VoucherManager\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}
		$relative = substr( $class, strlen( $prefix ) );
		$file = VOUCHER_MANAGER_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

register_activation_hook(
	VOUCHER_MANAGER_FILE,
	array( VoucherManager\Lifecycle\Activator::class, 'activate' )
);

register_deactivation_hook(
	VOUCHER_MANAGER_FILE,
	array( VoucherManager\Lifecycle\Deactivator::class, 'deactivate' )
);

VoucherManager\Core\Plugin::instance()->boot();
