<?php
/**
 * Pool inventory administration controller.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Infrastructure\WordPress\WpdbCodeInventoryRepository;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;

/**
 * Registers and renders the read-only pool inventory screen.
 */
final class InventoryAdmin {

	private WpdbPoolRepository $pools;
	private InventoryViewModel $view;
	private InventoryData $data;

	public function __construct() {
		$this->pools = new WpdbPoolRepository();
		$this->view  = new InventoryViewModel();
		$this->data  = new InventoryData(
			new WpdbCodeInventoryRepository(),
			$this->view
		);
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	public function register_page(): void {
		add_submenu_page(
			null,
			__( 'Pool Inventory', 'voucher-manager' ),
			__( 'Pool Inventory', 'voucher-manager' ),
			'manage_options',
			'voucher-manager-inventory',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		$this->guard();

		$pool_id = isset( $_GET['pool_id'] ) ? absint( $_GET['pool_id'] ) : 0;
		$pool    = $this->pools->find( $pool_id );

		if ( null === $pool ) {
			wp_safe_redirect( admin_url( 'admin.php?page=voucher-manager-pools' ) );
			exit;
		}

		$state     = isset( $_GET['state'] ) ? sanitize_key( wp_unslash( $_GET['state'] ) ) : 'all';
		$import_id = isset( $_GET['import_id'] ) ? absint( $_GET['import_id'] ) : 0;
		$page      = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$data      = $this->data->get( $pool_id, $state, $import_id, $page );
		$view      = $this->view;

		$template = VOUCHER_MANAGER_PATH . 'templates/admin/inventory.php';
		if ( is_readable( $template ) ) {
			require $template;
		}
	}

	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'voucher-manager' ) );
		}
	}
}
