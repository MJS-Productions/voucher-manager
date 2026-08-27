<?php

declare(strict_types=1);

use MJSProductions\Quality\Localization\LocalizationConfig;
use MJSProductions\Quality\Localization\LocalizationValidator;
use MJSProductions\Quality\Localization\LocalizationWorkflow;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);

$config = new LocalizationConfig(
    projectRoot: $root,
    textDomain: 'voucher-manager',
    potFile: 'languages/voucher-manager.pot',
    poFiles: [
        'languages/voucher-manager-de_DE.po',
    ],
);

$command = $argv[1] ?? 'update';

try {
    if ('update' === $command) {
        (new LocalizationWorkflow())->update($config);
        fwrite(STDOUT, "Localization artifacts updated." . PHP_EOL);
        exit(0);
    }

    if ('check' === $command) {
        $result = (new LocalizationWorkflow())->check($config);

        if (!$result->isCurrent()) {
            foreach ($result->staleFiles as $file) {
                fwrite(STDERR, "Localization artifact is stale: {$file}" . PHP_EOL);
            }

            exit(1);
        }

        fwrite(STDOUT, "Localization artifacts current." . PHP_EOL);
        exit(0);
    }

    if ('validate' === $command) {
        $result = (new LocalizationValidator())->validate($config);

        if (!$result->isComplete()) {
            foreach ($result->untranslatedByPoFile as $poFile => $messageIds) {
                foreach ($messageIds as $messageId) {
                    fwrite(STDERR, "Untranslated localization entry in {$poFile}: {$messageId}" . PHP_EOL);
                }
            }

            exit(1);
        }

        fwrite(STDOUT, "Localization translations complete." . PHP_EOL);
        exit(0);
    }

    fwrite(STDERR, "Unknown localization command: {$command}" . PHP_EOL);
    exit(1);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Localization failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
