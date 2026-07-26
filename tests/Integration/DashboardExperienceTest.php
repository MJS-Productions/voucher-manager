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
$assert(
	'Pool deleted' === $view->activity_label( 'pool.deleted' ),
	'Pool deletion should have a readable label.'
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
	'Pool #6 · 5 One-Time Codes added · 3 skipped · 2 invalid' === $view->activity_detail(
		'import.completed',
		array(
			'pool_id'  => 6,
			'imported' => 5,
			'skipped'  => 3,
			'invalid'  => 2,
			'code'     => 'MUST-NOT-APPEAR',
		)
	),
	'Completed imports should show a concise privacy-safe result summary.'
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
$template_source = file_get_contents( $root . '/templates/admin/dashboard.php' );

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
