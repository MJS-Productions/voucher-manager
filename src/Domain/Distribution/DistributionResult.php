<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Distribution;

final class DistributionResult {
	public function __construct(
		private readonly bool $success,
		private readonly ?string $code,
		private readonly string $message,
		private readonly ?int $remaining
	) {}
	public function success(): bool { return $this->success; }
	public function code(): ?string { return $this->code; }
	public function message(): string { return $this->message; }
	public function remaining(): ?int { return $this->remaining; }
}
