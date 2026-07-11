<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Import;

interface ImportRepository {
	public function start( int $pool_id, string $filename, string $file_type ): int;
	public function complete( int $id, int $total, int $imported, int $skipped, int $invalid ): bool;
	public function fail( int $id, int $total, int $imported, int $skipped, int $invalid ): bool;
	/** @return array<ImportRecord> */
	public function recent( int $limit = 20 ): array;
	public function find( int $id ): ?ImportRecord;
	public function mark_rolled_back( int $id ): bool;
}
