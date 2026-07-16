<?php
/**
 * Framework-free internationalization readiness test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Internationalization readiness assertion failed: ' . $message );
	}
};

$distribution_service  = file_get_contents( $root . '/src/Domain/Distribution/DistributionService.php' );
$distribution_template = file_get_contents( $root . '/templates/admin/distribution.php' );
$audit                 = file_get_contents( $root . '/docs/INTERNATIONALIZATION_AUDIT.md' );
$composer              = file_get_contents( $root . '/composer.json' );
$plugin                = file_get_contents( $root . '/voucher-manager.php' );

$assert(
	is_string( $distribution_service )
	&& str_contains( $distribution_service, "__( 'Pool is unavailable.', 'voucher-manager' )" )
	&& str_contains( $distribution_service, "__( 'No available One-Time Codes remain in this pool.', 'voucher-manager' )" )
	&& str_contains( $distribution_service, "__( 'One-Time Code distributed.', 'voucher-manager' )" ),
	'User-visible Distribution result messages must use the plugin text domain.'
);

$assert(
	is_string( $distribution_template )
	&& str_contains( $distribution_template, 'data-copy-label' )
	&& str_contains( $distribution_template, 'data-copied-label' )
	&& str_contains( $distribution_template, 'const copyLabel = button.dataset.copyLabel || button.textContent' )
	&& ! str_contains( $distribution_template, "|| 'Copied'" )
	&& ! str_contains( $distribution_template, "|| 'Copy code'" ),
	'JavaScript must reuse translated HTML labels rather than hard-coded English fallbacks.'
);

$runtime_files = array_merge(
	glob( $root . '/src/**/*.php', GLOB_BRACE ) ?: array(),
	glob( $root . '/templates/admin/*.php' ) ?: array(),
	array( $root . '/voucher-manager.php' )
);

foreach ( $runtime_files as $file ) {
	$source = file_get_contents( $file );
	if ( ! is_string( $source ) ) {
		continue;
	}

	preg_match_all(
		'/\b(?:__|_e|esc_html__|esc_attr__)\s*\(\s*([\'"])(.*?)\1\s*,\s*([\'"])(.*?)\3\s*\)/s',
		$source,
		$calls,
		PREG_SET_ORDER
	);

	foreach ( $calls as $call ) {
		$assert(
			'voucher-manager' === $call[4],
			'Runtime translation call uses an unexpected text domain in ' . basename( $file ) . '.'
		);
	}
}

$assert(
	is_string( $audit )
	&& str_contains( $audit, 'PASS after cleanup.' )
	&& str_contains( $audit, 'Technical exception messages remain untranslated' )
	&& str_contains( $audit, 'Part 3.2 completed:' )
	&& str_contains( $audit, 'Still pending for Part 3.3:' ),
	'The i18n audit must document the resolved gaps and remaining staged work.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:i18n-readiness' )
	&& strpos( $composer, '@test:i18n-readiness' ) < strpos( $composer, '@build' ),
	'Internationalization readiness coverage must run before build.'
);

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, "Text Domain:       voucher-manager" )
	&& str_contains( $plugin, "Domain Path:       /languages" )
	&& str_contains( $plugin, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Plugin localization metadata and database boundary must remain stable.'
);

echo "Internationalization readiness OK: runtime UI strings, text domain and translated JavaScript labels verified.\n";
