<?php
/**
 * Voucher pool entity.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Pool;

/**
 * Immutable representation of a voucher pool.
 */
final class Pool {

	public function __construct(
		private readonly ?int $id,
		private readonly string $name,
		private readonly string $slug,
		private readonly string $description,
		private readonly int $warning_threshold,
		private readonly string $status,
		private readonly string $created_at,
		private readonly string $updated_at
	) {
	}

	public function id(): ?int { return $this->id; }
	public function name(): string { return $this->name; }
	public function slug(): string { return $this->slug; }
	public function description(): string { return $this->description; }
	public function warning_threshold(): int { return $this->warning_threshold; }
	public function status(): string { return $this->status; }
	public function is_active(): bool { return 'active' === $this->status; }
	public function created_at(): string { return $this->created_at; }
	public function updated_at(): string { return $this->updated_at; }
}
