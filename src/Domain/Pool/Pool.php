<?php
declare(strict_types=1);

namespace VoucherManager\Domain\Pool;

final class Pool {

    public function __construct(
        private readonly ?int $id,
        private string $name,
        private string $description,
        private int $low_stock_threshold,
        private bool $active = true
    ) {}

    public function id(): ?int {
        return $this->id;
    }

    public function name(): string {
        return $this->name;
    }

    public function threshold(): int {
        return $this->low_stock_threshold;
    }

    public function is_active(): bool {
        return $this->active;
    }
}
