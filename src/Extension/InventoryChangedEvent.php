<?php
/**
 * Public inventory-change extension event.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

/**
 * Publishes semantic inventory-change events for extensions.
 */
final class InventoryChangedEvent {

	public const HOOK = 'voucher_manager_inventory_changed';

	public const REASON_DISTRIBUTION = 'distribution';
	public const REASON_IMPORT       = 'import';
	public const REASON_ROLLBACK     = 'rollback';
	public const REASON_DELETION     = 'deletion';

	/**
	 * Publishes an inventory-change event without allowing extension failures
	 * to hide or reverse an already completed Voucher Manager operation.
	 */
	public static function dispatch( int $pool_id, string $reason ): void {
		if ( 0 >= $pool_id || ! in_array( $reason, self::reasons(), true ) ) {
			return;
		}

		try {
			do_action( self::HOOK, $pool_id, $reason );
		} catch ( \Throwable $exception ) {
			// This is a last-resort diagnostic. Extension listeners must never
			// turn a completed inventory operation into an apparent failure.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				sprintf(
					'Voucher Manager inventory-change listener failure [%s]: %s',
					$reason,
					$exception::class
				)
			);
		}
	}

	/**
	 * @return array<int,string>
	 */
	private static function reasons(): array {
		return array(
			self::REASON_DISTRIBUTION,
			self::REASON_IMPORT,
			self::REASON_ROLLBACK,
			self::REASON_DELETION,
		);
	}
}
