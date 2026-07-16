<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$zipPath = $root . '/dist/voucher-manager.zip';

if (!is_file($zipPath)) {
    fwrite(STDERR, "Release ZIP does not exist. Run the build first." . PHP_EOL);
    exit(1);
}

if (class_exists(ZipArchive::class)) {
    $zip = new ZipArchive();

    if (true !== $zip->open($zipPath)) {
        fwrite(STDERR, "Cannot open release ZIP." . PHP_EOL);
        exit(1);
    }

    $names = [];

    for ($index = 0; $index < $zip->numFiles; ++$index) {
        $name = $zip->getNameIndex($index);
        if (false !== $name) {
            $names[] = $name;
        }
    }

    $zip->close();
} else {
    $unzipBinary = trim((string) shell_exec('command -v unzip 2>/dev/null'));

    if ('' === $unzipBinary) {
        fwrite(STDERR, "Neither ZipArchive nor the unzip command is available." . PHP_EOL);
        exit(1);
    }

    $command = sprintf(
        '%s -Z1 %s',
        escapeshellarg($unzipBinary),
        escapeshellarg($zipPath)
    );

    exec($command, $names, $exitCode);

    if (0 !== $exitCode) {
        fwrite(STDERR, "Cannot inspect release ZIP." . PHP_EOL);
        exit(1);
    }
}

$required = [
    'voucher-manager/voucher-manager.php',
    'voucher-manager/src/Core/Plugin.php',
    'voucher-manager/src/Lifecycle/Activator.php',
    'voucher-manager/languages/voucher-manager.pot',
    'voucher-manager/languages/voucher-manager-de_DE.po',
    'voucher-manager/languages/voucher-manager-de_DE.mo',
    'voucher-manager/uninstall.php',
];

foreach ($required as $file) {
    if (!in_array($file, $names, true)) {
        fwrite(STDERR, "Release artifact is missing: {$file}" . PHP_EOL);
        exit(1);
    }
}

foreach ($names as $name) {
    if (!str_starts_with($name, 'voucher-manager/')) {
        fwrite(STDERR, "Invalid release root: {$name}" . PHP_EOL);
        exit(1);
    }

    foreach (['/.git/', '/.github/', '/tools/', '/tests/', '/vendor/'] as $forbidden) {
        if (str_contains('/' . $name, $forbidden)) {
            fwrite(STDERR, "Development file included in release: {$name}" . PHP_EOL);
            exit(1);
        }
    }
}

fwrite(STDOUT, "Release artifact OK: " . count($names) . " entries checked." . PHP_EOL);
