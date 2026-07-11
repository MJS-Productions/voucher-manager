<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'voucher-manager.php',
    'src/Core/Plugin.php',
    'src/Lifecycle/Activator.php',
    'templates',
    'assets',
    'languages',
    'uninstall.php',
];

foreach ($required as $item) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item);
    if (!file_exists($path)) {
        fwrite(STDERR, "Missing required plugin item: {$item}" . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, "Plugin structure OK." . PHP_EOL);
