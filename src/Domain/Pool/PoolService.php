<?php
declare(strict_types=1);

namespace VoucherManager\Domain\Pool;

final class PoolService {

    public function create(string $name, string $description, int $threshold): Pool {
        return new Pool(
            null,
            trim($name),
            trim($description),
            max(0, $threshold),
            true
        );
    }
}
