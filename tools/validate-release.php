<?php
declare(strict_types=1);

$root    = dirname(__DIR__);
$zipPath = $root . '/dist/mjs-productions-voucher-manager.zip';
$releaseRoot = 'mjs-productions-voucher-manager/';

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
    $releaseRoot . 'voucher-manager.php',
    $releaseRoot . 'src/Core/Plugin.php',
    $releaseRoot . 'src/Lifecycle/Activator.php',
    $releaseRoot . 'readme.txt',
    $releaseRoot . 'uninstall.php',
];

foreach ($required as $file) {
    if (!in_array($file, $names, true)) {
        fwrite(STDERR, "Release artifact is missing: {$file}" . PHP_EOL);
        exit(1);
    }
}

$allowedTopLevel = [
    'assets',
    'src',
    'templates',
    'CHANGELOG.md',
    'LICENSE',
    'README.md',
    'SECURITY.md',
    'readme.txt',
    'uninstall.php',
    'voucher-manager.php',
];

foreach ($names as $name) {
    if (!str_starts_with($name, $releaseRoot)) {
        fwrite(STDERR, "Invalid release root: {$name}" . PHP_EOL);
        exit(1);
    }

    $relative = substr($name, strlen($releaseRoot));

    if ('' === $relative) {
        continue;
    }

    $topLevel = explode('/', $relative, 2)[0];

    if ('languages' === $topLevel || in_array(strtolower(pathinfo($relative, PATHINFO_EXTENSION)), ['po', 'mo'], true)) {
        fwrite(STDERR, "Bundled translation catalog included in release: {$name}" . PHP_EOL);
        exit(1);
    }

    if (!in_array($topLevel, $allowedTopLevel, true)) {
        fwrite(STDERR, "Non-production file included in release: {$name}" . PHP_EOL);
        exit(1);
    }

    if (str_starts_with(basename($relative), '.')) {
        fwrite(STDERR, "Hidden file included in release: {$name}" . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, "Release artifact OK: " . count($names) . " entries checked." . PHP_EOL);
