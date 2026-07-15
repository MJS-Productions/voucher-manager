<?php
/**
 * Plugin deactivation lifecycle.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Lifecycle;

/**
 * Removes scheduled work while preserving all data and settings.
 */
final class Deactivator {

	public static function deactivate(): void {
		( new ActivityRetentionScheduler() )->unschedule();
	}
}
