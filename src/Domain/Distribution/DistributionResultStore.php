<?php
/**
 * One-time Distribution result store.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Distribution;

interface DistributionResultStore {

	public function store( string $intent_token, int $user_id, DistributionResult $result, int $pool_id ): ?string;

	/** @return array{success:bool,code:?string,message:string,remaining:?int,pool_id:int}|null */
	public function consume( string $result_token, int $user_id ): ?array;

	public function create_delivery_for_intent( string $intent_token, int $user_id ): ?string;
}
