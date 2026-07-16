<?php
/**
 * Framework-free release identity test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root     = dirname( __DIR__, 2 );
$expected = '0.8.1-alpha';

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Release identity assertion failed: ' . $message );
	}
};

$plugin    = file_get_contents( $root . '/voucher-manager.php' );
$readme    = file_get_contents( $root . '/README.md' );
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
	is_string( $readme ) && str_contains( $readme, 'Release candidate:** `' . $expected . '` — The Activity Clarity Patch' ),
	'README must identify the reviewed release candidate.'
);

$assert(
	is_string( $changelog ) && str_contains( $changelog, '## ' . $expected . ' - 2026-07-16 — The Activity Clarity Patch' ),
	'Changelog must contain the reviewed release section.'
);

$assert(
	is_string( $release )
	&& str_contains( $release, '# Voucher Manager ' . $expected . ' — The Activity Clarity Patch' )
	&& str_contains( $release, 'No database schema migration is introduced' ),
	'Current release notes must match the reviewed version and upgrade boundary.'
);

$assert(
	! file_exists( $root . '/RELEASE-0.8.0-alpha.md' ),
	'The root release note must describe the current candidate rather than the previous release.'
);

$assert(
	is_string( $readiness )
	&& str_contains( $readiness, 'PASS — ready for Keeper concurrency and lifecycle smoke testing.' )
	&& str_contains( $readiness, 'Keeper smoke-test gate' ),
	'Final Sprint 8 review must document the approved release gate.'
);

echo "Release identity OK: 0.8.1-alpha is consistent across plugin, README, changelog, release notes and final gate.\n";
