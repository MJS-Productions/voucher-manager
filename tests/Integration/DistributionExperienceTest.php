<?php
/**
 * Framework-free distribution experience test.
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
		$file = $root . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Distribution experience assertion failed: ' . $message );
	}
};

$view = new VoucherManager\Admin\DistributionViewModel();
$pool = new VoucherManager\Domain\Pool\Pool( 7, 'Summer', 'summer', '', 10, 'active', '', '' );
$row  = array( 'pool' => $pool, 'total' => 12, 'available' => 4, 'assigned' => 8 );

$assert( 'Summer — 4 available, 12 total' === $view->pool_option_label( $row ), 'Pool options must expose inventory context.' );
$assert( $view->can_distribute( $row ), 'Active pools with available codes must be distributable.' );
$assert( ! $view->can_distribute( array_merge( $row, array( 'available' => 0 ) ) ), 'Empty pools must not be offered for manual distribution.' );
$assert( '4 codes remain available in this pool.' === $view->remaining_message( 4 ), 'Success result must explain remaining inventory.' );
$assert( 'This pool is now empty. Import more codes before the next distribution.' === $view->remaining_message( 0 ), 'Final distribution must guide the administrator to import.' );
$assert( 'warning' === $view->result_tone( 0 ) && 'success' === $view->result_tone( 1 ), 'Result tone must distinguish depleted inventory.' );

$admin    = file_get_contents( $root . '/src/Admin/DistributionAdmin.php' );
$template = file_get_contents( $root . '/templates/admin/distribution.php' );
$composer = file_get_contents( $root . '/composer.json' );

$assert( is_string( $admin ) && str_contains( $admin, 'PoolOverviewData' ) && str_contains( $admin, "'pool_id'   => \$pool_id" ), 'Distribution controller must prepare inventory rows and retain pool context in the one-time result.' );
$assert( is_string( $template ) && str_contains( $template, 'Your assigned code' ) && str_contains( $template, 'shown only in this one-time result' ), 'Success state must clearly present the one-time voucher result.' );
$assert( str_contains( $template, 'vm-copy-distributed-code' ) && str_contains( $template, 'navigator.clipboard.writeText' ), 'Success state must provide an explicit copy action.' );
$assert( str_contains( $template, 'array_filter' ) && str_contains( $template, 'can_distribute' ), 'Pool choices must be filtered through centralized distribution presentation rules.' );
$assert(
	str_contains( $template, "\$_GET['pool_id']" )
	&& str_contains( $template, 'absint( wp_unslash' )
	&& str_contains( $template, '$requested_pool_id === (int) $row[\'pool\']->id()' )
	&& str_contains( $template, 'selected( $selected_pool_id, (int) $row[\'pool\']->id() )' ),
	'Pool context from the Pools overview must preselect only a currently distributable Pool.'
);
$assert( str_contains( $template, 'No codes are ready to distribute' ) && str_contains( $template, 'voucher-manager-import' ), 'Empty inventory must show a guided import action.' );
$assert( str_contains( $template, 'Every distribution requires a new POST action' ), 'Distribution safety guidance must explain refresh behavior.' );
$assert(
	! str_contains( $template, 'get_transient' )
	&& ! str_contains( $admin, 'set_transient' )
	&& str_contains( $admin, '$this->results->consume' ),
	'One-time results must use the unique consume-once result store rather than a shared per-user transient.'
);
$assert( str_contains( $admin, 'check_admin_referer' ) && str_contains( $admin, "current_user_can( 'manage_options' )" ), 'Distribution execution must retain nonce and capability protection.' );
$assert( str_contains( $composer, '@test:distribution-experience' ) && strpos( $composer, '@test:distribution-experience' ) < strpos( $composer, '@build' ), 'Distribution Experience test must run before build.' );

echo "Distribution experience OK: inventory guidance, one-time result presentation, copy action and POST safety verified.\n";
