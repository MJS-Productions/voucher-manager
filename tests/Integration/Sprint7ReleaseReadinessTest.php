<?php
/**
 * Framework-free Sprint 7 release-readiness test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Sprint 7 release readiness assertion failed: ' . $message );
	}
};

$repository = file_get_contents( $root . '/src/Infrastructure/WordPress/WpdbCodeInventoryRepository.php' );
$record     = file_get_contents( $root . '/src/Domain/Code/CodeInventoryRecord.php' );
$view       = file_get_contents( $root . '/src/Admin/InventoryViewModel.php' );
$template   = file_get_contents( $root . '/templates/admin/inventory.php' );
$admin      = file_get_contents( $root . '/src/Admin/InventoryAdmin.php' );
$activity   = file_get_contents( $root . '/templates/admin/activity.php' );
$readiness  = file_get_contents( $root . '/docs/SPRINT_7_RELEASE_READINESS.md' );
$plugin     = file_get_contents( $root . '/voucher-manager.php' );

$assert(
	is_string( $repository )
	&& str_contains( $repository, 'LEFT JOIN %i i ON i.id = c.import_id AND i.pool_id = c.pool_id' )
	&& str_contains( $repository, '$query_args = array_merge( array( $table, $imports ), $args );' )
	&& str_contains( $repository, "RIGHT(c.code, 4)" )
	&& ! str_contains( $repository, 'SELECT c.code' ),
	'Inventory repository must preserve privacy-safe suffix selection and missing-provenance visibility.'
);

$assert(
	is_string( $record )
	&& str_contains( $record, 'import_filename' )
	&& ! str_contains( $record, 'private readonly string $code,' ),
	'Inventory read model must retain sanitized provenance without hydrating complete One-Time Code values.'
);

$assert(
	is_string( $view )
	&& str_contains( $view, 'lifecycle_integrity' )
	&& str_contains( $view, 'No automatic change was made' )
	&& str_contains( $view, 'Not assigned' ),
	'Lifecycle visibility must remain explicit and read-only.'
);

$assert(
	is_string( $template )
	&& str_contains( $template, 'Reset filters' )
	&& str_contains( $template, 'voucher-manager__table-scroll' )
	&& ! str_contains( $template, 'Copy code' )
	&& ! str_contains( $template, 'Reveal' )
	&& ! str_contains( $template, 'Export' ),
	'Inventory presentation must retain filter guidance, responsive containment and the no-disclosure action boundary.'
);

$assert(
	is_string( $admin )
	&& str_contains( $admin, "current_user_can( 'manage_options' )" )
	&& str_contains( $admin, "add_filter( 'parent_file'" )
	&& str_contains( $admin, "add_filter( 'submenu_file'" )
	&& ! str_contains( $admin, 'remove_submenu_page' ),
	'Inventory navigation must preserve capability checks and real WordPress parent registration.'
);

$assert(
	is_string( $activity )
	&& str_contains( $activity, 'has_active_filters' )
	&& str_contains( $activity, 'Reset filters' ),
	'Activity must retain the conditional filter-reset interaction rule discovered during Sprint 7.'
);

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Sprint 7 must not introduce a database migration.'
);

$assert(
	is_string( $readiness )
	&& str_contains( $readiness, 'selected `0.7.0-alpha`' )
	&& str_contains( $readiness, 'Confirm WordPress reports `0.7.0-alpha`' )
	&& str_contains( $readiness, 'Publish only after Keeper approval' ),
	'Sprint 7 readiness documentation must define candidate identity and the final WordPress gate.'
);

echo "Sprint 7 release readiness OK: Inventory privacy, navigation, lifecycle and candidate boundaries verified.\n";
