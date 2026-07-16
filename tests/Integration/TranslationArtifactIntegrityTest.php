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
$compilerPath = $root . '/tools/compile-translations.php';

$assert( is_readable( $potPath ), 'POT catalog must exist.' );
$assert( is_readable( $poPath ), 'PO catalog must exist.' );
$assert( is_readable( $moPath ), 'MO catalog must exist.' );
$assert( is_readable( $compilerPath ), 'Deterministic translation compiler must exist.' );

$command = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $compilerPath ) . ' --check';
exec( $command . ' 2>&1', $output, $exitCode );

$assert(
	0 === $exitCode,
	'Committed MO must exactly match deterministic PO compilation: ' . implode( ' | ', $output )
);

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

$header = unpack( 'Vmagic/Vrevision/Vcount/Voriginal/Vtranslation/VhashSize/VhashOffset', substr( $mo, 0, 28 ) );

$assert(
	is_array( $header )
	&& 0x950412de === $header['magic']
	&& 0 === $header['revision'],
	'MO header must be readable and use revision zero.'
);

$poLines      = preg_split( '/\R/', $po ) ?: array();
$catalogKeys  = array();
$currentId    = null;
$currentPlural = null;
$currentContext = null;

$flushKey = static function () use ( &$catalogKeys, &$currentId, &$currentPlural, &$currentContext ): void {
	if ( null !== $currentId ) {
		$catalogKeys[ (string) $currentContext . "\x04" . $currentId . "\x00" . (string) $currentPlural ] = true;
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
$build    = file_get_contents( $root . '/tools/build-release.php' );
$workflow = file_get_contents( $root . '/.github/workflows/quality.yml' );

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"translations": "php tools/compile-translations.php"' )
	&& str_contains( $composer, '@test:translation-artifact-integrity' )
	&& strpos( $composer, '@translations' ) < strpos( $composer, '@build' )
	&& strpos( $composer, '@test:translation-artifact-integrity' ) < strpos( $composer, '@build' ),
	'Composer Quality Gate must compile and validate translations before build.'
);

$assert(
	is_string( $build )
	&& str_contains( $build, "compile-translations.php" )
	&& str_contains( $build, 'translation compilation failed' ),
	'Direct release builds must compile translations before copying files.'
);

$assert(
	is_string( $workflow )
	&& str_contains( $workflow, 'Run translation build' )
	&& str_contains( $workflow, 'composer translations' )
	&& strpos( $workflow, 'composer translations' ) < strpos( $workflow, 'composer quality' ),
	'GitHub Actions must compile translations before the Quality Gate.'
);

echo "Translation artifact integrity OK: deterministic PO-to-MO build, freshness and release inclusion verified.\n";
