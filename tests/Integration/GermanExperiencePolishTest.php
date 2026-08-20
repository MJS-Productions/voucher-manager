<?php
/**
 * Framework-free German experience polish test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'German experience polish assertion failed: ' . $message );
	}
};

$po          = file_get_contents( $root . '/languages/voucher-manager-de_DE.po' );
$dashboard   = file_get_contents( $root . '/templates/admin/dashboard.php' );
$activity    = file_get_contents( $root . '/templates/admin/activity.php' );
$distribution = file_get_contents( $root . '/templates/admin/distribution.php' );
$direct      = file_get_contents( $root . '/templates/admin/distribution-direct-result.php' );
$rollback    = file_get_contents( $root . '/templates/admin/import-rollback-confirmation.php' );
$settings    = file_get_contents( $root . '/templates/admin/settings.php' );
$view        = file_get_contents( $root . '/src/Admin/SettingsViewModel.php' );
$composer    = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $po )
	&& str_contains( $po, 'msgstr "Bestand verwalten"' )
	&& str_contains( $po, 'msgstr "Die letzten Aktivitäten."' )
	&& str_contains( $po, 'msgstr "Aktivitäten prüfen und wichtige Vorgänge schnell erkennen"' ),
	'Dashboard and Activity descriptions must use the approved concise German copy.'
);

$assert(
	is_string( $distribution )
	&& is_string( $direct )
	&& ! str_contains( $distribution, 'Distribution complete' )
	&& ! str_contains( $direct, 'Distribution complete' )
	&& str_contains( $distribution, 'Assigned One-Time Code' ),
	'Successful Distribution must show only the useful result heading.'
);

$assert(
	is_string( $rollback )
	&& str_contains( $rollback, 'Undo this import?' )
	&& str_contains( $rollback, 'All One-Time Codes added by this import will be permanently removed.' )
	&& str_contains( $rollback, 'permanently removes all %d One-Time Code added by this import.' )
	&& ! str_contains( $rollback, 'still-available One-Time Codes' )
	&& ! str_contains( $rollback, 'may permanently remove up to' ),
	'Rollback confirmation must describe the all-or-nothing import boundary.'
);

$assert(
	is_string( $po )
	&& str_contains( $po, 'Diesen Import rückgängig machen?' )
	&& str_contains( $po, 'Alle durch diesen Import hinzugefügten Einmalcodes werden dauerhaft entfernt.' )
	&& str_contains( $po, 'alle %d durch diesen Import hinzugefügten Einmalcodes dauerhaft entfernt werden' ),
	'German rollback confirmation must match the all-or-nothing behavior.'
);

$assert(
	is_string( $settings )
	&& str_contains( $settings, 'if ( 0 < $settings->activity_retention_days() )' )
	&& str_contains( $settings, 'Cleanup runs automatically through WordPress Cron.' )
	&& is_string( $view )
	&& str_contains( $view, 'Activity history entries will be kept indefinitely.' ),
	'Cron guidance must be hidden for indefinite retention while the saved state remains explicit.'
);

$assert(
	is_string( $po )
	&& str_contains( $po, 'Einträge der Aktivitätshistorie werden dauerhaft aufbewahrt.' )
	&& str_contains( $po, 'Die Bereinigung erfolgt automatisch über WordPress-Cron.' ),
	'German retention copy must support both indefinite and scheduled cleanup states.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:german-experience-polish' )
	&& strpos( $composer, '@test:german-experience-polish' ) < strpos( $composer, '@build' ),
	'German experience polish coverage must run before build.'
);

echo "German experience polish OK: Dashboard, rollback, Distribution and retention context verified.\n";
