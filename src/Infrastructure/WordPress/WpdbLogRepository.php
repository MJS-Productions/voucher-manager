<?php
/**
 * WordPress database log repository.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Infrastructure\WordPress;

use RuntimeException;
use VoucherManager\Domain\Log\LogRepository;

/**
 * Persists operational events in the Voucher Manager log table.
 */
final class WpdbLogRepository implements LogRepository {

	/**
	 * Persist an operational event.
	 *
	 * @param array<string,mixed> $context Event context.
	 *
	 * @throws RuntimeException When WordPress cannot persist the event.
	 */
	public function add(
		string $event_type,
		string $message,
		array $context = array()
	): void {
		global $wpdb;

		$inserted = $wpdb->insert(
			$wpdb->prefix . 'vm_logs',
			array(
				'event_type' => $event_type,
				'message'    => $message,
				'context'    => wp_json_encode( $context ),
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			throw new RuntimeException( 'Operational event could not be persisted.' );
		}
	}
}
