<?php
declare(strict_types=1);

$root = dirname(__DIR__);

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

$classes = [
    VoucherManager\Core\Plugin::class,
    VoucherManager\Lifecycle\Activator::class,
    VoucherManager\Admin\Admin::class,
    VoucherManager\Domain\Distribution\DistributionService::class,
];

foreach ($classes as $class) {
    if (!class_exists($class)) {
        fwrite(STDERR, "Autoload failed: {$class}" . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, "Autoload smoke test OK: " . count($classes) . " classes loaded." . PHP_EOL);
