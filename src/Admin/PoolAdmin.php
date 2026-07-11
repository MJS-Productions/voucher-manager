<?php
/**
 * Pool administration controller.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use InvalidArgumentException;
use VoucherManager\Domain\Pool\PoolService;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;

final class PoolAdmin {
	private WpdbPoolRepository $repository;
	private PoolService $service;

	public function __construct() {
		$this->repository = new WpdbPoolRepository();
		$this->service = new PoolService( $this->repository );
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_voucher_manager_save_pool', array( $this, 'save' ) );
		add_action( 'admin_post_voucher_manager_toggle_pool', array( $this, 'toggle' ) );
		add_action( 'admin_post_voucher_manager_delete_pool', array( $this, 'delete' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			'voucher-manager',
			__( 'Pools', 'voucher-manager' ),
			__( 'Pools', 'voucher-manager' ),
			'manage_options',
			'voucher-manager-pools',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		$this->guard();
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list';
		$pool_id = isset( $_GET['pool_id'] ) ? absint( $_GET['pool_id'] ) : 0;
		$pool = 0 < $pool_id ? $this->repository->find( $pool_id ) : null;
		if ( in_array( $action, array( 'new', 'edit' ), true ) ) {
			$template = VOUCHER_MANAGER_PATH . 'templates/admin/pool-form.php';
		} else {
			$pools = $this->repository->all();
			$template = VOUCHER_MANAGER_PATH . 'templates/admin/pools.php';
		}
		if ( is_readable( $template ) ) { require $template; }
	}

	public function save(): void {
		$this->guard();
		check_admin_referer( 'voucher_manager_save_pool' );
		$id = isset( $_POST['pool_id'] ) ? absint( $_POST['pool_id'] ) : 0;
		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$threshold = isset( $_POST['warning_threshold'] ) ? absint( $_POST['warning_threshold'] ) : 0;
		$active = isset( $_POST['active'] );
		try {
			$success = 0 < $id
				? $this->service->update( $id, $name, $description, $threshold, $active )
				: 0 < $this->service->create( $name, $description, $threshold, $active );
			$this->redirect( $success ? ( 0 < $id ? 'updated' : 'created' ) : 'error' );
		} catch ( InvalidArgumentException $exception ) {
			$this->redirect( 'invalid' );
		}
	}

	public function toggle(): void {
		$this->guard();
		$id = isset( $_GET['pool_id'] ) ? absint( $_GET['pool_id'] ) : 0;
		check_admin_referer( 'voucher_manager_toggle_pool_' . $id );
		$pool = $this->repository->find( $id );
		$this->redirect( null !== $pool && $this->repository->set_active( $id, ! $pool->is_active() ) ? 'status' : 'error' );
	}

	public function delete(): void {
		$this->guard();
		$id = isset( $_GET['pool_id'] ) ? absint( $_GET['pool_id'] ) : 0;
		check_admin_referer( 'voucher_manager_delete_pool_' . $id );
		$this->redirect( $this->repository->delete( $id ) ? 'deleted' : 'delete_blocked' );
	}

	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'voucher-manager' ) );
		}
	}

	private function redirect( string $notice ): void {
		wp_safe_redirect( add_query_arg( array( 'page' => 'voucher-manager-pools', 'vm_notice' => $notice ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
