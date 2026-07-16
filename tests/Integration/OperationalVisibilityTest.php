<?php
/**
 * Framework-free operational visibility test.
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
		$file = $root . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Operational visibility assertion failed: ' . $message );
	}
};

$view = new VoucherManager\Admin\OperationalActivityViewModel();

$assert( 'Error' === $view->severity_label( 'admin.action_failed' ), 'Admin failures must be clearly identified as errors.' );
$assert( 'Attention' === $view->severity_label( 'distribution.empty' ), 'Empty inventory must be marked as needing attention.' );
$assert(
	'Import additional One-Time Codes into this pool before the next distribution.' === $view->guidance( 'distribution.empty' ),
	'Empty distribution events must provide an actionable next step.'
);
$assert(
	str_contains( $view->guidance( 'pool.delete_failed' ), 'rolled back' ),
	'Failed pool deletion must explain integrity-preserving rollback.'
);
$assert(
	'Pool #12' === $view->detail( 'distribution.empty', array( 'pool_id' => 12, 'code' => 'MUST-NOT-APPEAR' ) ),
	'Activity detail must remain privacy-safe.'
);
$assert( 'Imports' === $view->family_label( 'import' ), 'Activity families must use readable labels.' );
$assert( ! $view->has_active_filters( 'all', 'all' ), 'Default Activity view must not show a redundant Reset action.' );
$assert( $view->has_active_filters( 'import', 'all' ), 'Area filtering must activate Activity Reset guidance.' );
$assert( $view->has_active_filters( 'all', 'error' ), 'Outcome filtering must activate Activity Reset guidance.' );


$data_source      = file_get_contents( $root . '/src/Admin/OperationalActivityData.php' );
$admin_source     = file_get_contents( $root . '/src/Admin/OperationalActivityAdmin.php' );
$root_admin       = file_get_contents( $root . '/src/Admin/Admin.php' );
$template_source  = file_get_contents( $root . '/templates/admin/activity.php' );
$dashboard_source = file_get_contents( $root . '/templates/admin/dashboard.php' );
$composer_source  = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $data_source )
	&& str_contains( $data_source, 'LIMIT %d OFFSET %d' )
	&& str_contains( $data_source, '$this->normalize_family' )
	&& str_contains( $data_source, '$this->normalize_tone' ),
	'Operational history must be paginated and filter inputs must be allowlisted.'
);
$assert(
	! str_contains( $template_source, "['message']" )
	&& ! str_contains( $template_source, 'json_encode' )
	&& str_contains( $template_source, 'One-Time Code values and personal data are never presented here' ),
	'The activity view must not expose raw messages or raw context.'
);
$assert(
	str_contains( $template_source, 'Filter activity' )
	&& str_contains( $template_source, 'has_active_filters' )
	&& str_contains( $template_source, 'Reset filters' )
	&& str_contains( $template_source, 'Activity history' )
	&& str_contains( $template_source, 'activity-guidance' ),
	'The activity page must provide filters, history and action guidance.'
);
$assert(
	is_string( $admin_source )
	&& str_contains( $admin_source, "current_user_can( 'manage_options' )" )
	&& str_contains( $admin_source, 'sanitize_key' )
	&& str_contains( $admin_source, 'wp_unslash' ),
	'The activity admin boundary must enforce capability and sanitized request handling.'
);
$assert(
	is_string( $root_admin ) && str_contains( $root_admin, 'OperationalActivityAdmin' ),
	'The Activity submenu must be registered.'
);
$assert(
	is_string( $dashboard_source ) && str_contains( $dashboard_source, 'View all activity' ),
	'The dashboard must expose a deliberate path to complete operational history.'
);
$assert(
	is_string( $composer_source )
	&& str_contains( $composer_source, '@test:operational-visibility' )
	&& strpos( $composer_source, '@test:operational-visibility' ) < strpos( $composer_source, '@build' ),
	'Operational visibility coverage must run before the release build.'
);

echo "Operational visibility OK: filtered history, actionable guidance, privacy boundary and admin access verified.\n";
