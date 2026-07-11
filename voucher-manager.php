<?php
/**
 * Plugin Name:       Voucher Manager
 * Plugin URI:        https://github.com/mjs512/voucher-manager
 * Description:       Manage and distribute unique voucher codes securely in WordPress.
 * Version:           0.2.0-alpha.1
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

define( 'VOUCHER_MANAGER_VERSION', '0.2.0-alpha.1' );
define( 'VOUCHER_MANAGER_DATABASE_VERSION', '1' );
define( 'VOUCHER_MANAGER_FILE', __FILE__ );
define( 'VOUCHER_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'VOUCHER_MANAGER_URL', plugin_dir_url( __FILE__ ) );

$voucher_manager_autoloader = VOUCHER_MANAGER_PATH . 'vendor/autoload.php';

if ( ! is_readable( $voucher_manager_autoloader ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__(
					'Voucher Manager could not start because Composer dependencies are missing. Run "composer install" in the plugin directory.',
					'voucher-manager'
				)
			);
		}
	);

	return;
}

require_once $voucher_manager_autoloader;

register_activation_hook(
	VOUCHER_MANAGER_FILE,
	array( VoucherManager\Lifecycle\Activator::class, 'activate' )
);

VoucherManager\Core\Plugin::instance()->boot();
