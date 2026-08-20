<?php
/**
 * Plugin activation lifecycle.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Lifecycle;

use VoucherManager\Database\Migrator;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;

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

		$was_installed = false !== get_option( 'voucher_manager_version', false );

		( new Migrator() )->migrate();

		update_option( 'voucher_manager_version', VOUCHER_MANAGER_VERSION, false );

		( new ActivityRetentionScheduler() )->reconcile();

		$logger = new OperationalLogger( new WpdbLogRepository() );
		$logger->info(
			$was_installed ? OperationalEvent::PLUGIN_ACTIVATED : OperationalEvent::PLUGIN_INSTALLED,
			$was_installed ? 'Voucher Manager was activated.' : 'Voucher Manager was installed.'
		);
	}
}
