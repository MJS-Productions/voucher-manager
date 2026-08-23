<?php
/**
 * Public pool read extension contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

use VoucherManager\Domain\Pool\Pool;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;

/**
 * Provides supported read-only pool access for extensions.
 */
final class PoolReadApi {

	private WpdbPoolRepository $pools;

	public function __construct() {
		$this->pools = new WpdbPoolRepository();
	}

	/**
	 * Returns all pools.
	 *
	 * @return array<Pool>
	 */
	public function all(): array {
		return $this->pools->all();
	}

	/**
	 * Returns a pool by ID.
	 */
	public function find( int $pool_id ): ?Pool {
		return $this->pools->find( $pool_id );
	}
}
