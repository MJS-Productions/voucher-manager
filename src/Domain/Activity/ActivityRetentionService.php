<?php
/**
 * Bounded Activity retention service.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Activity;

/**
 * Deletes only old operational Activity in small, predictable batches.
 */
final class ActivityRetentionService {

	public const BATCH_SIZE = 500;

	public function __construct(
		private readonly ActivityRetentionRepository $repository
	) {
	}

	public function cleanup( int $retention_days, ?int $now_utc = null ): int {
		if ( 0 >= $retention_days ) {
			return 0;
		}

		return $this->repository->delete_oldest_before(
			$this->cutoff( $retention_days, $now_utc ),
			self::BATCH_SIZE
		);
	}

	public function cutoff( int $retention_days, ?int $now_utc = null ): string {
		$now = $now_utc ?? time();

		return gmdate( 'Y-m-d H:i:s', $now - ( $retention_days * DAY_IN_SECONDS ) );
	}
}
