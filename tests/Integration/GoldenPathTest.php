<?php
/**
 * Framework-free golden path integration test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Domain\Code\CodeRepository;
use VoucherManager\Domain\Code\CodeStateMachine;
use VoucherManager\Domain\Code\CodeStatus;
use VoucherManager\Domain\Distribution\DistributionService;
use VoucherManager\Domain\Import\ImportRecord;
use VoucherManager\Domain\Import\ImportRepository;
use VoucherManager\Domain\Import\ImportService;
use VoucherManager\Domain\Log\LogRepository;
use VoucherManager\Domain\Pool\Pool;
use VoucherManager\Domain\Pool\PoolRepository;
use VoucherManager\Support\CodeFileParser;

$root = dirname(__DIR__, 2);

spl_autoload_register(
    static function (string $class) use ($root): void {
        $prefix = 'VoucherManager\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $file = $root . '/src/' . str_replace('\\', '/', $relative) . '.php';

        if (is_readable($file)) {
            require_once $file;
        }
    }
);

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new \RuntimeException('Golden path assertion failed: ' . $message);
    }
};

final class MemoryPoolRepository implements PoolRepository {
    /** @var array<int,Pool> */
    private array $pools = [];
    private int $nextId = 1;

    public function all(): array { return array_values($this->pools); }
    public function find(int $id): ?Pool { return $this->pools[$id] ?? null; }

    public function create(string $name, string $description, int $warning_threshold, bool $active): int {
        $id = $this->nextId++;
        $now = '2026-07-11 00:00:00';
        $this->pools[$id] = new Pool(
            $id,
            $name,
            strtolower(str_replace(' ', '-', $name)),
            $description,
            $warning_threshold,
            $active ? 'active' : 'inactive',
            $now,
            $now
        );
        return $id;
    }

    public function update(int $id, string $name, string $description, int $warning_threshold, bool $active): bool {
        if (!isset($this->pools[$id])) { return false; }
        $old = $this->pools[$id];
        $this->pools[$id] = new Pool(
            $id,
            $name,
            $old->slug(),
            $description,
            $warning_threshold,
            $active ? 'active' : 'inactive',
            $old->created_at(),
            '2026-07-11 00:00:01'
        );
        return true;
    }

    public function set_active(int $id, bool $active): bool {
        $pool = $this->find($id);
        return null !== $pool && $this->update(
            $id,
            $pool->name(),
            $pool->description(),
            $pool->warning_threshold(),
            $active
        );
    }

    public function delete(int $id): bool { unset($this->pools[$id]); return true; }
    public function code_count(int $id): int { return 0; }
}

final class MemoryImportRepository implements ImportRepository {
    /** @var array<int,array<string,mixed>> */
    public array $records = [];
    private int $nextId = 1;

    public function start(int $pool_id, string $filename, string $file_type): int {
        $id = $this->nextId++;
        $this->records[$id] = compact('pool_id', 'filename', 'file_type') + ['status' => 'processing'];
        return $id;
    }

    public function complete(int $id, int $total, int $imported, int $skipped, int $invalid): bool {
        $this->records[$id] += compact('total', 'imported', 'skipped', 'invalid');
        $this->records[$id]['status'] = 'completed';
        return true;
    }

    public function fail(int $id, int $total, int $imported, int $skipped, int $invalid): bool {
        $this->records[$id] += compact('total', 'imported', 'skipped', 'invalid');
        $this->records[$id]['status'] = 'failed';
        return true;
    }

    public function recent(int $limit = 20): array { return []; }
    public function find(int $id): ?ImportRecord { return null; }
    public function mark_rolled_back(int $id): bool { $this->records[$id]['status'] = 'rolled_back'; return true; }
}

final class MemoryCodeRepository implements CodeRepository {
    /** @var array<int,array{id:int,pool_id:int,import_id:int,code:string,status:string}> */
    private array $codes = [];
    private int $nextId = 1;

    public function insert_batch(int $pool_id, int $import_id, array $codes): int {
        $inserted = 0;
        foreach ($codes as $code) {
            foreach ($this->codes as $existing) {
                if ($existing['code'] === $code) { continue 2; }
            }
            $id = $this->nextId++;
            $this->codes[$id] = compact('id', 'pool_id', 'import_id', 'code') + ['status' => CodeStatus::AVAILABLE->value];
            ++$inserted;
        }
        return $inserted;
    }

