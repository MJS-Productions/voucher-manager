<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Admin;

use InvalidArgumentException;
use Throwable;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Domain\Pool\PoolLifecycleService;
use VoucherManager\Domain\Pool\PoolService;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;
use VoucherManager\Infrastructure\WordPress\WpdbPoolLifecycleRepository;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;

final class PoolAdmin {
	private WpdbPoolRepository $repository;
	private PoolService $service;
	private PoolOverviewData $overview;
	private WpdbPoolLifecycleRepository $lifecycle_repository;
	private PoolLifecycleService $lifecycle;
	private OperationalLogger $logger;
	public function __construct() {
		$this->repository = new WpdbPoolRepository(); $this->service = new PoolService( $this->repository ); $this->overview = new PoolOverviewData();
		$this->lifecycle_repository = new WpdbPoolLifecycleRepository();
		$this->logger = new OperationalLogger( new WpdbLogRepository() );
		$this->lifecycle = new PoolLifecycleService( $this->lifecycle_repository, $this->logger );
	}
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_voucher_manager_save_pool', array( $this, 'save' ) );
		add_action( 'admin_post_voucher_manager_toggle_pool', array( $this, 'toggle' ) );
		add_action( 'admin_post_voucher_manager_delete_available_codes', array( $this, 'delete_available_codes' ) );
		add_action( 'admin_post_voucher_manager_delete_pool', array( $this, 'delete' ) );
	}
	public function register_menu(): void { add_submenu_page( 'voucher-manager', __( 'Pools', 'voucher-manager' ), __( 'Pools', 'voucher-manager' ), 'manage_options', 'voucher-manager-pools', array( $this, 'render' ) ); }
	public function render(): void {
		$this->guard(); $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list'; $pool_id = isset( $_GET['pool_id'] ) ? absint( $_GET['pool_id'] ) : 0; $pool = 0 < $pool_id ? $this->repository->find( $pool_id ) : null;
		if ( in_array( $action, array( 'new', 'edit' ), true ) ) { $template = VOUCHER_MANAGER_PATH . 'templates/admin/pool-form.php'; }
		elseif ( in_array( $action, array( 'danger-zone', 'confirm-delete-available' ), true ) && null !== $pool ) {
			$summary = $this->lifecycle_repository->deletion_summary( $pool_id );
			$template = 'confirm-delete-available' === $action
				? VOUCHER_MANAGER_PATH . 'templates/admin/pool-delete-available-confirmation.php'
				: VOUCHER_MANAGER_PATH . 'templates/admin/pool-danger-zone.php';
		}
		else { $pools = $this->repository->all(); $pool_rows = $this->overview->rows( $pools ); $template = VOUCHER_MANAGER_PATH . 'templates/admin/pools.php'; }
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
		$existing = 0 < $id ? $this->repository->find( $id ) : null;
		try {
			if ( 0 < $id ) {
				$success = $this->service->update( $id, $name, $description, $threshold, $active );
				if ( $success ) {
					$this->logger->info( OperationalEvent::POOL_UPDATED, 'Pool settings were updated.', array( 'pool_id' => $id, 'pool_name' => $name ) );
					if ( null !== $existing && $existing->is_active() !== $active ) {
						$this->log_pool_status( $id, $name, $active );
					}
				}
				$this->redirect( $success ? 'updated' : 'error' );
			}

			$new_id = $this->service->create( $name, $description, $threshold, $active );
			if ( 0 < $new_id ) {
				$this->logger->info(
					OperationalEvent::POOL_CREATED,
					'Pool was created.',
					array( 'pool_id' => $new_id, 'pool_name' => $name, 'status' => $active ? 'active' : 'inactive' )
				);
			}
			$this->redirect( 0 < $new_id ? 'created' : 'error' );
		} catch ( InvalidArgumentException ) {
			$this->redirect( 'invalid' );
		}
	}
	public function toggle(): void {
		$this->guard();
		$id = isset( $_GET['pool_id'] ) ? absint( $_GET['pool_id'] ) : 0;
		check_admin_referer( 'voucher_manager_toggle_pool_' . $id );
		$pool = $this->repository->find( $id );
		if ( null === $pool ) {
			$this->redirect( 'error' );
		}
		$active = ! $pool->is_active();
		$success = $this->repository->set_active( $id, $active );
		if ( $success ) {
			$this->log_pool_status( $id, $pool->name(), $active );
		}
		$this->redirect( $success ? 'status' : 'error' );
	}
	public function delete_available_codes(): void {
		$this->guard();
		$id = isset( $_POST['pool_id'] ) ? absint( $_POST['pool_id'] ) : 0;
		check_admin_referer( 'voucher_manager_delete_available_codes_' . $id );
		$confirmed = isset( $_POST['confirm_delete_available'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['confirm_delete_available'] ) );
		if ( ! $confirmed ) {
			$this->redirect_delete_available_confirmation( $id, 'confirmation_required' );
		}
		try {
			$deleted = $this->lifecycle->delete_available_codes( $id );
			$this->redirect( 'available_deleted', $deleted );
		} catch ( Throwable ) {
			$this->redirect_delete_available_confirmation( $id, 'delete_failed' );
		}
	}
	public function delete(): void { $this->guard(); $id = isset( $_POST['pool_id'] ) ? absint( $_POST['pool_id'] ) : 0; check_admin_referer( 'voucher_manager_delete_pool_' . $id ); $pool = $this->repository->find( $id ); $confirmation = isset( $_POST['pool_name_confirmation'] ) ? sanitize_text_field( wp_unslash( $_POST['pool_name_confirmation'] ) ) : ''; if ( null === $pool || $confirmation !== $pool->name() ) { $this->redirect_danger( $id, 'confirmation_failed' ); } try { $this->lifecycle->delete_pool( $id, $pool->name() ); $this->redirect( 'deleted' ); } catch ( Throwable ) { $this->redirect_danger( $id, 'delete_failed' ); } }
	private function log_pool_status( int $id, string $name, bool $active ): void {
		$this->logger->info(
			$active ? OperationalEvent::POOL_ACTIVATED : OperationalEvent::POOL_DEACTIVATED,
			$active ? 'Pool was activated.' : 'Pool was deactivated.',
			array( 'pool_id' => $id, 'pool_name' => $name, 'status' => $active ? 'active' : 'inactive' )
		);
	}
	private function guard(): void { if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You are not allowed to access this page.', 'voucher-manager' ) ); } }
	private function redirect( string $notice, int $count = 0 ): void { $args = array( 'page' => 'voucher-manager-pools', 'vm_notice' => $notice ); if ( 0 < $count ) { $args['vm_count'] = $count; } wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) ); exit; }
	private function redirect_danger( int $id, string $notice ): void { wp_safe_redirect( add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'danger-zone', 'pool_id' => $id, 'vm_notice' => $notice ), admin_url( 'admin.php' ) ) ); exit; }
	private function redirect_delete_available_confirmation( int $id, string $notice ): void { wp_safe_redirect( add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'confirm-delete-available', 'pool_id' => $id, 'vm_notice' => $notice ), admin_url( 'admin.php' ) ) ); exit; }
}
