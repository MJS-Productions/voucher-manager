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
	'Code distributed' === $view->activity_label( 'distribution.completed' ),
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
	'2 codes remain available.' === $view->activity_detail(
		'distribution.completed',
		array( 'remaining' => 2, 'code' => 'MUST-NOT-APPEAR' )
	),
	'Distribution detail should show inventory, not the voucher value.'
);
$assert(
	'Pool #12' === $view->activity_detail(
		'distribution.empty',
		array( 'pool_id' => 12 )
	),
	'Pool-related events should identify the internal pool.'
);
$assert(
	'Voucher Manager activity' === $view->activity_label( 'future.event' ),
	'Unknown events should degrade gracefully.'
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

fwrite(
	STDOUT,
	"Dashboard experience OK: navigation, activity presentation and privacy-safe details verified." . PHP_EOL
);
