<?php
declare(strict_types=1);

$root = dirname(__DIR__);

$translationCompiler = $root . '/tools/compile-translations.php';
$command             = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($translationCompiler);
passthru($command, $translationExitCode);

if (0 !== $translationExitCode) {
    fwrite(STDERR, "Release build stopped because translation compilation failed." . PHP_EOL);
    exit(1);
}

$dist    = $root . '/dist';
$build   = $dist . '/voucher-manager';
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

/*
 * Keep the release artifact intentionally small and production-only.
 * Repository, CI, test, tooling, release-note and local-development files
 * remain in GitHub but are not shipped to WordPress installations.
 */
$includedTopLevelDirectories = [
    'assets',
    'languages',
    'src',
    'templates',
];

$includedTopLevelFiles = [
    'CHANGELOG.md',
    'LICENSE',
    'README.md',
    'SECURITY.md',
    'readme.txt',
    'uninstall.php',
    'voucher-manager.php',
];

foreach ($includedTopLevelDirectories as $directory) {
    $sourceDirectory = $root . DIRECTORY_SEPARATOR . $directory;

    if (!is_dir($sourceDirectory)) {
        fwrite(STDERR, "Required release directory is missing: {$directory}" . PHP_EOL);
        exit(1);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDirectory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relative = substr($item->getPathname(), strlen($root) + 1);
        $target   = $build . DIRECTORY_SEPARATOR . $relative;

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
}

foreach ($includedTopLevelFiles as $file) {
    $source = $root . DIRECTORY_SEPARATOR . $file;

    if (!is_file($source)) {
        fwrite(STDERR, "Required release file is missing: {$file}" . PHP_EOL);
        exit(1);
    }

    copy($source, $build . DIRECTORY_SEPARATOR . $file);
}

if (class_exists(ZipArchive::class)) {
    $zip = new ZipArchive();

    if (true !== $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        fwrite(STDERR, "Cannot create release ZIP." . PHP_EOL);
        exit(1);
    }

    $zipFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($build, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($zipFiles as $file) {
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
