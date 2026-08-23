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
		return $this->service->distribute( $pool_id );
	}
}
