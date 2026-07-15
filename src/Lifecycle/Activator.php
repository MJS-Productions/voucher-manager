<?php
/**
 * Plugin activation lifecycle.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Lifecycle;

use VoucherManager\Database\Migrator;

/**
 * Handles plugin activation.
 */
final class Activator {

	/**
	 * Install or upgrade the database schema.
	 */
	public static function activate(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		( new Migrator() )->migrate();

		update_option( 'voucher_manager_version', VOUCHER_MANAGER_VERSION, false );

		( new ActivityRetentionScheduler() )->reconcile();
	}
}
