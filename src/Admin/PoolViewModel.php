<?php
/**
 * Pool administration presentation rules.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Pool\Pool;

/**
 * Converts pool inventory into concise, consistent UI states.
 */
final class PoolViewModel {

	/**
	 * Return the inventory state for a pool.
	 */
	public function inventory_state( Pool $pool, int $available ): string {
		if ( ! $pool->is_active() ) {
			return 'inactive';
		}

		if ( 0 === $available ) {
			return 'empty';
		}

		if ( 0 < $pool->warning_threshold() && $available <= $pool->warning_threshold() ) {
			return 'low';
		}

		return 'ready';
	}

	/**
	 * Return a readable inventory label.
	 */
	public function inventory_label( Pool $pool, int $available ): string {
		return match ( $this->inventory_state( $pool, $available ) ) {
			'inactive' => __( 'Inactive', 'voucher-manager' ),
			'empty'    => __( 'Empty', 'voucher-manager' ),
			'low'      => __( 'Low stock', 'voucher-manager' ),
			default    => __( 'Ready', 'voucher-manager' ),
		};
	}

	/**
	 * Return a concise stock hint.
	 */
	public function inventory_hint( Pool $pool, int $available ): string {
		if ( ! $pool->is_active() ) {
			return __( 'Distribution is paused.', 'voucher-manager' );
		}

		if ( 0 === $available ) {
			return __( 'Import codes to continue distribution.', 'voucher-manager' );
		}

		if ( 0 < $pool->warning_threshold() && $available <= $pool->warning_threshold() ) {
			return sprintf(
				/* translators: %d: configured warning threshold */
				__( 'Warning threshold: %d', 'voucher-manager' ),
				$pool->warning_threshold()
			);
		}

		return __( 'Available for distribution.', 'voucher-manager' );
	}
}