    public function delete_available_by_import(int $import_id): int {
        $deleted = 0;
        foreach ($this->codes as $id => $row) {
            if ($row['import_id'] === $import_id && CodeStatus::AVAILABLE->value === $row['status']) {
                unset($this->codes[$id]);
                ++$deleted;
            }
        }
        return $deleted;
    }

    public function count_assigned_by_import(int $import_id): int {
        return count(array_filter(
            $this->codes,
            static fn(array $row): bool => $row['import_id'] === $import_id && CodeStatus::ASSIGNED->value === $row['status']
        ));
    }

    public function claim_next_available(int $pool_id): ?array {
        (new CodeStateMachine())->assert_transition(CodeStatus::AVAILABLE, CodeStatus::ASSIGNED);

        foreach ($this->codes as $id => $row) {
            if ($row['pool_id'] === $pool_id && CodeStatus::AVAILABLE->value === $row['status']) {
                $this->codes[$id]['status'] = CodeStatus::ASSIGNED->value;
                return ['id' => $id, 'code' => $row['code']];
            }
        }
        return null;
    }

    public function count_available(int $pool_id): int {
        return count(array_filter(
            $this->codes,
            static fn(array $row): bool => $row['pool_id'] === $pool_id && CodeStatus::AVAILABLE->value === $row['status']
        ));
    }
}

final class MemoryLogRepository implements LogRepository {
    /** @var array<int,array{event_type:string,message:string,context:array<string,mixed>}> */
    public array $entries = [];

    public function add(string $event_type, string $message, array $context = []): void {
        $this->entries[] = compact('event_type', 'message', 'context');
    }
}

$pools = new MemoryPoolRepository();
$imports = new MemoryImportRepository();
$codes = new MemoryCodeRepository();
$logs = new MemoryLogRepository();

$poolId = $pools->create('Golden Path Pool', 'Integration test pool.', 1, true);
$assert(1 === $poolId, 'Pool creation failed.');
$assert(true === $pools->find($poolId)?->is_active(), 'Created pool must be active.');

$file = tempnam(sys_get_temp_dir(), 'voucher-manager-');
if (false === $file) {
    throw new \RuntimeException('Could not create temporary import file.');
}

file_put_contents($file, "ALPHA-001\nBETA-002\nALPHA-001\n\n");

try {
    $importService = new ImportService($imports, $codes, $logs, new CodeFileParser());
    $importResult = $importService->import($poolId, $file, 'golden-path.txt', 'txt');

    $assert(5 === $importResult->total(), 'Import total should include parser output including trailing empty rows.');
    $assert(2 === $importResult->imported(), 'Two unique non-empty codes should be imported.');
    $assert(1 === $importResult->skipped(), 'Duplicate should be skipped.');
    $assert(2 === $importResult->invalid(), 'Blank and trailing empty rows should be invalid.');
    $assert(2 === $codes->count_available($poolId), 'Two codes should be available after import.');

    $distribution = new DistributionService($pools, $codes, $logs);
    $first = $distribution->distribute($poolId);
    $second = $distribution->distribute($poolId);
    $empty = $distribution->distribute($poolId);

    $assert($first->success(), 'First distribution should succeed.');
    $assert($second->success(), 'Second distribution should succeed.');
    $assert($first->code() !== $second->code(), 'Distributed codes must be unique.');
    $assert(1 === $first->remaining(), 'One code should remain after first distribution.');
    $assert(0 === $second->remaining(), 'No codes should remain after second distribution.');
    $assert(!$empty->success(), 'Distribution from an empty pool must fail cleanly.');
    $assert(null === $empty->code(), 'Empty distribution must not expose a code.');

    $eventTypes = array_column($logs->entries, 'event_type');
    $assert(in_array('import.completed', $eventTypes, true), 'Import completion must be logged.');
    $assert(2 === count(array_filter($eventTypes, static fn(string $type): bool => 'distribution.completed' === $type)), 'Each successful distribution must be logged.');
    $assert(in_array('distribution.empty', $eventTypes, true), 'Empty distribution must be logged.');

    $rollbackBlocked = false;
    try {
        $importService->rollback($importResult->import_id());
    } catch (\RuntimeException) {
        $rollbackBlocked = true;
    }
    $assert($rollbackBlocked, 'Rollback must be blocked after codes have been assigned.');
} finally {
    unlink($file);
}

fwrite(STDOUT, "Golden path OK: pool -> import -> distribution -> log -> protected rollback." . PHP_EOL);
