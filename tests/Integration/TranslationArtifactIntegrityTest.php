<?php
/**
 * Framework-free translation artifact integrity test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Translation artifact integrity assertion failed: ' . $message );
	}
};

$potPath      = $root . '/languages/voucher-manager.pot';
$poPath       = $root . '/languages/voucher-manager-de_DE.po';
$moPath       = $root . '/languages/voucher-manager-de_DE.mo';
$localization = $root . '/tools/localization.php';

$assert( is_readable( $potPath ), 'POT catalog must exist.' );
$assert( is_readable( $poPath ), 'PO catalog must exist.' );
$assert( is_readable( $moPath ), 'MO catalog must exist.' );
$assert( is_readable( $localization ), 'Shared-localization consumer adapter must exist.' );

$po = file_get_contents( $poPath );
$mo = file_get_contents( $moPath );

$assert(
	is_string( $po )
	&& str_contains( $po, '"Language: de_DE\\n"' )
	&& str_contains( $po, '"Plural-Forms: nplurals=2; plural=(n != 1);\\n"' ),
	'German PO metadata must remain valid.'
);

$assert(
	is_string( $mo )
	&& 28 <= strlen( $mo )
	&& "\xDE\x12\x04\x95" === substr( $mo, 0, 4 ),
	'MO must use the little-endian GNU gettext format.'
);

$header = unpack(
	'Vmagic/Vrevision/Vcount/Voriginal/Vtranslation/VhashSize/VhashOffset',
	substr( $mo, 0, 28 )
);

$assert(
	is_array( $header )
	&& 0x950412de === $header['magic']
	&& 0 === $header['revision'],
	'MO header must be readable and use revision zero.'
);

$poLines        = preg_split( '/\R/', $po ) ?: array();
$catalogKeys    = array();
$currentId      = null;
$currentPlural  = null;
$currentContext = null;

$flushKey = static function () use (
	&$catalogKeys,
	&$currentId,
	&$currentPlural,
	&$currentContext
): void {
	if ( null !== $currentId ) {
		$catalogKeys[
			(string) $currentContext
			. "\x04"
			. $currentId
			. "\x00"
			. (string) $currentPlural
		] = true;
	}

	$currentId      = null;
	$currentPlural  = null;
	$currentContext = null;
};

foreach ( $poLines as $line ) {
	$line = trim( $line );

	if ( '' === $line ) {
		$flushKey();
		continue;
	}

	if ( str_starts_with( $line, 'msgctxt "' ) ) {
		$currentContext = stripcslashes( substr( $line, 9, -1 ) );
	} elseif ( str_starts_with( $line, 'msgid_plural "' ) ) {
		$currentPlural = stripcslashes( substr( $line, 14, -1 ) );
	} elseif ( str_starts_with( $line, 'msgid "' ) ) {
		$currentId = stripcslashes( substr( $line, 7, -1 ) );
	}
}

$flushKey();

$assert(
	count( $catalogKeys ) === $header['count'],
	'MO entry count must equal the unique PO catalog keys, including metadata.'
);

$composer = file_get_contents( $root . '/composer.json' );
$workflow = file_get_contents( $root . '/.github/workflows/quality.yml' );

$assert(
	is_string( $composer )
	&& str_contains(
		$composer,
		'"translations": "php tools/localization.php update"'
	)
	&& str_contains(
		$composer,
		'"translations:check": "php tools/localization.php check"'
	)
	&& str_contains(
		$composer,
		'"translations:validate": "php tools/localization.php validate"'
	),
	'Composer must expose the shared Localization update, check and validation commands.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:translation-artifact-integrity' )
	&& ! str_contains( $composer, '"@translations"' )
	&& strpos( $composer, '@test:translation-artifact-integrity' )
		< strpos( $composer, '@build' ),
	'The Quality Gate must validate translation integration before build without regenerating localization artifacts.'
);

$assert(
	is_string( $workflow )
	&& str_contains( $workflow, 'localization:' )
	&& str_contains( $workflow, 'Check localization artifacts' )
	&& str_contains( $workflow, 'composer translations:check' )
	&& str_contains( $workflow, 'Validate translations' )
	&& str_contains( $workflow, 'composer translations:validate' ),
	'GitHub Actions must provide a dedicated Localization quality job.'
);

echo "Translation artifact integrity OK: catalogs, shared Localization integration and CI gates verified.\n";
