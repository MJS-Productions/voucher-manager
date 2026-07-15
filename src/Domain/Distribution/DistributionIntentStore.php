<?php
/**
 * One-use Distribution intent store.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Distribution;

interface DistributionIntentStore {

	public function create( int $user_id ): string;

	public function consume( string $token, int $user_id ): bool;
}
