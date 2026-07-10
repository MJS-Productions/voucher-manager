<?php
/**
 * Recursively checks the syntax of project PHP files.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$project_root = dirname(__DIR__);
$excluded     = array(
	$project_root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR,
);

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator(
		$project_root,
		FilesystemIterator::SKIP_DOTS
	)
);

$failed  = false;
$checked = 0;

foreach ($iterator as $file) {
	if (!$file instanceof SplFileInfo || 'php' !== strtolower($file->getExtension())) {
		continue;
	}

	$path = $file->getPathname();

	foreach ($excluded as $excluded_path) {
		if (str_starts_with($path, $excluded_path)) {
			continue 2;
		}
	}

	++$checked;

	$command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path);
	exec($command, $output, $exit_code);

	if (0 !== $exit_code) {
		$failed = true;
		fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
	}

	$output = array();
}

if ($failed) {
	exit(1);
}

fwrite(STDOUT, sprintf("Syntax OK: %d PHP files checked.%s", $checked, PHP_EOL));
