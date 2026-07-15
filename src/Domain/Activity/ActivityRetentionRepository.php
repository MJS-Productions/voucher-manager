<?php
/**
 * Activity retention repository contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Activity;

interface ActivityRetentionRepository {

	public function delete_oldest_before( string $utc_cutoff, int $limit ): int;
}
