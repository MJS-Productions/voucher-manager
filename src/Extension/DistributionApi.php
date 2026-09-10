<?php
/**
 * Public distribution extension contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

use VoucherManager\Domain\Distribution\DistributionResult;
use VoucherManager\Domain\Distribution\DistributionService;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Infrastructure\WordPress\WpdbCodeRepository;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;

/**
 * Provides the supported distribution entry point for extensions.
 */
final class DistributionApi {

	private DistributionService $service;

	public function __construct() {
		$this->service = new DistributionService(
			new WpdbPoolRepository(),
			new WpdbCodeRepository(),
			new OperationalLogger( new WpdbLogRepository() )
		);
	}

	/**
	 * Distributes the next available One-Time Code from a pool.
	 */
	public function distribute( int $pool_id ): DistributionResult {
		$result = $this->service->distribute( $pool_id );

		$this->dispatch_inventory_change( $pool_id, $result );

		return $result;
	}

	/**
	 * Distributes while treating an already-empty pool as an expected result.
	 *
	 * This preserves successful distribution Activity, including the transition
	 * when the last available One-Time Code is consumed, while suppressing the
	 * distribution.empty Activity entry for an already-empty pool.
	 */
	public function distribute_with_expected_empty_result( int $pool_id ): DistributionResult {
		$result = $this->service->distribute( $pool_id, false );

		$this->dispatch_inventory_change( $pool_id, $result );

		return $result;
	}

	private function dispatch_inventory_change( int $pool_id, DistributionResult $result ): void {
		if ( $result->success() ) {
			InventoryChangedEvent::dispatch(
				$pool_id,
				InventoryChangedEvent::REASON_DISTRIBUTION
			);
		}
	}
}
