<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Import;

final class ImportRecord {
	public function __construct(
		private readonly int $id,
		private readonly int $pool_id,
		private readonly string $pool_name,
		private readonly string $filename,
		private readonly string $file_type,
		private readonly string $status,
		private readonly int $total_rows,
		private readonly int $imported_rows,
		private readonly int $skipped_rows,
		private readonly int $invalid_rows,
		private readonly string $created_at
	) {}
	public function id(): int { return $this->id; }
	public function pool_id(): int { return $this->pool_id; }
	public function pool_name(): string { return $this->pool_name; }
	public function filename(): string { return $this->filename; }
	public function file_type(): string { return $this->file_type; }
	public function status(): string { return $this->status; }
	public function total_rows(): int { return $this->total_rows; }
	public function imported_rows(): int { return $this->imported_rows; }
	public function skipped_rows(): int { return $this->skipped_rows; }
	public function invalid_rows(): int { return $this->invalid_rows; }
	public function created_at(): string { return $this->created_at; }
}
