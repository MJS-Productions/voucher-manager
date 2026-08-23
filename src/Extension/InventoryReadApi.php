<?php
/**
 * Public inventory read extension contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

use VoucherManager\Domain\Code\CodeStatus;

/**
 * Provides supported read-only inventory access for extensions.
 */
final class InventoryReadApi {

	/**
	 * Returns inventory counts for one pool.
	 *
	 * @return array{total:int,available:int,assigned:int}
	 */
	public function for_pool( int $pool_id ): array {
		$inventories = $this->for_pools( array( $pool_id ) );

		return $inventories[ $pool_id ] ?? $this->empty_inventory();
	}

	/**
	 * Returns inventory counts indexed by pool ID.
	 *
	 * @param array<int> $pool_ids Pool IDs to inspect.
	 * @return array<int,array{total:int,available:int,assigned:int}>
	 */
	public function for_pools( array $pool_ids ): array {
		global $wpdb;

		$pool_ids = array_values(
			array_unique(
				array_filter(
					array_map(
						static fn( int $pool_id ): int => abs( $pool_id ),
						$pool_ids
					)
				)
			)
		);

		if ( empty( $pool_ids ) ) {
			return array();
		}

		$placeholders = implode( ', ', array_fill( 0, count( $pool_ids ), '%d' ) );
		$table        = $wpdb->prefix . 'vm_codes';
		$query_args   = array_merge( array( $table ), $pool_ids );

		// The identifier and every pool ID are supplied through wpdb placeholders.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pool_id, status, COUNT(*) AS amount
					FROM %i
					WHERE pool_id IN ({$placeholders})
					GROUP BY pool_id, status",
				...$query_args
			),
			ARRAY_A
		);

		$inventories = array_fill_keys(
			$pool_ids,
			$this->empty_inventory()
		);

		foreach ( is_array( $results ) ? $results : array() as $result ) {
			$pool_id = (int) $result['pool_id'];
			$status  = (string) $result['status'];
			$amount  = (int) $result['amount'];

			if ( ! isset( $inventories[ $pool_id ] ) ) {
				continue;
			}

			$inventories[ $pool_id ]['total'] += $amount;

			if ( CodeStatus::AVAILABLE->value === $status ) {
				$inventories[ $pool_id ]['available'] = $amount;
			}

			if ( CodeStatus::ASSIGNED->value === $status ) {
				$inventories[ $pool_id ]['assigned'] = $amount;
			}
		}

		return $inventories;
	}

	/**
	 * Returns an empty inventory value.
	 *
	 * @return array{total:int,available:int,assigned:int}
	 */
	private function empty_inventory(): array {
		return array(
			'total'     => 0,
			'available' => 0,
			'assigned'  => 0,
		);
	}
}
