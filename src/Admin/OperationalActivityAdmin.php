<?php
/**
 * Operational activity administration controller.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

/**
 * Registers and renders the privacy-safe operational activity history.
 */
final class OperationalActivityAdmin {

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			'voucher-manager',
			__( 'Operational Activity', 'voucher-manager' ),
			_x( 'Activity', 'admin menu label', 'voucher-manager' ),
			Capabilities::VIEW_ACTIVITY,
			'voucher-manager-activity',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		if ( ! current_user_can( Capabilities::VIEW_ACTIVITY ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'voucher-manager' ) );
		}

		$family = isset( $_GET['family'] ) ? sanitize_key( wp_unslash( $_GET['family'] ) ) : 'all';
		$tone   = isset( $_GET['tone'] ) ? sanitize_key( wp_unslash( $_GET['tone'] ) ) : 'all';
		$page   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;

		$data = ( new OperationalActivityData() )->get( $family, $tone, $page );
		$view = new OperationalActivityViewModel();

		$template = VOUCHER_MANAGER_PATH . 'templates/admin/activity.php';
		if ( is_readable( $template ) ) {
			require $template;
		}
	}
}
