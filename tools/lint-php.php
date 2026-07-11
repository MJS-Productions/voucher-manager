<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$failed = false;
$count = 0;

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || 'php' !== strtolower($file->getExtension())) {
        continue;
    }

    $path = $file->getPathname();

    if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)
        || str_contains($path, DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    ++$count;
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path);
    exec($command, $output, $exitCode);

    if (0 !== $exitCode) {
        $failed = true;
        fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
    }

    $output = [];
}

if ($failed) {
    exit(1);
}

fwrite(STDOUT, sprintf("PHP syntax OK: %d files checked.%s", $count, PHP_EOL));
