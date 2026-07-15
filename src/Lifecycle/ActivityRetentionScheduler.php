<?php
/**
 * Activity retention Cron lifecycle.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Lifecycle;

use VoucherManager\Domain\Activity\ActivityRetentionService;
use VoucherManager\Domain\Settings\Settings;
use VoucherManager\Infrastructure\WordPress\WpSettingsRepository;
use VoucherManager\Infrastructure\WordPress\WpdbActivityRetentionRepository;

/**
 * Reconciles the daily cleanup schedule and runs bounded cleanup.
 */
final class ActivityRetentionScheduler {

	public const HOOK = 'voucher_manager_cleanup_activity';

	public function register(): void {
		add_action( self::HOOK, array( $this, 'run' ) );
		add_action( 'plugins_loaded', array( $this, 'reconcile' ), 20 );
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

		try {
			( new ActivityRetentionService( new WpdbActivityRetentionRepository() ) )
				->cleanup( $settings->activity_retention_days() );
		} catch ( \Throwable $exception ) {
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
