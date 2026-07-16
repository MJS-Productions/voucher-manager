<?php
/**
 * Framework-free inventory experience test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Admin\InventoryData;
use VoucherManager\Admin\InventoryViewModel;
use VoucherManager\Domain\Code\CodeInventoryRecord;
use VoucherManager\Domain\Code\CodeInventoryRepository;
use VoucherManager\Domain\Code\CodeStatus;

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string {
		unset( $domain );
		return $text;
	}
}
if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $name ): string {
		return 'date_format' === $name ? 'Y-m-d' : 'H:i';
	}
}
if ( ! function_exists( 'wp_date' ) ) {
	function wp_date( string $format, int $timestamp ): string {
		return gmdate( $format, $timestamp );
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
		throw new RuntimeException( 'Inventory experience assertion failed: ' . $message );
	}
};

$view = new InventoryViewModel();
$record = new CodeInventoryRecord(
	101,
	7,
	12,
	'codes.csv',
	'7X4P',
	CodeStatus::AVAILABLE,
	'2026-07-13 10:00:00',
	null
);

$assert( '••••••••7X4P' === $view->reference( $record ), 'Inventory must expose only a masked administrative reference.' );

$short = new CodeInventoryRecord(
	102,
	7,
	12,
	null,
	'',
	CodeStatus::AVAILABLE,
	'2026-07-13 10:00:00',
	null
);
$assert( 'Code #102' === $view->reference( $short ), 'Short or unavailable suffixes must fall back to an internal reference.' );
$assert( 'Import #12 — codes.csv' === $view->import_reference( $record ), 'Healthy import provenance must include ID and sanitized filename.' );
$assert( 'Import #12 unavailable' === $view->import_reference( $short ), 'Missing import rows must remain visible with a defensive fallback.' );

$assigned = new CodeInventoryRecord( 103, 7, 12, 'codes.csv', 'A91K', CodeStatus::ASSIGNED, '2026-07-13 10:00:00', '2026-07-13 11:00:00' );
$assigned_missing_time = new CodeInventoryRecord( 104, 7, 12, 'codes.csv', 'B91K', CodeStatus::ASSIGNED, '2026-07-13 10:00:00', null );
$available_with_time = new CodeInventoryRecord( 105, 7, null, null, 'C91K', CodeStatus::AVAILABLE, '2026-07-13 10:00:00', '2026-07-13 11:00:00' );
$invalid_import_time = new CodeInventoryRecord( 106, 7, null, null, 'D91K', CodeStatus::AVAILABLE, 'not-a-time', null );

$assert( 'healthy' === $view->lifecycle_integrity( $record ), 'Available records without assignment timestamps must be healthy.' );
$assert( 'healthy' === $view->lifecycle_integrity( $assigned ), 'Assigned records with assignment timestamps must be healthy.' );
$assert( 'attention' === $view->lifecycle_integrity( $assigned_missing_time ), 'Assigned records without assignment timestamps need attention.' );
$assert( 'attention' === $view->lifecycle_integrity( $available_with_time ), 'Available records with assignment timestamps need attention.' );
$assert( 'Not assigned' === $view->formatted_assigned_at( $record ), 'Healthy available records need explicit Not assigned language.' );
$assert( 'Assignment time unavailable' === $view->formatted_assigned_at( $assigned_missing_time ), 'Missing assignment timestamps need a defensive fallback.' );
$assert( 'Unexpected assignment timestamp' === $view->formatted_assigned_at( $available_with_time ), 'Contradictory available records need an explicit warning.' );
$assert( 'Import time unavailable' === $view->formatted_imported_at( $invalid_import_time ), 'Invalid import timestamps need a defensive fallback.' );
$assert( str_contains( $view->lifecycle_note( $assigned_missing_time ), 'No automatic change was made' ), 'Integrity warnings must remain read-only.' );

$assert( 'all' === $view->normalized_state( 'reserved' ), 'Prepared states must not become public filters.' );
$assert( CodeStatus::AVAILABLE === $view->state_from_request( 'available' ), 'Available filter must map to the active workflow state.' );
$assert( CodeStatus::ASSIGNED === $view->state_from_request( 'assigned' ), 'Assigned filter must map to the active workflow state.' );
$assert( null === $view->state_from_request( 'all' ), 'All-state filtering must use the repository public-state scope.' );
$assert( ! $view->has_active_filters( 'all', null ), 'Default inventory view must not show a redundant Reset action.' );
$assert( $view->has_active_filters( 'available', null ), 'State filtering must activate Reset guidance.' );
$assert( $view->has_active_filters( 'all', 12 ), 'Import filtering must activate Reset guidance.' );
$assert( 'Available · Import #12 — codes.csv' === $view->active_filter_summary( 'available', 12, array( array( 'id' => 12, 'filename' => 'codes.csv' ) ) ), 'Combined filters need a readable summary.' );
$assert( 'Showing 51–100 of 120 matching records' === $view->result_range( 2, 50, 120 ), 'Pagination must explain the visible result range.' );
$assert( '0 matching records' === $view->result_range( 1, 50, 0 ), 'Empty filtered results need an explicit zero count.' );
$assert( 'This pool has no inventory yet.' === $view->empty_state_title( true, 'all', null ), 'Empty pools need pool-level guidance.' );
$assert( 'No assigned One-Time Codes match this filter.' === $view->empty_state_title( false, 'assigned', null ), 'Assigned-only emptiness must describe the selected filter.' );
$assert( 'No codes match this import filter.' === $view->empty_state_title( false, 'all', 12 ), 'Import-filter emptiness must be contextual.' );


$repository = new class() implements CodeInventoryRepository {
	/** @var array<string,mixed> */
	public array $last_search = array();

	public function search( int $pool_id, ?CodeStatus $status, ?int $import_id, int $limit, int $offset ): array {
		$this->last_search = compact( 'pool_id', 'status', 'import_id', 'limit', 'offset' );
		return array();
	}

	public function count_matching( int $pool_id, ?CodeStatus $status, ?int $import_id ): int {
		unset( $pool_id, $status, $import_id );
		return 120;
	}

	public function counts( int $pool_id ): array {
		unset( $pool_id );
		return array( 'total' => 120, 'available' => 80, 'assigned' => 40 );
	}

	public function import_options( int $pool_id ): array {
		unset( $pool_id );
		return array(
			array( 'id' => 12, 'filename' => 'codes.csv' ),
			array( 'id' => 14, 'filename' => 'later.txt' ),
		);
	}
};

