<?php
/**
 * WordPress Activity retention repository.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Infrastructure\WordPress;

use RuntimeException;
use VoucherManager\Domain\Activity\ActivityRetentionRepository;

/**
 * Performs bounded deletion only in the Voucher Manager Activity table.
 */
final class WpdbActivityRetentionRepository implements ActivityRetentionRepository {

	public function delete_oldest_before( string $utc_cutoff, int $limit ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'vm_logs';
		$limit = max( 1, min( 1000, $limit ) );

		$sql = "DELETE FROM {$table}
			WHERE id IN (
				SELECT id FROM (
					SELECT id
					FROM {$table}
					WHERE created_at < %s
					ORDER BY id ASC
					LIMIT %d
				) AS voucher_manager_expired_activity
			)";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted = $wpdb->query( $wpdb->prepare( $sql, $utc_cutoff, $limit ) );

		if ( false === $deleted ) {
			throw new RuntimeException( 'Expired operational Activity could not be deleted.' );
		}

		return (int) $deleted;
	}
}
