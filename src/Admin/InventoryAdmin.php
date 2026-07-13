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
		add_filter( 'parent_file', array( $this, 'highlight_parent_menu' ) );
		add_filter( 'submenu_file', array( $this, 'highlight_pools_submenu' ), 10, 2 );
	}

	public function register_page(): void {
		add_submenu_page(
			'voucher-manager',
			__( 'Pool Inventory', 'voucher-manager' ),
			__( 'Pool Inventory', 'voucher-manager' ),
			'manage_options',
			'voucher-manager-inventory',
			array( $this, 'render' )
		);

		remove_submenu_page( 'voucher-manager', 'voucher-manager-inventory' );
	}


	/**
	 * Keep the Voucher Manager menu expanded on the hidden Inventory detail page.
	 */
	public function highlight_parent_menu( string $parent_file ): string {
		global $plugin_page;

		return 'voucher-manager-inventory' === $plugin_page
			? 'voucher-manager'
			: $parent_file;
	}

	/**
	 * Keep Pools highlighted while viewing a hidden pool Inventory detail page.
	 */
	public function highlight_pools_submenu( ?string $submenu_file, string $parent_file ): ?string {
		global $plugin_page;

		unset( $parent_file );

		return 'voucher-manager-inventory' === $plugin_page
			? 'voucher-manager-pools'
			: $submenu_file;
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