$data = ( new InventoryData( $repository, $view ) )->get( 7, 'available', 12, 3, 50 );
$assert( 3 === $data['page'] && 3 === $data['pages'], 'Inventory pagination must be bounded by the matching result count.' );
$assert( 100 === $repository->last_search['offset'], 'Inventory pagination must use deterministic page offsets.' );
$assert( 12 === $repository->last_search['import_id'], 'Valid imports must remain scoped to the selected pool.' );

( new InventoryData( $repository, $view ) )->get( 7, 'reserved', 999, 1, 1 );
$assert( null === $repository->last_search['status'], 'Unknown state input must safely fall back to the public all-state scope.' );
$assert( null === $repository->last_search['import_id'], 'Unknown import input must not become a repository filter.' );
$assert( 10 === $repository->last_search['limit'], 'Per-page input must have a safe lower bound.' );

$repository_source = file_get_contents( $root . '/src/Infrastructure/WordPress/WpdbCodeInventoryRepository.php' );
$admin_source      = file_get_contents( $root . '/src/Admin/InventoryAdmin.php' );
$template_source   = file_get_contents( $root . '/templates/admin/inventory.php' );
$pools_source      = file_get_contents( $root . '/templates/admin/pools.php' );
$schema_source     = file_get_contents( $root . '/voucher-manager.php' );
$composer_source   = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $repository_source )
	&& str_contains( $repository_source, 'LEFT JOIN {$imports} i ON i.id = c.import_id AND i.pool_id = c.pool_id' )
	&& str_contains( $repository_source, 'i.filename AS import_filename' )
	&& str_contains( $repository_source, "CASE WHEN CHAR_LENGTH(c.code) > 4 THEN RIGHT(c.code, 4) ELSE '' END AS code_suffix" )
	&& ! str_contains( $repository_source, 'SELECT id, pool_id, import_id, code,' ),
	'Repository must never hydrate the complete One-Time Code value for inventory presentation.'
);
$assert(
	str_contains( $repository_source, "status IN (%s, %s)" )
	&& str_contains( $repository_source, 'CodeStatus::AVAILABLE->value' )
	&& str_contains( $repository_source, 'CodeStatus::ASSIGNED->value' ),
	'Inventory queries must remain limited to approved user-facing states.'
);
$assert(
	is_string( $admin_source )
	&& str_contains( $admin_source, "current_user_can( 'manage_options' )" )
	&& str_contains( $admin_source, 'sanitize_key' )
	&& str_contains( $admin_source, 'absint' ),
	'Inventory access must retain capability and sanitized request handling.'
);
$assert(
	is_string( $template_source )
	&& str_contains( $template_source, 'References are masked' )
	&& str_contains( $template_source, 'Pool totals' )
	&& str_contains( $template_source, 'result_range' )
	&& str_contains( $template_source, 'active_filter_summary' )
	&& str_contains( $template_source, 'empty_state_title' )
	&& str_contains( $template_source, 'Reset filters' )
	&& str_contains( $template_source, 'voucher-manager__table-scroll' )
	&& str_contains( $template_source, 'import_reference' )
	&& str_contains( $template_source, 'formatted_imported_at' )
	&& str_contains( $template_source, 'formatted_assigned_at' )
	&& str_contains( $template_source, 'lifecycle_integrity' )
	&& str_contains( $template_source, 'lifecycle_note' )
	&& ! str_contains( $template_source, 'Copy code' )
	&& ! str_contains( $template_source, 'Reveal' )
	&& ! str_contains( $template_source, 'voucher_code' ),
	'Inventory UI must explain masking and must not expose copy or reveal actions.'
);
$assert(
	is_string( $pools_source )
	&& str_contains( $pools_source, "'page'    => 'voucher-manager-inventory'" )
	&& str_contains( $pools_source, 'View inventory' ),
	'Every Pool card must expose a deliberate Inventory path.'
);
$assert(
	is_string( $schema_source )
	&& str_contains( $schema_source, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Read-only Inventory must not introduce an unnecessary schema migration.'
);
$assert(
	is_string( $composer_source )
	&& str_contains( $composer_source, '@test:inventory-experience' )
	&& strpos( $composer_source, '@test:inventory-experience' ) < strpos( $composer_source, '@build' ),
	'Inventory Experience coverage must run before the release build.'
);

echo "Inventory experience OK: pool scoping, public-state filters, pagination and masked-reference privacy verified.\n";
