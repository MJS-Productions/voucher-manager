<?php
/**
 * One-Time Code inventory read repository.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Code;

interface CodeInventoryRepository {

	/**
	 * @return array<CodeInventoryRecord>
	 */
	public function search(
		int $pool_id,
		?CodeStatus $status,
		?int $import_id,
		int $limit,
		int $offset
	): array;

	public function count_matching( int $pool_id, ?CodeStatus $status, ?int $import_id ): int;

	/**
	 * @return array{total:int,available:int,assigned:int}
	 */
	public function counts( int $pool_id ): array;

	/**
	 * @return array<int,array{id:int,filename:string}>
	 */
	public function import_options( int $pool_id ): array;
}
