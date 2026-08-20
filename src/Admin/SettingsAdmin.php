<?php
/**
 * Voucher Manager Settings administration.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Domain\Settings\Settings;
use VoucherManager\Infrastructure\WordPress\WpSettingsRepository;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;
use VoucherManager\Lifecycle\ActivityRetentionScheduler;

/**
 * Registers and saves the minimal production-hardening settings.
 */
final class SettingsAdmin {

	private WpSettingsRepository $repository;
	private SettingsViewModel $view;
	private OperationalLogger $logger;

	public function __construct() {
		$this->repository = new WpSettingsRepository();
		$this->view       = new SettingsViewModel();
		$this->logger     = new OperationalLogger( new WpdbLogRepository() );
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_voucher_manager_save_settings', array( $this, 'save' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			'voucher-manager',
			__( 'Voucher Manager Settings', 'voucher-manager' ),
			_x( 'Settings', 'admin menu label', 'voucher-manager' ),
			'manage_options',
			'voucher-manager-settings',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		$this->guard();

		$settings = $this->repository->get();
		$view     = $this->view;
		$notice   = isset( $_GET['vm_notice'] ) ? sanitize_key( wp_unslash( $_GET['vm_notice'] ) ) : '';

		$template = VOUCHER_MANAGER_PATH . 'templates/admin/settings.php';
		if ( is_readable( $template ) ) {
			require $template;
		}
	}

	public function save(): void {
		$this->guard();
		check_admin_referer( 'voucher_manager_save_settings' );

		$retention = isset( $_POST['activity_retention_days'] )
			? (int) sanitize_text_field( wp_unslash( $_POST['activity_retention_days'] ) )
			: Settings::DEFAULT_ACTIVITY_RETENTION_DAYS;

		$delete_requested = isset( $_POST['delete_data_on_uninstall'] )
			&& '1' === sanitize_text_field( wp_unslash( $_POST['delete_data_on_uninstall'] ) );

		$confirmed = isset( $_POST['confirm_delete_data_on_uninstall'] )
			&& '1' === sanitize_text_field( wp_unslash( $_POST['confirm_delete_data_on_uninstall'] ) );

		$current = $this->repository->get();

		if ( $delete_requested && ! $current->delete_data_on_uninstall() && ! $confirmed ) {
			$this->redirect( 'uninstall_confirmation_required' );
		}

		$settings = Settings::from_array(
			array(
				'activity_retention_days'  => $retention,
				'delete_data_on_uninstall' => $delete_requested,
			)
		);

		$retention_changed = $current->activity_retention_days() !== $settings->activity_retention_days();
		$uninstall_behavior_changed = $current->delete_data_on_uninstall() !== $settings->delete_data_on_uninstall();
		$saved = $this->repository->save( $settings );

		if ( $saved && ( $retention_changed || $uninstall_behavior_changed ) ) {
			$this->logger->info(
				OperationalEvent::SETTINGS_UPDATED,
				'Voucher Manager settings were updated.',
				array(
					'retention_changed'          => $retention_changed,
					'uninstall_behavior_changed' => $uninstall_behavior_changed,
				)
			);
		}

		( new ActivityRetentionScheduler() )->reconcile( $settings );
		$this->redirect( 'settings_saved' );
	}

	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'voucher-manager' ) );
		}
	}

	private function redirect( string $notice ): void {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => 'voucher-manager-settings',
					'vm_notice' => $notice,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
