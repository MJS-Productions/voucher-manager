<?php
/**
 * Framework-free Product Language test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Product Language assertion failed: ' . $message );
	}
};

$user_facing_files = array_merge(
	glob( $root . '/templates/admin/*.php' ) ?: array(),
	glob( $root . '/src/Admin/*.php' ) ?: array(),
	array(
		$root . '/src/Domain/Distribution/DistributionService.php',
		$root . '/voucher-manager.php',
	)
);

$combined = '';
foreach ( $user_facing_files as $file ) {
	$combined .= "\n" . file_get_contents( $file );
}

$forbidden = array(
	'Voucher Code',
	'Voucher Codes',
	'voucher code',
	'voucher codes',
	'Your assigned code',
	'Create your first pool',
	'Organize voucher codes',
	'Distributed voucher code',
);

foreach ( $forbidden as $phrase ) {
	$assert(
		! str_contains( $combined, $phrase ),
		'Legacy or non-neutral user-facing phrase remains: ' . $phrase
	);
}

$required = array(
	'One-Time Code',
	'One-Time Codes',
	'Assigned One-Time Code',
	'Available One-Time Codes',
	'One-Time Code inventory',
);

foreach ( $required as $phrase ) {
	$assert( str_contains( $combined, $phrase ), 'Approved Product Language phrase is missing: ' . $phrase );
}

$distribution_view = file_get_contents( $root . '/src/Admin/DistributionViewModel.php' );
$assert(
	is_string( $distribution_view )
	&& str_contains( $distribution_view, '_n(' )
	&& str_contains( $distribution_view, '%d One-Time Code remains available in this pool.' )
	&& str_contains( $distribution_view, '%d One-Time Codes remain available in this pool.' ),
	'Remaining inventory must use translation-ready singular and plural forms.'
);

$guide = file_get_contents( $root . '/docs/PRODUCT_LANGUAGE.md' );
$assert(
	is_string( $guide )
	&& str_contains( $guide, 'Neutral-professional German' )
	&& str_contains( $guide, 'Einmalcode' )
	&& str_contains( $guide, 'Do not rename stable PHP classes' ),
	'The approved Product Language contract must be documented.'
);

$composer = file_get_contents( $root . '/composer.json' );
$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:product-language' )
	&& strpos( $composer, '@test:product-language' ) < strpos( $composer, '@build' ),
	'Product Language coverage must run before build.'
);

echo "Product Language OK: One-Time Code terminology, neutral tone and plural readiness verified.\n";
