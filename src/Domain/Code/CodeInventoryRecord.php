<?php
/**
 * Privacy-safe inventory record.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Code;

/**
 * Read-only code inventory data without the complete voucher value.
 */
final class CodeInventoryRecord {

	public function __construct(
		private readonly int $id,
		private readonly int $pool_id,
		private readonly ?int $import_id,
		private readonly string $code_suffix,
		private readonly CodeStatus $status,
		private readonly string $imported_at,
		private readonly ?string $assigned_at
	) {
	}

	public function id(): int { return $this->id; }
	public function pool_id(): int { return $this->pool_id; }
	public function import_id(): ?int { return $this->import_id; }
	public function code_suffix(): string { return $this->code_suffix; }
	public function status(): CodeStatus { return $this->status; }
	public function imported_at(): string { return $this->imported_at; }
	public function assigned_at(): ?string { return $this->assigned_at; }
}
