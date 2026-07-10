<?php
/**
 * Database migration runner.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Database;

/**
 * Creates and upgrades Voucher Manager database tables.
 */
final class Migrator {

	/**
	 * Run pending schema migrations.
	 */
	public function migrate(): void {
		$installed_version = (string) get_option( 'voucher_manager_database_version', '0' );

		if ( version_compare( $installed_version, VOUCHER_MANAGER_DATABASE_VERSION, '>=' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$schema = new Schema();

		foreach ( $schema->statements() as $statement ) {
			dbDelta( $statement );
		}

		update_option(
			'voucher_manager_database_version',
			VOUCHER_MANAGER_DATABASE_VERSION,
			false
		);
	}
}
