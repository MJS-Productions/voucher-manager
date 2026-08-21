<?php
/**
 * Framework-free release identity test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root     = dirname( __DIR__, 2 );
$expected = '1.0.8';

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Release identity assertion failed: ' . $message );
	}
};

$plugin    = file_get_contents( $root . '/voucher-manager.php' );
$readme    = file_get_contents( $root . '/README.md' );
$wp_readme = file_get_contents( $root . '/readme.txt' );
$changelog = file_get_contents( $root . '/CHANGELOG.md' );
$release   = file_get_contents( $root . '/RELEASE-' . $expected . '.md' );
$readiness = file_get_contents( $root . '/docs/SPRINT_8_PART_5_4_FINAL_DISTRIBUTION_SAFETY_REVIEW.md' );

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, 'Version:           ' . $expected )
	&& str_contains( $plugin, "define( 'VOUCHER_MANAGER_VERSION', '" . $expected . "' );" ),
	'Plugin header and runtime version constant must match the reviewed release.'
);

$assert(
	is_string( $readme ) && str_contains( $readme, 'Current release:** `' . $expected . '` — WordPress 7.1 and Release Hardening' ),
	'README must identify the patch release.'
);

$assert(
	is_string( $changelog ) && str_contains( $changelog, '## ' . $expected . ' - 2026-08-21 — WordPress 7.1 and Release Hardening' ),
	'Changelog must contain the reviewed release section.'
);

$assert(
	is_string( $wp_readme ) && str_contains( $wp_readme, 'Stable tag: ' . $expected ),
	'WordPress readme Stable tag must match the reviewed release.'
);

$assert(
	is_string( $release )
	&& str_contains( $release, '# Voucher Manager ' . $expected . ' — WordPress 7.1 and Release Hardening' )
	&& str_contains( $release, 'No database schema migration is introduced' ),
	'Patch release notes must match the reviewed version and upgrade boundary.'
);

$assert(
	! file_exists( $root . '/RELEASE-0.9.3-alpha.md' ),
	'The root release note must describe the current patch release rather than an Alpha release.'
);

$assert(
	is_string( $readiness )
	&& str_contains( $readiness, 'PASS — ready for Keeper concurrency and lifecycle smoke testing.' )
	&& str_contains( $readiness, 'Keeper smoke-test gate' ),
	'Final Sprint 8 review must document the approved release gate.'
);

echo "Release identity OK: 1.0.8 is consistent across plugin, README, WordPress readme, changelog, release notes and final gate.\n";
