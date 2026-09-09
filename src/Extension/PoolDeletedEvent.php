<?php
/**
 * Public pool-deleted extension event.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

/**
 * Publishes semantic pool-deleted events for extensions.
 */
final class PoolDeletedEvent {

	public const HOOK = 'voucher_manager_pool_deleted';

	/**
	 * Publishes a pool-deleted event without allowing extension failures
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
			// turn a completed pool deletion into an apparent failure.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				sprintf(
					'Voucher Manager pool-deleted listener failure: %s',
					$exception::class
				)
			);
		}
	}
}
