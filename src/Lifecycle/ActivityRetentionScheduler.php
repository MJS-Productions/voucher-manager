<?php
/**
 * Activity retention Cron lifecycle.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Lifecycle;

use VoucherManager\Domain\Activity\ActivityRetentionService;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Domain\Settings\Settings;
use VoucherManager\Infrastructure\WordPress\WpSettingsRepository;
use VoucherManager\Infrastructure\WordPress\WpdbActivityRetentionRepository;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;

/**
 * Reconciles the daily cleanup schedule and runs bounded cleanup.
 */
final class ActivityRetentionScheduler {

	public const HOOK = 'voucher_manager_cleanup_activity';

	public function register(): void {
		add_action( self::HOOK, array( $this, 'run' ) );
		add_action( 'plugins_loaded', array( $this, 'reconcile_from_wordpress' ), 20 );
	}

	/**
	 * WordPress action bridge.
	 *
	 * WordPress may pass an empty string to callbacks for actions without
	 * explicit arguments, so keep that boundary separate from typed internals.
	 */
	public function reconcile_from_wordpress(): void {
		$this->reconcile();
	}

	public function reconcile( ?Settings $settings = null ): void {
		$settings = $settings ?? ( new WpSettingsRepository() )->get();

		if ( 0 === $settings->activity_retention_days() ) {
			$this->unschedule();
			return;
		}

		if ( false === wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::HOOK );
		}
	}

	public function run(): void {
		$settings = ( new WpSettingsRepository() )->get();

		if ( 0 === $settings->activity_retention_days() ) {
			$this->unschedule();
			return;
		}

		$retention_days = $settings->activity_retention_days();
		$logger         = new OperationalLogger( new WpdbLogRepository() );

		try {
			$deleted = ( new ActivityRetentionService( new WpdbActivityRetentionRepository() ) )
				->cleanup( $retention_days );

			if ( 0 < $deleted ) {
				$logger->info(
					OperationalEvent::ACTIVITY_CLEANUP_COMPLETED,
					'Expired Activity entries were cleaned up.',
					array(
						'deleted_count'  => $deleted,
						'retention_days' => $retention_days,
					)
				);
			}
		} catch ( \Throwable $exception ) {
			$logger->error(
				OperationalEvent::ACTIVITY_CLEANUP_FAILED,
				'Automatic Activity cleanup failed.',
				array(
					'retention_days' => $retention_days,
					'exception_class' => $exception::class,
				)
			);

			error_log(
				sprintf(
					'Voucher Manager Activity cleanup failed: %s',
					$exception::class
				)
			);
		}
	}

	public function unschedule(): void {
		wp_clear_scheduled_hook( self::HOOK );
	}
}
