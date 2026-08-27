<?php
/**
 * Framework-free product identity test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Product identity assertion failed: ' . $message );
	}
};

$plugin   = file_get_contents( $root . '/voucher-manager.php' );
$readme   = file_get_contents( $root . '/README.md' );
$manifest = file_get_contents( $root . '/MANIFEST.md' );
$adr      = file_get_contents( $root . '/docs/adr/0033-product-identity.md' );
$release  = file_get_contents( $root . '/RELEASE-1.0.7.md' );
$composer = file_get_contents( $root . '/composer.json' );

$tagline = 'Professional One-Time Code Management for WordPress';

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, 'Plugin Name:       MJS-Productions Voucher Manager' )
	&& str_contains( $plugin, 'Description:       ' . $tagline . '.' )
	&& str_contains( $plugin, 'Text Domain:       mjs-productions-voucher-manager' ),
	'Plugin header must use the approved public and technical identity.'
);

foreach ( array( $readme, $manifest, $adr, $release ) as $document ) {
	$assert(
		is_string( $document )
		&& str_contains( $document, 'Voucher Manager' )
		&& str_contains( $document, $tagline ),
		'Public identity documents must use the approved product name and tagline.'
	);
}

$assert(
	is_string( $adr )
	&& str_contains( $adr, '**MJS-Productions Voucher Manager**' )
	&& str_contains( $adr, '**Voucher Manager PRO**' )
	&& str_contains( $adr, 'plugin slug: `mjs-productions-voucher-manager`' )
	&& str_contains( $adr, 'text domain: `mjs-productions-voucher-manager`' ),
	'ADR 0033 must preserve the approved base-plugin, PRO-extension and technical identity.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"description": "' . $tagline . '."' )
	&& str_contains( $composer, '@test:product-identity' ),
	'Composer metadata and Quality Gate must use and protect the approved identity.'
);

echo "Product identity OK: base-plugin name, tagline and PRO-extension identity verified.\n";
