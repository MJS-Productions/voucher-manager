<?php
/**
 * Framework-free Sprint 6 cross-layer release-readiness test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n( string $single, string $plural, int $number, string $domain = 'default' ): string {
		unset( $domain );
		return 1 === $number ? $single : $plural;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( mixed $value ): int {
		return abs( (int) $value );
	}
}

$root = dirname( __DIR__, 2 );

spl_autoload_register(
	static function ( string $class ) use ( $root ): void {
		$prefix = 'VoucherManager\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}

		$file = $root . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) . '.php' );
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Sprint 6 release-readiness assertion failed: ' . $message );
	}
};

$dashboard = new VoucherManager\Admin\DashboardViewModel();
$fallback  = $dashboard->activity_label( 'unknown.event' );

foreach ( VoucherManager\Domain\Log\OperationalEvent::cases() as $event ) {
	$label = $dashboard->activity_label(
		$event->value,
		array(
			'pool_id'                 => 7,
			'import_id'               => 8,
			'remaining'               => 2,
			'deleted_available_count' => 3,
			'imported'                => 4,
			'skipped'                 => 1,
			'invalid'                 => 0,
		)
	);

	$assert(
		$fallback !== $label,
		sprintf( 'Stable event %s must not use the generic Dashboard fallback.', $event->value )
	);
}

$dashboard_data = file_get_contents( $root . '/src/Admin/DashboardData.php' );
$activity_view  = file_get_contents( $root . '/templates/admin/activity.php' );
$pool_admin     = file_get_contents( $root . '/src/Admin/PoolAdmin.php' );
$pool_danger    = file_get_contents( $root . '/templates/admin/pool-danger-zone.php' );
$pool_confirm   = file_get_contents( $root . '/templates/admin/pool-delete-available-confirmation.php' );
$import_admin   = file_get_contents( $root . '/src/Admin/ImportAdmin.php' );
$import_view    = file_get_contents( $root . '/templates/admin/import.php' );
$rollback_view  = file_get_contents( $root . '/templates/admin/import-rollback-confirmation.php' );
$distribution  = file_get_contents( $root . '/templates/admin/distribution.php' );
$readme         = file_get_contents( $root . '/README.md' );
$changelog      = file_get_contents( $root . '/CHANGELOG.md' );
$release_doc    = file_get_contents( $root . '/docs/SPRINT_6_RELEASE_READINESS.md' );
$composer       = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $dashboard_data )
	&& str_contains( $dashboard_data, "'event_type' => sanitize_text_field" )
	&& ! str_contains( $dashboard_data, "'event_type' => sanitize_key" ),
	'Dotted stable event names must survive the Dashboard data boundary.'
);

$assert(
	is_string( $activity_view )
	&& ! str_contains( $activity_view, "['message']" )
	&& ! str_contains( $activity_view, 'json_encode' ),
	'Operational Activity must not render raw log messages or raw context.'
);

$assert(
	is_string( $pool_danger )
	&& is_string( $pool_confirm )
	&& str_contains( $pool_danger, "'action' => 'confirm-delete-available'" )
	&& str_contains( $pool_confirm, 'confirm_delete_available' )
	&& is_string( $pool_admin )
	&& str_contains( $pool_admin, 'confirm_delete_available' ),
	'Available-code deletion must retain review and server-side acknowledgement.'
);

$assert(
	is_string( $import_view )
	&& is_string( $rollback_view )
	&& is_string( $import_admin )
	&& ! str_contains( $import_view, 'confirm(' )
	&& str_contains( $rollback_view, 'confirm_rollback' )
	&& str_contains( $import_admin, 'confirm_rollback' ),
	'Import rollback must retain its dedicated review and confirmed POST boundary.'
);

$assert(
	is_string( $distribution )
	&& str_contains( $distribution, 'get_transient( $key )' )
	&& str_contains( $distribution, 'delete_transient( $key )' ),
	'Distribution result must remain one-time presentation data.'
);

$assert(
	is_string( $readme )
	&& str_contains( $readme, 'Release candidate:** `0.6.0-alpha` — The First Experience' )
	&& str_contains( $readme, 'Previous published release:** `0.5.0-alpha` — The Stable Foundation' )
	&& str_contains( $readme, 'version identity was selected during the Sprint 6 release review' ),
	'README must distinguish the reviewed 0.6.0-alpha candidate from the previous published release.'
);

$assert(
	is_string( $changelog )
	&& 1 === substr_count( $changelog, '# Changelog' )
	&& 1 === substr_count( $changelog, '## Unreleased' ),
	'Changelog must remain one chronological document with one Unreleased section.'
);

$assert(
	! file_exists( $root . '/HOTFIX-0.4.1-alpha.1.md' )
	&& ! file_exists( $root . '/SPRINT-2.md' ),
	'Obsolete root documentation must not return.'
);

$assert(
	is_string( $release_doc )
	&& str_contains( $release_doc, 'Manual WordPress smoke-test matrix' )
	&& str_contains( $release_doc, 'Final release gate' ),
	'Sprint 6 release-readiness documentation must preserve smoke-test and final release gates.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:sprint6-release-readiness' )
	&& strpos( $composer, '@test:sprint6-release-readiness' ) < strpos( $composer, '@build' ),
	'Sprint 6 release-readiness coverage must run before the release build.'
);

echo "Sprint 6 release readiness OK: event vocabulary, destructive boundaries, one-time results and documentation consistency verified.\n";
