<?php
/**
 * Bounded Activity retention service.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Activity;

use UnexpectedValueException;

/**
 * Selects and removes old operational Activity in small, predictable batches.
 */
final class ActivityRetentionService {

	public const BATCH_SIZE = 500;

	public function __construct(
		private readonly ActivityRetentionRepository $repository
	) {
	}

	/**
	 * Runs one bounded retention batch.
	 *
	 * Without an archive hand-off, all selected candidates are deleted exactly
	 * as in Voucher Manager's standalone retention flow.
	 *
	 * When an archive hand-off is supplied, it receives the concrete candidate
	 * records and must return only IDs whose archive persistence is confirmed.
	 * Only those confirmed candidate IDs may be deleted.
	 *
	 * @param null|callable(array<int,array{
	 *   id:int,
	 *   event_type:string,
	 *   message:string,
	 *   context:?string,
	 *   created_at:string
	 * }>):array<int> $archive_handoff Optional archive confirmation hand-off.
	 */
	public function cleanup(
		int $retention_days,
		?int $now_utc = null,
		?callable $archive_handoff = null
	): int {
		if ( 0 >= $retention_days ) {
			return 0;
		}

		$candidates = $this->repository->find_oldest_before(
			$this->cutoff( $retention_days, $now_utc ),
			self::BATCH_SIZE
		);

		if ( array() === $candidates ) {
			return 0;
		}

		$candidate_ids = $this->candidate_ids( $candidates );
		$delete_ids    = $candidate_ids;

		if ( null !== $archive_handoff ) {
			$confirmed = $archive_handoff( $candidates );
			$delete_ids = $this->confirmed_candidate_ids( $confirmed, $candidate_ids );
		}

		if ( array() === $delete_ids ) {
			return 0;
		}

		return $this->repository->delete_by_ids( $delete_ids );
	}

	public function cutoff( int $retention_days, ?int $now_utc = null ): string {
		$now = $now_utc ?? time();

		return gmdate( 'Y-m-d H:i:s', $now - ( $retention_days * DAY_IN_SECONDS ) );
	}

	/**
	 * @param array<int,array{
	 *   id:int,
	 *   event_type:string,
	 *   message:string,
	 *   context:?string,
	 *   created_at:string
	 * }> $candidates Activity retention candidates.
	 * @return array<int>
	 */
	private function candidate_ids( array $candidates ): array {
		$ids = array();

		foreach ( $candidates as $candidate ) {
			$id = $candidate['id'] ?? 0;
			if ( ! is_int( $id ) || 0 >= $id ) {
				throw new UnexpectedValueException( 'Activity retention candidate IDs must be positive integers.' );
			}

			$ids[] = $id;
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * @param mixed      $confirmed     Archive hand-off result.
	 * @param array<int> $candidate_ids Selected candidate IDs.
	 * @return array<int>
	 */
	private function confirmed_candidate_ids( mixed $confirmed, array $candidate_ids ): array {
		if ( ! is_array( $confirmed ) ) {
			throw new UnexpectedValueException( 'Activity archive hand-off must return confirmed Activity IDs.' );
		}

		$confirmed_ids = array();

		foreach ( $confirmed as $id ) {
			if ( ! is_int( $id ) || 0 >= $id || ! in_array( $id, $candidate_ids, true ) ) {
				throw new UnexpectedValueException( 'Activity archive hand-off confirmed an invalid candidate ID.' );
			}

			$confirmed_ids[] = $id;
		}

		return array_values( array_unique( $confirmed_ids ) );
	}
}
