<?php
/**
 * Dashboard presentation rules.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

/**
 * Converts operational event names into human-readable dashboard content.
 */
final class DashboardViewModel {

	/**
	 * Return a translated label for an operational event.
	 */
	public function activity_label( string $event_type ): string {
		return match ( $event_type ) {
			'import.completed'         => __( 'Import completed', 'voucher-manager' ),
			'import.failed'            => __( 'Import failed', 'voucher-manager' ),
			'import.rolled_back'       => __( 'Import rolled back', 'voucher-manager' ),
			'import.rollback_blocked'  => __( 'Import rollback blocked', 'voucher-manager' ),
			'distribution.completed'   => __( 'Code distributed', 'voucher-manager' ),
			'distribution.empty'       => __( 'Pool has no available codes', 'voucher-manager' ),
			'distribution.failed'      => __( 'Distribution failed', 'voucher-manager' ),
			'admin.action_failed'      => __( 'Administrative action failed', 'voucher-manager' ),
			default                    => __( 'Voucher Manager activity', 'voucher-manager' ),
		};
	}

	/**
	 * Return the visual tone for an operational event.
	 */
	public function activity_tone( string $event_type ): string {
		return match ( $event_type ) {
			'import.completed',
			'import.rolled_back',
			'distribution.completed' => 'success',
			'distribution.empty',
			'import.rollback_blocked' => 'warning',
			'import.failed',
			'distribution.failed',
			'admin.action_failed' => 'error',
			default => 'neutral',
		};
	}

	/**
	 * Return a concise activity detail without exposing voucher values.
	 *
	 * @param array<string,mixed> $context Operational context.
	 */
	public function activity_detail( string $event_type, array $context ): string {
		$pool_id   = isset( $context['pool_id'] ) ? absint( $context['pool_id'] ) : 0;
		$import_id = isset( $context['import_id'] ) ? absint( $context['import_id'] ) : 0;
		$remaining = isset( $context['remaining'] ) ? absint( $context['remaining'] ) : null;

		if ( 'distribution.completed' === $event_type && null !== $remaining ) {
			return sprintf(
				/* translators: %d: number of remaining available codes */
				_n( '%d code remains available.', '%d codes remain available.', $remaining, 'voucher-manager' ),
				$remaining
			);
		}

		if ( 0 < $pool_id ) {
			return sprintf(
				/* translators: %d: internal pool ID */
				__( 'Pool #%d', 'voucher-manager' ),
				$pool_id
			);
		}

		if ( 0 < $import_id ) {
			return sprintf(
				/* translators: %d: internal import ID */
				__( 'Import #%d', 'voucher-manager' ),
				$import_id
			);
		}

		return '';
	}
}
