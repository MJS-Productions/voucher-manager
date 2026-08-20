<?php
/**
 * Framework-free dashboard presentation test.
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

if ( ! function_exists( '_x' ) ) {
	function _x( string $text, string $context, string $domain = 'default' ): string {
		unset( $context, $domain );
		return $text;
	}
}

if ( ! function_exists( 'esc_html_x' ) ) {
	function esc_html_x( string $text, string $context, string $domain = 'default' ): string {
		unset( $context, $domain );
		return $text;
	}
}


if ( ! function_exists( '_n' ) ) {
	function _n(
		string $single,
		string $plural,
		int $number,
		string $domain = 'default'
	): string {
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

		$relative = substr( $class, strlen( $prefix ) );
		$file     = $root . '/src/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Dashboard assertion failed: ' . $message );
	}
};

$view = new VoucherManager\Admin\DashboardViewModel();

$assert(
	'One-Time Code distributed' === $view->activity_label( 'distribution.completed' ),
	'Completed distributions should have a readable label.'
);
$assert(
	'success' === $view->activity_tone( 'distribution.completed' ),
	'Completed distributions should have a success tone.'
);
$assert(
	'error' === $view->activity_tone( 'admin.action_failed' ),
	'Unexpected admin failures should have an error tone.'
);
$assert( 'Settings updated' === $view->activity_label( 'settings.updated' ), 'Settings updates should have a readable label.' );
$assert( 'success' === $view->activity_tone( 'settings.updated' ), 'Settings updates should have a success tone.' );
$assert( 'Activity cleanup completed' === $view->activity_label( 'activity.cleanup_completed' ), 'Successful cleanup should have a readable label.' );
$assert( 'Activity cleanup failed' === $view->activity_label( 'activity.cleanup_failed' ), 'Failed cleanup should have a readable label.' );
$assert( 'success' === $view->activity_tone( 'activity.cleanup_completed' ), 'Successful cleanup should have a success tone.' );
$assert( 'error' === $view->activity_tone( 'activity.cleanup_failed' ), 'Failed cleanup should have an error tone.' );
$assert( 'Voucher Manager installed' === $view->activity_label( 'plugin.installed' ), 'Plugin installation should have a readable label.' );
$assert( 'Voucher Manager activated' === $view->activity_label( 'plugin.activated' ), 'Plugin activation should have a readable label.' );
$assert( 'Voucher Manager deactivated' === $view->activity_label( 'plugin.deactivated' ), 'Plugin deactivation should have a readable label.' );
$assert( 'Voucher Manager uninstalled' === $view->activity_label( 'plugin.uninstalled' ), 'Plugin uninstall should have a readable label.' );
$assert( 'neutral' === $view->activity_tone( 'plugin.installed' ), 'Plugin lifecycle events should remain informational.' );
$assert( 'neutral' === $view->activity_tone( 'plugin.uninstalled' ), 'Plugin uninstall with retained data should remain informational.' );
$assert(
	"Pool: Amazon Vouchers\n\nRemaining inventory: 2 One-Time Codes" === $view->activity_detail(
		'distribution.completed',
		array( 'remaining' => 2, 'pool_name' => 'Amazon Vouchers', 'code' => 'MUST-NOT-APPEAR' )
	),
	'Distribution detail should show Pool context and inventory, not the One-Time Code value.'
);
$assert(
	"Pool: Amazon Vouchers\n\nRemaining inventory: 1 One-Time Code" === $view->activity_detail(
		'distribution.completed',
		array( 'remaining' => 1, 'pool_name' => 'Amazon Vouchers' )
	),
	'Distribution detail should use singular inventory grammar.'
);
$assert(
	'Pool #12' === $view->activity_detail(
		'distribution.empty',
		array( 'pool_id' => 12 )
	),
	'Pool-related events should identify the internal pool.'
);

$assert(
	'Deleted 4 available One-Time Codes' === $view->activity_label(
		'pool.available_codes_deleted',
		array( 'deleted_available_count' => 4, 'code' => 'MUST-NOT-APPEAR' )
	),
	'Available-code deletion should show the affected count without exposing One-Time Code values.'
);
$assert( 'Pool created' === $view->activity_label( 'pool.created' ), 'Pool creation should have a readable label.' );
$assert( 'Pool updated' === $view->activity_label( 'pool.updated' ), 'Pool updates should have a readable label.' );
$assert( 'Pool activated' === $view->activity_label( 'pool.activated' ), 'Pool activation should have a readable label.' );
$assert( 'Pool deactivated' === $view->activity_label( 'pool.deactivated' ), 'Pool deactivation should have a readable label.' );
$assert( 'success' === $view->activity_tone( 'pool.created' ), 'Pool creation should have a success tone.' );
$assert( 'success' === $view->activity_tone( 'pool.updated' ), 'Pool updates should have a success tone.' );
$assert( 'success' === $view->activity_tone( 'pool.activated' ), 'Pool activation should have a success tone.' );
$assert( 'success' === $view->activity_tone( 'pool.deactivated' ), 'Pool deactivation should have a success tone.' );
$assert(
	'Pool: Campaign Pool' === $view->activity_detail( 'pool.created', array( 'pool_id' => 21, 'pool_name' => 'Campaign Pool' ) ),
	'Pool lifecycle details should use the Pool name.'
);
$assert(
	'Pool deleted' === $view->activity_label( 'pool.deleted' ),
	'Pool deletion should have a readable label.'
);
$assert(
	'Pool: Retired Campaign' === $view->activity_detail(
		'pool.deleted',
		array( 'pool_id' => 12, 'pool_name' => 'Retired Campaign' )
	),
	'Pool deletion detail should preserve the Pool name instead of exposing only the internal ID.'
);
$assert(
	'Pool deletion failed' === $view->activity_label( 'pool.delete_failed' ),
	'Failed pool deletion should have a readable label.'
);
$assert(
	'warning' === $view->activity_tone( 'pool.deleted' ),
	'Successful destructive lifecycle events should have a warning tone.'
);
$assert(
	'error' === $view->activity_tone( 'pool.delete_failed' ),
	'Failed destructive lifecycle events should have an error tone.'
);
$assert(
	'Pool: Campaign Codes · 5 One-Time Codes added · 3 skipped · 2 invalid' === $view->activity_detail(
		'import.completed',
		array(
			'pool_id'   => 6,
			'pool_name' => 'Campaign Codes',
			'imported'  => 5,
			'skipped'   => 3,
			'invalid'   => 2,
			'code'      => 'MUST-NOT-APPEAR',
		)
	),
	'Completed imports should show the Pool name and a concise privacy-safe result summary.'
);
$assert(
	'Pool #6 · 5 One-Time Codes added · 3 skipped · 2 invalid' === $view->activity_detail(
		'import.completed',
		array(
			'pool_id'  => 6,
			'imported' => 5,
			'skipped'  => 3,
			'invalid'  => 2,
		)
	),
	'Legacy completed imports without stored Pool names should retain the Pool ID fallback.'
);

$assert(
	'Voucher Manager activity' === $view->activity_label( 'future.event' ),
	'Unknown events should degrade gracefully.'
);


$data_source = file_get_contents( $root . '/src/Admin/DashboardData.php' );
$assert(
	is_string( $data_source )
	&& str_contains( $data_source, "'event_type' => sanitize_text_field" )
	&& ! str_contains( $data_source, "'event_type' => sanitize_key" ),
	'Dashboard data loading must preserve dots in stable event names.'
);

$admin_source = file_get_contents( $root . '/src/Admin/Admin.php' );
$pool_admin_source = file_get_contents( $root . '/src/Admin/PoolAdmin.php' );
$activity_data_source = file_get_contents( $root . '/src/Admin/OperationalActivityData.php' );
$template_source = file_get_contents( $root . '/templates/admin/dashboard.php' );

$assert(
	is_string( $pool_admin_source )
	&& str_contains( $pool_admin_source, 'OperationalEvent::POOL_CREATED' )
	&& str_contains( $pool_admin_source, 'OperationalEvent::POOL_UPDATED' )
	&& str_contains( $pool_admin_source, 'OperationalEvent::POOL_ACTIVATED' )
	&& str_contains( $pool_admin_source, 'OperationalEvent::POOL_DEACTIVATED' )
	&& str_contains( $pool_admin_source, 'is_active() !== $active' ),
	'Pool creation, editing and both status-change paths must record Activity.'
);
$assert(
	is_string( $activity_data_source )
	&& str_contains( $activity_data_source, "'settings.updated'" )
	&& str_contains( $activity_data_source, "'activity.cleanup_completed'" )
	&& str_contains( $activity_data_source, "'activity.cleanup_failed'" )
	&& str_contains( $activity_data_source, "'settings'" )
	&& str_contains( $activity_data_source, "'pool.created'" )
	&& str_contains( $activity_data_source, "'pool.updated'" )
	&& str_contains( $activity_data_source, "'pool.activated'" )
	&& str_contains( $activity_data_source, "'pool.deactivated'" ),
	'Settings, maintenance and Pool lifecycle events must participate in Activity outcome filtering.'
);
$assert(
	is_string( $admin_source ) && str_contains( $admin_source, "__( 'Dashboard', 'voucher-manager' )" ),
	'The duplicated submenu label should be renamed to Dashboard.'
);
$assert(
	is_string( $template_source ) && str_contains( $template_source, 'voucher-manager__quick-actions' ),
	'The dashboard should expose quick actions.'
);
$assert(
	is_string( $template_source ) && str_contains( $template_source, 'Recent activity' ),
	'The dashboard should include recent operational activity.'
);
$assert(
	is_string( $template_source ) && str_contains( $template_source, 'activity_label( $event_type, $context )' ),
	'The dashboard should pass sanitized event context into context-aware activity labels.'
);

fwrite(
	STDOUT,
	"Dashboard experience OK: navigation, activity presentation and privacy-safe details verified." . PHP_EOL
);
