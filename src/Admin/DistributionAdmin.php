<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Admin;

use VoucherManager\Domain\Distribution\DistributionService;
use VoucherManager\Infrastructure\WordPress\WpdbCodeRepository;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;

final class DistributionAdmin {
	private WpdbPoolRepository $pools;
	private DistributionService $service;

	public function __construct() {
		$this->pools = new WpdbPoolRepository();
		$this->service = new DistributionService( $this->pools, new WpdbCodeRepository(), new WpdbLogRepository() );
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_voucher_manager_distribute_code', array( $this, 'distribute' ) );
	}

	public function register_menu(): void {
		add_submenu_page( 'voucher-manager', __( 'Distribution', 'voucher-manager' ), __( 'Distribution', 'voucher-manager' ), 'manage_options', 'voucher-manager-distribution', array( $this, 'render' ) );
	}

	public function render(): void {
		$this->guard();
		$pools = array_values( array_filter( $this->pools->all(), static fn( $pool ): bool => $pool->is_active() ) );
		$template = VOUCHER_MANAGER_PATH . 'templates/admin/distribution.php';
		if ( is_readable( $template ) ) { require $template; }
	}

	public function distribute(): void {
		$this->guard();
		check_admin_referer( 'voucher_manager_distribute_code' );
		$pool_id = isset( $_POST['pool_id'] ) ? absint( $_POST['pool_id'] ) : 0;
		$result = $this->service->distribute( $pool_id );
		set_transient(
			'voucher_manager_distribution_' . get_current_user_id(),
			array( 'success' => $result->success(), 'code' => $result->code(), 'message' => $result->message(), 'remaining' => $result->remaining() ),
			MINUTE_IN_SECONDS
		);
		wp_safe_redirect( add_query_arg( array( 'page' => 'voucher-manager-distribution', 'vm_notice' => $result->success() ? 'distributed' : 'failed' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You are not allowed to access this page.', 'voucher-manager' ) ); }
	}
}
