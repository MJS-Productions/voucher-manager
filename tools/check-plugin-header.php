<?php
declare(strict_types=1);

$plugin = file_get_contents(__DIR__ . '/../voucher-manager.php');
if ($plugin === false) {
    exit(1);
}

foreach (['Plugin Name:', 'Version:'] as $header) {
    if (strpos($plugin, $header) === false) {
        fwrite(STDERR, "Missing plugin header: {$header}\n");
        exit(1);
    }
}

echo "Plugin header OK\n";
