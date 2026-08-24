<?php
/**
 * WordPress administration integration.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

final class Admin {
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		( new PoolAdmin() )->register();
		( new ImportAdmin() )->register();
		( new DistributionAdmin() )->register();
		( new OperationalActivityAdmin() )->register();
		( new InventoryAdmin() )->register();
		( new SettingsAdmin() )->register();
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'Voucher Manager', 'voucher-manager' ),
			__( 'Voucher Manager', 'voucher-manager' ),
			Capabilities::VIEW_DASHBOARD,
			'voucher-manager',
			array( $this, 'render_dashboard' ),
			'dashicons-tickets-alt',
			58
		);

		add_submenu_page(
			'voucher-manager',
			__( 'Voucher Manager Dashboard', 'voucher-manager' ),
			__( 'Dashboard', 'voucher-manager' ),
			Capabilities::VIEW_DASHBOARD,
			'voucher-manager',
			array( $this, 'render_dashboard' )
		);
	}

	public function enqueue_assets( string $hook_suffix ): void {
		if ( ! str_contains( $hook_suffix, 'voucher-manager' ) ) {
			return;
		}
		wp_enqueue_style(
			'voucher-manager-admin',
			VOUCHER_MANAGER_URL . 'assets/css/admin.css',
			array(),
			VOUCHER_MANAGER_VERSION
		);

		wp_enqueue_script(
			'voucher-manager-admin',
			VOUCHER_MANAGER_URL . 'assets/js/admin.js',
			array(),
			VOUCHER_MANAGER_VERSION,
			true
		);
	}

	public function render_dashboard(): void {
		if ( ! current_user_can( Capabilities::VIEW_DASHBOARD ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'voucher-manager' ) );
		}
		$data     = ( new DashboardData() )->get();
		$template = VOUCHER_MANAGER_PATH . 'templates/admin/dashboard.php';
		if ( is_readable( $template ) ) {
			require $template;
		}
	}
}
