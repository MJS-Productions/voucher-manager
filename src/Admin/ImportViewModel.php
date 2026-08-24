<?php
/**
 * Import administration presentation rules.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Import\ImportRecord;

final class ImportViewModel {
	public function status_label( ImportRecord $import ): string {
		return match ( $import->status() ) {
			'completed'   => __( 'Completed', 'mjs-productions-voucher-manager' ),
			'rolled_back' => __( 'Rolled back', 'mjs-productions-voucher-manager' ),
			'failed'      => __( 'Failed', 'mjs-productions-voucher-manager' ),
			'processing'  => __( 'Processing', 'mjs-productions-voucher-manager' ),
			default       => ucwords( str_replace( '_', ' ', $import->status() ) ),
		};
	}

	public function status_tone( ImportRecord $import ): string {
		return match ( $import->status() ) {
			'completed' => 'success',
			'failed'    => 'error',
			default     => 'neutral',
		};
	}

	public function result_summary( ImportRecord $import ): string {
		return sprintf(
			/* translators: 1: imported rows, 2: skipped rows, 3: invalid rows, 4: total rows. */
			__( '%1$d added, %2$d skipped, %3$d invalid — %4$d rows processed', 'mjs-productions-voucher-manager' ),
			$import->imported_rows(),
			$import->skipped_rows(),
			$import->invalid_rows(),
			$import->total_rows()
		);
	}

	public function can_review_rollback( ImportRecord $import, int $assigned_count = 0 ): bool {
		return 'completed' === $import->status()
			&& 0 < $import->imported_rows()
			&& 0 === $assigned_count;
	}
	/**
	 * Return a requested destination pool only when it exists in the loaded inventory rows.
	 *
	 * @param array<int,array{pool:\VoucherManager\Domain\Pool\Pool,total:int,available:int,assigned:int}> $pool_rows Pool inventory rows.
	 */
	public function selected_pool_id( int $requested_pool_id, array $pool_rows ): int {
		if ( 0 >= $requested_pool_id ) {
			return 0;
		}

		foreach ( $pool_rows as $row ) {
			if ( $requested_pool_id === (int) $row['pool']->id() ) {
				return $requested_pool_id;
			}
		}

		return 0;
	}

}
