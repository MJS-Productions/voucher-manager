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
	'',
	CodeStatus::AVAILABLE,
	'2026-07-13 10:00:00',
	null
);
$assert( 'Code #102' === $view->reference( $short ), 'Short or unavailable suffixes must fall back to an internal reference.' );
$assert( 'all' === $view->normalized_state( 'reserved' ), 'Prepared states must not become public filters.' );
$assert( CodeStatus::AVAILABLE === $view->state_from_request( 'available' ), 'Available filter must map to the active workflow state.' );
$assert( CodeStatus::ASSIGNED === $view->state_from_request( 'assigned' ), 'Assigned filter must map to the active workflow state.' );
$assert( null === $view->state_from_request( 'all' ), 'All-state filtering must use the repository public-state scope.' );

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
	&& str_contains( $repository_source, "CASE WHEN CHAR_LENGTH(code) > 4 THEN RIGHT(code, 4) ELSE '' END AS code_suffix" )
	&& ! str_contains( $repository_source, 'SELECT id, pool_id, import_id, code,' ),
	'Repository must never hydrate the complete voucher value for inventory presentation.'
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
