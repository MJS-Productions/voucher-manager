<?php
/**
 * Activity retention repository contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Activity;

interface ActivityRetentionRepository {

	/**
	 * Returns the oldest active Activity records before the retention cutoff.
	 *
	 * @return array<int,array{
	 *   id:int,
	 *   event_type:string,
	 *   message:string,
	 *   context:?string,
	 *   created_at:string
	 * }>
	 */
	public function find_oldest_before( string $utc_cutoff, int $limit ): array;

	/**
	 * Deletes only the supplied active Activity record IDs.
	 *
	 * @param array<int> $ids Activity record IDs.
	 */
	public function delete_by_ids( array $ids ): int;
}
