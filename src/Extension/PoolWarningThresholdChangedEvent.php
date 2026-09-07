<?php
/**
 * Public pool warning-threshold change extension event.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

/**
 * Publishes semantic pool warning-threshold change events for extensions.
 */
final class PoolWarningThresholdChangedEvent {

	public const HOOK = 'voucher_manager_pool_warning_threshold_changed';

	/**
	 * Publishes a threshold-change event without allowing extension failures
	 * to hide or reverse an already completed Voucher Manager operation.
	 */
	public static function dispatch( int $pool_id ): void {
		if ( 0 >= $pool_id ) {
			return;
		}

		try {
			do_action( self::HOOK, $pool_id );
		} catch ( \Throwable $exception ) {
			// This is a last-resort diagnostic. Extension listeners must never
			// turn a completed pool update into an apparent failure.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				sprintf(
					'Voucher Manager pool warning-threshold listener failure: %s',
					$exception::class
				)
			);
		}
	}
}
