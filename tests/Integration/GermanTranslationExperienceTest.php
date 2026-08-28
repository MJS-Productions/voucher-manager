<?php
/**
 * Framework-free German translation experience test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'German translation assertion failed: ' . $message );
	}
};

$pot_path = $root . '/languages/voucher-manager.pot';
$po_path  = $root . '/languages/voucher-manager-de_DE.po';
$mo_path  = $root . '/languages/voucher-manager-de_DE.mo';

$assert( is_readable( $pot_path ), 'POT source catalog must exist.' );
$assert( is_readable( $po_path ), 'German PO catalog must exist.' );
$assert( is_readable( $mo_path ) && 0 < filesize( $mo_path ), 'Compiled German MO catalog must exist.' );

$pot = file_get_contents( $pot_path );
$po  = file_get_contents( $po_path );
$mo  = file_get_contents( $mo_path );

$assert(
	is_string( $pot )
	&& str_contains( $pot, 'X-Domain: mjs-productions-voucher-manager' )
	&& str_contains( $pot, 'msgid "One-Time Code inventory"' )
	&& str_contains( $pot, 'msgid_plural "%d One-Time Codes"' ),
	'POT catalog must contain Product Language and plural source strings.'
);

$assert(
	is_string( $po )
	&& str_contains( $po, '"Language: de_DE\\n"' )
	&& str_contains( $po, '"Plural-Forms: nplurals=2; plural=(n != 1);\\n"' ),
	'German catalog metadata and plural rule must be correct.'
);

$required_pairs = array(
	'msgid "Distribution"' => 'msgstr "Ausgabe"',
	'msgid "One-Time Code inventory"' => 'msgstr "Einmalcode-Bestand"',
	'msgid "Activity history"' => 'msgstr "Aktivitätshistorie"',
	'msgid "Danger Zone"' => 'msgstr "Gefahrenbereich"',
	'msgid "Permanent deletion cannot be undone."' => 'msgstr "Die dauerhafte Löschung kann nicht rückgängig gemacht werden."',
	'msgid "Pool"' => 'msgstr "Pool"',
	'msgid "Pool created"' => 'msgstr "Pool erstellt"',
	'msgid "Pool updated"' => 'msgstr "Pool aktualisiert"',
	'msgid "Pool activated"' => 'msgstr "Pool aktiviert"',
	'msgid "Pool deactivated"' => 'msgstr "Pool deaktiviert"',
	'msgid "Settings updated"' => 'msgstr "Einstellungen aktualisiert"',
	'msgid "Activity cleanup completed"' => 'msgstr "Activity-Bereinigung abgeschlossen"',
	'msgid "Activity cleanup failed"' => 'msgstr "Activity-Bereinigung fehlgeschlagen"',
	'msgid "Voucher Manager installed"' => 'msgstr "Voucher Manager installiert"',
	'msgid "Voucher Manager activated"' => 'msgstr "Voucher Manager aktiviert"',
	'msgid "Voucher Manager deactivated"' => 'msgstr "Voucher Manager deaktiviert"',
	'msgid "Voucher Manager uninstalled"' => 'msgstr "Voucher Manager deinstalliert"',
	'msgid "Remaining inventory: %d One-Time Code"' => 'msgstr[0] "Verbleibender Bestand: %d Einmalcode"',
	'msgid "One-Time Code values are not stored in Activity history."' => 'msgstr "Einmalcode-Werte werden nicht in der Aktivitätshistorie gespeichert."',
);

foreach ( $required_pairs as $source => $translation ) {
	$assert(
		str_contains( $po, $source ) && str_contains( $po, $translation ),
		'Approved German glossary translation is missing for ' . $source . '.'
	);
}

$assert(
	str_contains( $po, 'msgctxt "One-Time Code status"' )
	&& str_contains( $po, 'msgid "Available"' )
	&& str_contains( $po, 'msgstr "Verfügbar"' )
	&& str_contains( $po, 'msgid "Assigned"' )
	&& str_contains( $po, 'msgstr "Ausgegeben"' ),
	'Contextual One-Time Code statuses must use the approved German terms.'
);

$assert(
	str_contains( $po, 'msgstr[0] "%d Einmalcode"' )
	&& str_contains( $po, 'msgstr[1] "%d Einmalcodes"' )
	&& str_contains( $po, 'msgstr[0] "%d Importeintrag"' )
	&& str_contains( $po, 'msgstr[1] "%d Importeinträge"' ),
	'German singular and plural Product Language must be complete.'
);

$catalog_body = preg_replace( '/\Amsgid ""\nmsgstr ""\n(?:".*"\n)+\n/sU', '', $po, 1 );
$assert(
	is_string( $catalog_body )
	&& ! preg_match( '/^msgstr(?:\[\d+\])? ""$/m', $catalog_body ),
	'German PO must not contain untranslated non-header entries.'
);

$assert(
	is_string( $mo )
	&& 4 <= strlen( $mo )
	&& "\xDE\x12\x04\x95" === substr( $mo, 0, 4 ),
	'German MO must use the valid little-endian GNU gettext magic header.'
);

$plugin = file_get_contents( $root . '/src/Core/Plugin.php' );
$assert(
	is_string( $plugin )
	&& ! str_contains( $plugin, 'load_plugin_textdomain(' ),
	'Plugin must rely on WordPress language packs instead of manually loading a bundled text domain.'
);

$danger = file_get_contents( $root . '/templates/admin/pool-danger-zone.php' );
$assert(
	is_string( $danger )
	&& str_contains( $danger, 'This permanently deletes the pool, %1$s and %2$s.' )
	&& ! str_contains( $danger, '%1$s, and %2$s' ),
	'English source language must follow the approved no-serial-comma style.'
);

$composer = file_get_contents( $root . '/composer.json' );
$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:german-translation' )
	&& strpos( $composer, '@test:german-translation' ) < strpos( $composer, '@build' ),
	'German Translation Experience coverage must run before build.'
);

echo "German translation experience OK: complete de_DE PO/MO, glossary, plurals and WordPress language-pack delivery boundary verified.\n";
