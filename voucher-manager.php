<?php
/**
 * Plugin Name:       MJS-Productions Voucher Manager
 * Plugin URI:        https://github.com/MJS-Productions/voucher-manager
 * Description:       Professional One-Time Code Management for WordPress.
 * Version:           1.0.8
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            MJS-Productions
 * Author URI:        https://mjs-productions.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mjs-productions-voucher-manager
 *
 * @package VoucherManager
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VOUCHER_MANAGER_VERSION', '1.0.8' );
define( 'VOUCHER_MANAGER_DATABASE_VERSION', '2' );
define( 'VOUCHER_MANAGER_EXTENSION_API_VERSION', '1' );
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
