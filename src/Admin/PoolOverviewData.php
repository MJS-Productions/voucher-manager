<?php
/**
 * Pool overview data provider.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Code\CodeStatus;
use VoucherManager\Domain\Pool\Pool;

/**
 * Adds inventory information to pool entities for administration views.
 */
final class PoolOverviewData {

	/**
	 * Build pool overview rows.
	 *
	 * @param array<Pool> $pools Pools to enrich.
	 * @return array<int,array{pool:Pool,total:int,available:int,assigned:int}>
	 */
	public function rows( array $pools ): array {
		global $wpdb;

		if ( empty( $pools ) ) {
			return array();
		}

		$pool_ids = array_values(
			array_filter(
				array_map(
					static fn( Pool $pool ): int => (int) $pool->id(),
					$pools
				)
			)
		);

		if ( empty( $pool_ids ) ) {
			return array();
		}

		$placeholders = implode( ', ', array_fill( 0, count( $pool_ids ), '%d' ) );
		$table        = $wpdb->prefix . 'vm_codes';

		$query_args = array_merge( array( $table ), $pool_ids );

		// The identifier and every pool ID are supplied through wpdb placeholders.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$prepared = $wpdb->prepare(
			"SELECT pool_id, status, COUNT(*) AS amount
				FROM %i
				WHERE pool_id IN ({$placeholders})
				GROUP BY pool_id, status",
			...$query_args
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $prepared, ARRAY_A );
		$counts  = array();

		foreach ( is_array( $results ) ? $results : array() as $result ) {
			$pool_id = (int) $result['pool_id'];
			$status  = (string) $result['status'];
			$amount  = (int) $result['amount'];

			if ( ! isset( $counts[ $pool_id ] ) ) {
				$counts[ $pool_id ] = array(
					'total'     => 0,
					'available' => 0,
					'assigned'  => 0,
				);
			}

			$counts[ $pool_id ]['total'] += $amount;

			if ( CodeStatus::AVAILABLE->value === $status ) {
				$counts[ $pool_id ]['available'] = $amount;
			}

			if ( CodeStatus::ASSIGNED->value === $status ) {
				$counts[ $pool_id ]['assigned'] = $amount;
			}
		}

		return array_map(
			static function ( Pool $pool ) use ( $counts ): array {
				$pool_id   = (int) $pool->id();
				$inventory = $counts[ $pool_id ] ?? array(
					'total'     => 0,
					'available' => 0,
					'assigned'  => 0,
				);

				return array(
					'pool'      => $pool,
					'total'     => $inventory['total'],
					'available' => $inventory['available'],
					'assigned'  => $inventory['assigned'],
				);
			},
			$pools
		);
	}
}
