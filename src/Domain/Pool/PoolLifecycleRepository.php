<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Pool;

interface PoolLifecycleRepository {
	/** @return array{total:int,available:int,assigned:int,imports:int}|null */
	public function deletion_summary( int $pool_id ): ?array;
	public function delete_available_codes( int $pool_id ): int;
	/** @return array{deleted_code_count:int,deleted_import_count:int} */
	public function delete_pool_with_data( int $pool_id ): array;
}
