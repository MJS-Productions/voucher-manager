<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$dist = $root . '/dist';
$build = $dist . '/voucher-manager';
$zipPath = $dist . '/voucher-manager.zip';

$remove = static function (string $path) use (&$remove): void {
    if (!file_exists($path)) {
        return;
    }

    if (is_dir($path)) {
        foreach (scandir($path) ?: [] as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }
            $remove($path . DIRECTORY_SEPARATOR . $item);
        }
        rmdir($path);
        return;
    }

    unlink($path);
};

$remove($build);
$remove($zipPath);
mkdir($build, 0777, true);

$excludedTopLevel = [
    '.git',
    '.github',
    'dist',
    'docs',
    'tests',
    'tools',
    'vendor',
];

$excludedFiles = [
    '.gitignore',
    'composer.json',
    'composer.lock',
    'CONTRIBUTING.md',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $relative = substr($item->getPathname(), strlen($root) + 1);
    $segments = explode(DIRECTORY_SEPARATOR, $relative);

    if (in_array($segments[0], $excludedTopLevel, true)
        || in_array($relative, $excludedFiles, true)) {
        continue;
    }

    $target = $build . DIRECTORY_SEPARATOR . $relative;

    if ($item->isDir()) {
        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }
        continue;
    }

    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0777, true);
    }

    copy($item->getPathname(), $target);
}

if (class_exists(ZipArchive::class)) {
    $zip = new ZipArchive();

    if (true !== $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        fwrite(STDERR, "Cannot create release ZIP." . PHP_EOL);
        exit(1);
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($build, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $relative = substr($file->getPathname(), strlen($build) + 1);
        $zip->addFile(
            $file->getPathname(),
            'voucher-manager/' . str_replace(DIRECTORY_SEPARATOR, '/', $relative)
        );
    }

    $zip->close();
} else {
    $zipBinary = trim((string) shell_exec('command -v zip 2>/dev/null'));

    if ('' === $zipBinary) {
        fwrite(STDERR, "Neither ZipArchive nor the zip command is available." . PHP_EOL);
        exit(1);
    }

    $command = sprintf(
        'cd %s && %s -qr %s voucher-manager',
        escapeshellarg($dist),
        escapeshellarg($zipBinary),
        escapeshellarg($zipPath)
    );

    exec($command, $output, $exitCode);

    if (0 !== $exitCode) {
        fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, "Release artifact created: dist/voucher-manager.zip" . PHP_EOL);
