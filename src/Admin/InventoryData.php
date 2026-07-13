<?php
/**
 * Inventory data orchestration.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Code\CodeInventoryRepository;

/**
 * Prepares pool-scoped, filtered and paginated inventory data.
 */
final class InventoryData {

	public function __construct(
		private readonly CodeInventoryRepository $repository,
		private readonly InventoryViewModel $view
	) {
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get(
		int $pool_id,
		string $requested_state,
		int $requested_import_id,
		int $page,
		int $per_page = 50
	): array {
		$state          = $this->view->normalized_state( $requested_state );
		$status         = $this->view->state_from_request( $state );
		$import_options = $this->repository->import_options( $pool_id );
		$import_id      = $this->view->normalized_import_id( $requested_import_id, $import_options );
		$per_page       = min( 100, max( 10, $per_page ) );
		$page           = max( 1, $page );

		$total = $this->repository->count_matching( $pool_id, $status, $import_id );
		$pages = max( 1, (int) ceil( $total / $per_page ) );
		$page  = min( $page, $pages );

		return array(
			'records'        => $this->repository->search(
				$pool_id,
				$status,
				$import_id,
				$per_page,
				( $page - 1 ) * $per_page
			),
			'counts'         => $this->repository->counts( $pool_id ),
			'import_options' => $import_options,
			'filters'        => array(
				'state'     => $state,
				'import_id' => $import_id,
			),
			'total'          => $total,
			'page'           => $page,
			'per_page'       => $per_page,
			'pages'          => $pages,
		);
	}
}
