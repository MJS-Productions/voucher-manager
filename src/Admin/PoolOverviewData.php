<?php
/**
 * Pool overview data provider.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Pool\Pool;
use VoucherManager\Extension\InventoryReadApi;

/**
 * Adds inventory information to pool entities for administration views.
 */
final class PoolOverviewData {

	private InventoryReadApi $inventory;

	public function __construct() {
		$this->inventory = new InventoryReadApi();
	}

	/**
	 * Build pool overview rows.
	 *
	 * @param array<Pool> $pools Pools to enrich.
	 * @return array<int,array{pool:Pool,total:int,available:int,assigned:int}>
	 */
	public function rows( array $pools ): array {
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

		$inventories = $this->inventory->for_pools( $pool_ids );

		return array_map(
			static function ( Pool $pool ) use ( $inventories ): array {
				$pool_id   = (int) $pool->id();
				$inventory = $inventories[ $pool_id ] ?? array(
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
