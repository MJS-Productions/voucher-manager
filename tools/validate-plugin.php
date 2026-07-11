<?php
declare(strict_types=1);

$required = [
    'voucher-manager.php',
    'src',
    'templates',
    'assets',
];

foreach ($required as $item) {
    if (!file_exists(__DIR__ . '/../' . $item)) {
        fwrite(STDERR, "Missing required plugin item: {$item}\n");
        exit(1);
    }
}

echo "Plugin structure OK\n";
