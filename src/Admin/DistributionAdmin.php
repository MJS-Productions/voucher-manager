<?php
/**
 * Distribution administration controller.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Distribution\DistributionResult;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Extension\DistributionApi;
use VoucherManager\Infrastructure\WordPress\WpDistributionIntentStore;
use VoucherManager\Infrastructure\WordPress\WpDistributionResultStore;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;
use VoucherManager\Support\ErrorBoundary;

/**
 * Handles manual distribution actions in WordPress administration.
 */
final class DistributionAdmin {

	private WpdbPoolRepository $pools;
	private DistributionApi $service;
	private ErrorBoundary $boundary;
	private PoolOverviewData $overview;
	private DistributionViewModel $view;
	private WpDistributionIntentStore $intents;
	private WpDistributionResultStore $results;

	public function __construct() {
		$logger         = new OperationalLogger( new WpdbLogRepository() );
		$this->pools    = new WpdbPoolRepository();
		$this->service  = new DistributionApi();
		$this->boundary = new ErrorBoundary( $logger );
		$this->overview = new PoolOverviewData();
		$this->view     = new DistributionViewModel();
		$this->intents  = new WpDistributionIntentStore();
		$this->results  = new WpDistributionResultStore();
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_voucher_manager_distribute_code', array( $this, 'distribute' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			'voucher-manager',
			__( 'Distribution', 'mjs-productions-voucher-manager' ),
			__( 'Distribution', 'mjs-productions-voucher-manager' ),
			Capabilities::DISTRIBUTE_CODES,
			'voucher-manager-distribution',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		$this->guard();

		$result_token = isset( $_GET['result'] )
			? sanitize_text_field( wp_unslash( $_GET['result'] ) )
			: '';
		$result       = '' === $result_token
			? null
			: $this->results->consume( $result_token, get_current_user_id() );

		$pool_rows = $this->boundary->execute(
			fn(): array => $this->overview->rows(
				array_values(
					array_filter(
						$this->pools->all(),
						static fn( $pool ): bool => $pool->is_active()
					)
				)
			),
			array(),
			array(
				'action' => 'distribution.render',
				'source' => 'manual',
			)
		);
		$result_pool_name = '';
		if ( is_array( $result ) ) {
			$result_pool = $this->pools->find( (int) ( $result['pool_id'] ?? 0 ) );
			if ( null !== $result_pool ) {
				$result_pool_name = $result_pool->name();
			}
		}

		$view = $this->view;

		$has_successful_result = is_array( $result )
			&& ! empty( $result['success'] )
			&& is_string( $result['code'] ?? null );

		$intent_token = '';
		if ( ! $has_successful_result ) {
			$intent_token = $this->boundary->execute(
				fn(): string => $this->intents->create( get_current_user_id() ),
				'',
				array(
					'action' => 'distribution.intent_create',
					'source' => 'manual',
				)
			);
		}

		$template = VOUCHER_MANAGER_PATH . 'templates/admin/distribution.php';

		if ( is_readable( $template ) ) {
			require $template;
		}
	}

	public function distribute(): void {
		$this->guard();
		check_admin_referer( 'voucher_manager_distribute_code' );

		$user_id      = get_current_user_id();
		$pool_id      = isset( $_POST['pool_id'] ) ? absint( $_POST['pool_id'] ) : 0;
		$intent_token = isset( $_POST['distribution_intent'] )
			? sanitize_text_field( wp_unslash( $_POST['distribution_intent'] ) )
			: '';

		if ( ! $this->intents->consume( $intent_token, $user_id ) ) {
			$existing_result = $this->wait_for_replay_delivery( $intent_token, $user_id );

			if ( null !== $existing_result ) {
				$this->redirect_to_result( $existing_result );
			}

			$this->redirect_with_notice( 'replayed' );
		}

		$fallback = new DistributionResult(
			false,
			null,
			__( 'Distribution could not be completed. Please try again.', 'mjs-productions-voucher-manager' ),
			null
		);

		$result = $this->boundary->execute(
			fn(): DistributionResult => $this->service->distribute( $pool_id ),
			$fallback,
			array(
				'action'  => 'distribution.execute',
				'pool_id' => $pool_id,
				'source'  => 'manual',
			),
			OperationalEvent::DISTRIBUTION_FAILED
		);

		$result_token = $this->results->store( $intent_token, $user_id, $result, $pool_id );

		if ( null === $result_token ) {
			$this->render_direct_result( $result, $pool_id );
		}

		$this->redirect_to_result( $result_token );
	}

	private function wait_for_replay_delivery( string $intent_token, int $user_id ): ?string {
		for ( $attempt = 0; $attempt < 20; ++$attempt ) {
			$result_token = $this->results->create_delivery_for_intent( $intent_token, $user_id );
			if ( null !== $result_token ) {
				return $result_token;
			}

			usleep( 50000 );
		}

		return null;
	}

	private function redirect_to_result( string $result_token ): void {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'   => 'voucher-manager-distribution',
					'result' => $result_token,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	private function redirect_with_notice( string $notice ): void {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => 'voucher-manager-distribution',
					'vm_notice' => $notice,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	private function render_direct_result( DistributionResult $distribution_result, int $pool_id ): void {
		$result = array(
			'success'   => $distribution_result->success(),
			'code'      => $distribution_result->code(),
			'message'   => $distribution_result->message(),
			'remaining' => $distribution_result->remaining(),
			'pool_id'   => $pool_id,
		);
		$result_pool      = $this->pools->find( $pool_id );
		$result_pool_name = null === $result_pool ? '' : $result_pool->name();
		$view             = $this->view;

		$template = VOUCHER_MANAGER_PATH . 'templates/admin/distribution-direct-result.php';
		if ( is_readable( $template ) ) {
			require $template;
		}
		exit;
	}

	private function guard(): void {
		if ( ! current_user_can( Capabilities::DISTRIBUTE_CODES ) ) {
			wp_die(
				esc_html__( 'You are not allowed to access this page.', 'mjs-productions-voucher-manager' )
			);
		}
	}
}
