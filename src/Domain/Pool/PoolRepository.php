<?php
/**
 * Pool persistence contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Pool;

interface PoolRepository {
	/** @return array<Pool> */
	public function all(): array;
	public function find( int $id ): ?Pool;
	public function create( string $name, string $description, int $warning_threshold, bool $active ): int;
	public function update( int $id, string $name, string $description, int $warning_threshold, bool $active ): bool;
	public function set_active( int $id, bool $active ): bool;
	public function delete( int $id ): bool;
	public function code_count( int $id ): int;
}
