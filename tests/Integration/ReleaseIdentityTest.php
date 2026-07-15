<?php
/**
 * Framework-free release identity test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root     = dirname( __DIR__, 2 );
$expected = '0.7.0-alpha';

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Release identity assertion failed: ' . $message );
	}
};

$plugin    = file_get_contents( $root . '/voucher-manager.php' );
$readme    = file_get_contents( $root . '/README.md' );
$changelog = file_get_contents( $root . '/CHANGELOG.md' );
$release   = file_get_contents( $root . '/RELEASE-' . $expected . '.md' );
$readiness = file_get_contents( $root . '/docs/SPRINT_7_RELEASE_READINESS.md' );

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, 'Version:           ' . $expected )
	&& str_contains( $plugin, "define( 'VOUCHER_MANAGER_VERSION', '" . $expected . "' );" ),
	'Plugin header and runtime version constant must match the reviewed release.'
);

$assert(
	is_string( $readme ) && str_contains( $readme, 'Release candidate:** `' . $expected . '` — The Visible Inventory' ),
	'README must identify the reviewed release candidate.'
);

$assert(
	is_string( $changelog ) && str_contains( $changelog, '## ' . $expected . ' - 2026-07-15 — The Visible Inventory' ),
	'Changelog must contain the reviewed release section.'
);

$assert(
	is_string( $release )
	&& str_contains( $release, '# Voucher Manager ' . $expected . ' — The Visible Inventory' )
	&& str_contains( $release, 'No database schema migration is introduced' ),
	'Current release notes must match the reviewed version and upgrade boundary.'
);

$assert(
	! file_exists( $root . '/RELEASE-0.6.0-alpha.md' ),
	'The root release note must describe the current candidate rather than the previous release.'
);

$assert(
	is_string( $readiness )
	&& str_contains( $readiness, 'selected `0.7.0-alpha`' )
	&& str_contains( $readiness, 'Confirm WordPress reports `0.7.0-alpha`' ),
	'Release-readiness documentation must reflect the selected version.'
);

echo "Release identity OK: 0.7.0-alpha is consistent across plugin, README, changelog, release notes and final gate.\n";
