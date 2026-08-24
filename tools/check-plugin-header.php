<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$path = $root . '/voucher-manager.php';
$plugin = file_get_contents($path);

if (false === $plugin) {
    fwrite(STDERR, "Cannot read voucher-manager.php." . PHP_EOL);
    exit(1);
}

$required = [
    'Plugin Name' => 'MJS-Productions Voucher Manager',
    'Text Domain' => 'mjs-productions-voucher-manager',
    'Requires PHP' => '8.1',
];

foreach ($required as $header => $expected) {
    if (!preg_match('/^\s*\*\s*' . preg_quote($header, '/') . ':\s*(.+)$/mi', $plugin, $match)) {
        fwrite(STDERR, "Missing plugin header: {$header}" . PHP_EOL);
        exit(1);
    }

    if (trim($match[1]) !== $expected) {
        fwrite(STDERR, "Unexpected {$header}: " . trim($match[1]) . PHP_EOL);
        exit(1);
    }
}

if (!preg_match('/^\s*\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?)$/mi', $plugin, $version)) {
    fwrite(STDERR, "Missing or invalid Version header." . PHP_EOL);
    exit(1);
}

if (!preg_match("/define\(\s*'VOUCHER_MANAGER_VERSION',\s*'([^']+)'\s*\)/", $plugin, $constant)) {
    fwrite(STDERR, "Missing VOUCHER_MANAGER_VERSION constant." . PHP_EOL);
    exit(1);
}

if ($version[1] !== $constant[1]) {
    fwrite(STDERR, "Version header and constant do not match." . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Plugin header OK: {$version[1]}." . PHP_EOL);
