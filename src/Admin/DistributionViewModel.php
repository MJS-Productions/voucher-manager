<?php
/**
 * Distribution presentation rules.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Pool\Pool;

/**
 * Keeps manual-distribution guidance and inventory presentation out of templates.
 */
final class DistributionViewModel {

	/**
	 * @param array{pool:Pool,total:int,available:int,assigned:int} $row Pool inventory row.
	 */
	public function pool_option_label( array $row ): string {
		return sprintf(
			__( '%1$s — %2$d available, %3$d total', 'voucher-manager' ),
			$row['pool']->name(),
			$row['available'],
			$row['total']
		);
	}

	/**
	 * @param array{pool:Pool,total:int,available:int,assigned:int} $row Pool inventory row.
	 */
	public function can_distribute( array $row ): bool {
		return $row['pool']->is_active() && 0 < $row['available'];
	}

	public function remaining_message( ?int $remaining ): string {
		if ( null === $remaining ) {
			return __( 'The One-Time Code was assigned successfully. Remaining inventory could not be refreshed.', 'voucher-manager' );
		}

		if ( 0 === $remaining ) {
			return __( 'This pool is now empty. Import more One-Time Codes before the next distribution.', 'voucher-manager' );
		}

		return sprintf(
			/* translators: %d: number of available One-Time Codes remaining in the pool */
			_n(
				'%d One-Time Code remains available in this pool.',
				'%d One-Time Codes remain available in this pool.',
				$remaining,
				'voucher-manager'
			),
			$remaining
		);
	}

	public function result_tone( ?int $remaining ): string {
		return null === $remaining || 0 === $remaining ? 'warning' : 'success';
	}
}
