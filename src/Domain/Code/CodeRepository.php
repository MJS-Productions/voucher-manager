<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Code;

interface CodeRepository {
	/** @param array<string> $codes */
	public function insert_batch( int $pool_id, int $import_id, array $codes ): int;
	public function delete_available_by_import( int $import_id ): int;
	public function count_assigned_by_import( int $import_id ): int;
}
