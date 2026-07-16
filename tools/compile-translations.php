<?php
/**
 * Deterministically compiles bundled gettext PO catalogs to MO artifacts.
 *
 * Usage:
 *   php tools/compile-translations.php
 *   php tools/compile-translations.php --check
 */

declare(strict_types=1);

$root      = dirname( __DIR__ );
$languages = $root . '/languages';
$checkOnly = in_array( '--check', $argv, true );

$catalogs = array(
	'voucher-manager-de_DE.po' => 'voucher-manager-de_DE.mo',
);

$decodeQuoted = static function ( string $value ): string {
	$decoded = json_decode( $value, true, 512, JSON_THROW_ON_ERROR );

	if ( ! is_string( $decoded ) ) {
		throw new RuntimeException( 'Invalid PO quoted string.' );
	}

	return $decoded;
};

/**
 * @return array<int,array<string,mixed>>
 */
$parsePo = static function ( string $source ) use ( $decodeQuoted ): array {
	$entries = array();
	$current = array();
	$active  = null;

	$flush = static function () use ( &$entries, &$current, &$active ): void {
		if ( array_key_exists( 'msgid', $current ) ) {
			$entries[] = $current;
		}

		$current = array();
		$active  = null;
	};

	foreach ( preg_split( '/\R/', $source ) ?: array() as $rawLine ) {
		$line = trim( $rawLine );

		if ( '' === $line ) {
			$flush();
			continue;
		}

		if ( str_starts_with( $line, '#' ) ) {
			continue;
		}

		if ( str_starts_with( $line, 'msgctxt ' ) ) {
			$current['msgctxt'] = $decodeQuoted( substr( $line, 8 ) );
			$active             = 'msgctxt';
			continue;
		}

		if ( str_starts_with( $line, 'msgid_plural ' ) ) {
			$current['msgid_plural'] = $decodeQuoted( substr( $line, 13 ) );
			$active                  = 'msgid_plural';
			continue;
		}

		if ( str_starts_with( $line, 'msgid ' ) ) {
			$current['msgid'] = $decodeQuoted( substr( $line, 6 ) );
			$active           = 'msgid';
			continue;
		}

		if ( preg_match( '/^msgstr\[(\d+)\]\s+(.+)$/', $line, $matches ) ) {
			$index = (int) $matches[1];
			$current['msgstr_plural'][ $index ] = $decodeQuoted( $matches[2] );
			$active = array( 'msgstr_plural', $index );
			continue;
		}

		if ( str_starts_with( $line, 'msgstr ' ) ) {
			$current['msgstr'] = $decodeQuoted( substr( $line, 7 ) );
			$active            = 'msgstr';
			continue;
		}

		if ( str_starts_with( $line, '"' ) ) {
			$value = $decodeQuoted( $line );

			if ( is_array( $active ) ) {
				$current[ $active[0] ][ $active[1] ] .= $value;
			} elseif ( is_string( $active ) ) {
				$current[ $active ] = ( $current[ $active ] ?? '' ) . $value;
			}
		}
	}

	$flush();

	return $entries;
};

$compileMo = static function ( array $entries ): string {
	$catalog = array();

	foreach ( $entries as $entry ) {
		$msgid   = (string) ( $entry['msgid'] ?? '' );
		$context = (string) ( $entry['msgctxt'] ?? '' );
		$key     = ( '' !== $context ? $context . "\x04" : '' ) . $msgid;

		if ( array_key_exists( 'msgid_plural', $entry ) ) {
			$key .= "\x00" . (string) $entry['msgid_plural'];
			$plural = $entry['msgstr_plural'] ?? array();

			if ( ! is_array( $plural ) || array() === $plural ) {
				throw new RuntimeException( 'Plural entry has no translations: ' . $msgid );
			}

			ksort( $plural );
			$value = implode( "\x00", array_map( 'strval', $plural ) );
		} else {
			$value = (string) ( $entry['msgstr'] ?? '' );
		}

		if ( '' !== $msgid && '' === $value ) {
			throw new RuntimeException( 'Untranslated PO entry: ' . $msgid );
		}

		$catalog[ $key ] = $value;
	}

	ksort( $catalog, SORT_STRING );

	$ids          = array_keys( $catalog );
	$translations = array_values( $catalog );
	$count        = count( $ids );
	$headerSize   = 7 * 4;
	$originalTableOffset    = $headerSize;
	$translationTableOffset = $originalTableOffset + ( $count * 8 );
	$originalStringsOffset   = $translationTableOffset + ( $count * 8 );

	$originalBlob = '';
	$originalMeta = array();
	$offset       = $originalStringsOffset;

	foreach ( $ids as $id ) {
		$bytes          = (string) $id;
		$originalMeta[] = array( strlen( $bytes ), $offset );
		$originalBlob  .= $bytes . "\x00";
		$offset        += strlen( $bytes ) + 1;
	}

	$translationStringsOffset = $originalStringsOffset + strlen( $originalBlob );
	$translationBlob          = '';
	$translationMeta          = array();
	$offset                   = $translationStringsOffset;

	foreach ( $translations as $translation ) {
		$bytes             = (string) $translation;
		$translationMeta[] = array( strlen( $bytes ), $offset );
		$translationBlob  .= $bytes . "\x00";
		$offset           += strlen( $bytes ) + 1;
	}

	$mo = pack(
		'V7',
		0x950412de,
		0,
		$count,
		$originalTableOffset,
		$translationTableOffset,
		0,
		0
	);

	foreach ( $originalMeta as [ $length, $entryOffset ] ) {
		$mo .= pack( 'V2', $length, $entryOffset );
	}

	foreach ( $translationMeta as [ $length, $entryOffset ] ) {
		$mo .= pack( 'V2', $length, $entryOffset );
	}

	return $mo . $originalBlob . $translationBlob;
};

foreach ( $catalogs as $poName => $moName ) {
	$poPath = $languages . '/' . $poName;
	$moPath = $languages . '/' . $moName;

	if ( ! is_readable( $poPath ) ) {
		fwrite( STDERR, "Translation source missing: languages/{$poName}" . PHP_EOL );
		exit( 1 );
	}

	try {
		$source   = file_get_contents( $poPath );
		$entries  = $parsePo( is_string( $source ) ? $source : '' );
		$compiled = $compileMo( $entries );
	} catch ( Throwable $exception ) {
		fwrite( STDERR, 'Translation compilation failed: ' . $exception->getMessage() . PHP_EOL );
		exit( 1 );
	}

	if ( $checkOnly ) {
		$current = is_file( $moPath ) ? file_get_contents( $moPath ) : false;

		if ( ! is_string( $current ) || ! hash_equals( hash( 'sha256', $compiled ), hash( 'sha256', $current ) ) ) {
			fwrite( STDERR, "Translation artifact is stale: languages/{$moName}" . PHP_EOL );
			exit( 1 );
		}

		fwrite( STDOUT, "Translation artifact current: languages/{$moName}" . PHP_EOL );
		continue;
	}

	if ( false === file_put_contents( $moPath, $compiled ) ) {
		fwrite( STDERR, "Cannot write translation artifact: languages/{$moName}" . PHP_EOL );
		exit( 1 );
	}

	fwrite(
		STDOUT,
		sprintf(
			"Translation artifact compiled: languages/%s (%d entries, sha256 %s)%s",
			$moName,
			count( $entries ),
			hash( 'sha256', $compiled ),
			PHP_EOL
		)
	);
}
