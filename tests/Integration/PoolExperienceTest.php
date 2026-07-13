<?php
/**
 * Framework-free pool experience test.
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
		throw new RuntimeException( 'Pool experience assertion failed: ' . $message );
	}
};

$pool = static function ( bool $active, int $threshold ): VoucherManager\Domain\Pool\Pool {
	return new VoucherManager\Domain\Pool\Pool(
		1,
		'Campaign',
		'campaign',
		'Campaign codes',
		$threshold,
		$active ? 'active' : 'inactive',
		'2026-07-13 00:00:00',
		'2026-07-13 00:00:00'
	);
};

$view = new VoucherManager\Admin\PoolViewModel();

$assert( 'inactive' === $view->inventory_state( $pool( false, 10 ), 100 ), 'Inactive pools must remain visually inactive.' );
$assert( 'empty' === $view->inventory_state( $pool( true, 10 ), 0 ), 'An active pool with no codes must be empty.' );
$assert( 'low' === $view->inventory_state( $pool( true, 10 ), 10 ), 'The warning threshold must include the threshold value.' );
$assert( 'low' === $view->inventory_state( $pool( true, 10 ), 4 ), 'Inventory below the warning threshold must be low.' );
$assert( 'ready' === $view->inventory_state( $pool( true, 10 ), 11 ), 'Inventory above the threshold must be ready.' );
$assert( 'ready' === $view->inventory_state( $pool( true, 0 ), 1 ), 'A disabled threshold must not produce low stock.' );
$assert( 'Low stock' === $view->inventory_label( $pool( true, 10 ), 5 ), 'Low stock should have a readable label.' );
$assert( 'Distribution is paused.' === $view->inventory_hint( $pool( false, 10 ), 5 ), 'Inactive pools need a clear explanation.' );

$template = file_get_contents( $root . '/templates/admin/pools.php' );
$admin    = file_get_contents( $root . '/src/Admin/PoolAdmin.php' );

$assert( is_string( $template ) && str_contains( $template, 'voucher-manager__pool-grid' ), 'Pools should use the overview card layout.' );
$assert( is_string( $template ) && str_contains( $template, 'Available' ), 'Available inventory must be visible.' );
$assert( is_string( $template ) && str_contains( $template, 'Distributed' ), 'Distributed inventory must be visible.' );
$assert( is_string( $template ) && str_contains( $template, 'Create your first pool' ), 'The empty state should guide first-time users.' );
$assert( is_string( $admin ) && str_contains( $admin, 'PoolOverviewData' ), 'Pool inventory should be prepared outside the template.' );

fwrite(
	STDOUT,
	"Pool experience OK: inventory states, guidance and pool overview presentation verified." . PHP_EOL
);
