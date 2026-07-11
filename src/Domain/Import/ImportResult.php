<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Import;

final class ImportResult {
	public function __construct(
		private readonly int $import_id,
		private readonly int $total,
		private readonly int $imported,
		private readonly int $skipped,
		private readonly int $invalid
	) {}
	public function import_id(): int { return $this->import_id; }
	public function total(): int { return $this->total; }
	public function imported(): int { return $this->imported; }
	public function skipped(): int { return $this->skipped; }
	public function invalid(): int { return $this->invalid; }
}
