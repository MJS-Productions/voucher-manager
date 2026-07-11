<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Distribution;

use VoucherManager\Domain\Code\CodeRepository;
use VoucherManager\Domain\Log\LogRepository;
use VoucherManager\Domain\Pool\PoolRepository;

final class DistributionService {
	public function __construct(
		private readonly PoolRepository $pools,
		private readonly CodeRepository $codes,
		private readonly LogRepository $logs
	) {}

	public function distribute( int $pool_id ): DistributionResult {
		$pool = $this->pools->find( $pool_id );
		if ( null === $pool || ! $pool->is_active() ) {
			return new DistributionResult( false, null, 'Pool is unavailable.', 0 );
		}

		$claimed = $this->codes->claim_next_available( $pool_id );
		if ( null === $claimed ) {
			$this->logs->add( 'distribution_empty', 'No available code could be distributed.', array( 'pool_id' => $pool_id ) );
			return new DistributionResult( false, null, 'No available codes remain in this pool.', 0 );
		}

		$remaining = $this->codes->count_available( $pool_id );
		$this->logs->add(
			'code_distributed',
			'An available code was distributed.',
			array( 'pool_id' => $pool_id, 'code_id' => $claimed['id'], 'remaining' => $remaining )
		);
		return new DistributionResult( true, $claimed['code'], 'Code distributed.', $remaining );
	}
}
