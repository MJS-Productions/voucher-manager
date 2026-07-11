<?php
/**
 * Main plugin application.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Core;

use VoucherManager\Admin\Admin;
use VoucherManager\Database\Migrator;

/** Coordinates plugin services and WordPress hooks. */
final class Plugin {
	private static ?self $instance = null;

	private function __construct() {}

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function boot(): void {
		add_action( 'plugins_loaded', array( $this, 'maybe_upgrade_database' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		if ( is_admin() ) {
			( new Admin() )->register();
		}
	}

	public function maybe_upgrade_database(): void {
		( new Migrator() )->migrate();
		update_option( 'voucher_manager_version', VOUCHER_MANAGER_VERSION, false );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'voucher-manager',
			false,
			dirname( plugin_basename( VOUCHER_MANAGER_FILE ) ) . '/languages'
		);
	}
}
