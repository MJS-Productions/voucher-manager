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
 * Selects and deletes bounded retention candidates only in Activity storage.
 */
final class WpdbActivityRetentionRepository implements ActivityRetentionRepository {

	/**
	 * @return array<int,array{
	 *   id:int,
	 *   event_type:string,
	 *   message:string,
	 *   context:?string,
	 *   created_at:string
	 * }>
	 */
	public function find_oldest_before( string $utc_cutoff, int $limit ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'vm_logs';
		$limit = max( 1, min( 1000, $limit ) );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, event_type, message, context, created_at
				FROM %i
				WHERE created_at < %s
				ORDER BY id ASC
				LIMIT %d',
				$table,
				$utc_cutoff,
				$limit
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			throw new RuntimeException( 'Expired operational Activity could not be selected.' );
		}

		$candidates = array();

		foreach ( $rows as $row ) {
			$candidates[] = array(
				'id'         => absint( $row['id'] ?? 0 ),
				'event_type' => (string) ( $row['event_type'] ?? '' ),
				'message'    => (string) ( $row['message'] ?? '' ),
				'context'    => isset( $row['context'] ) ? (string) $row['context'] : null,
				'created_at' => (string) ( $row['created_at'] ?? '' ),
			);
		}

		return $candidates;
	}

	/**
	 * @param array<int> $ids Activity record IDs.
	 */
	public function delete_by_ids( array $ids ): int {
		global $wpdb;

		$ids = array_values(
			array_unique(
				array_filter(
					array_map( 'absint', $ids )
				)
			)
		);

		if ( array() === $ids ) {
			return 0;
		}

		$table        = $wpdb->prefix . 'vm_logs';
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$args         = array_merge( array( $table ), $ids );

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM %i WHERE id IN ({$placeholders})",
				...$args
			)
		);

		if ( false === $deleted ) {
			throw new RuntimeException( 'Confirmed operational Activity could not be deleted.' );
		}

		return (int) $deleted;
	}
}
