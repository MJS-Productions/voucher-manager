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
$localization = $root . '/tools/localization.php';

$assert( is_readable( $potPath ), 'POT catalog must exist.' );
$assert( is_readable( $poPath ), 'PO catalog must exist.' );
$assert( is_readable( $localization ), 'Shared-localization consumer adapter must exist.' );

$po = file_get_contents( $poPath );

$assert(
	is_string( $po )
	&& str_contains( $po, '"Language: de_DE\\n"' )
	&& str_contains( $po, '"Plural-Forms: nplurals=2; plural=(n != 1);\\n"' ),
	'German PO metadata must remain valid.'
);

$localizationSource = file_get_contents( $localization );

$assert(
	is_string( $localizationSource )
	&& str_contains( $localizationSource, 'generateMoFiles: false' ),
	'Voucher Manager localization must explicitly disable MO generation.'
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

echo "Translation artifact integrity OK: POT/PO catalogs, PO-only shared Localization integration and CI gates verified.\n";
