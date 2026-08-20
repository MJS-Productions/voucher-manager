<?php
/**
 * Plugin deactivation lifecycle.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Lifecycle;

use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;

/**
 * Removes scheduled work while preserving all data and settings.
 */
final class Deactivator {

	public static function deactivate(): void {
		( new ActivityRetentionScheduler() )->unschedule();

		( new OperationalLogger( new WpdbLogRepository() ) )->info(
			OperationalEvent::PLUGIN_DEACTIVATED,
			'Voucher Manager was deactivated.'
		);
	}
}
