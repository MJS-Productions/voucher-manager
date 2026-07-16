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
	 *
	 * @param array<string,mixed> $context Operational context.
	 */
	public function activity_label( string $event_type, array $context = array() ): string {
		if ( 'pool.available_codes_deleted' === $event_type ) {
			$deleted = isset( $context['deleted_available_count'] )
				? absint( $context['deleted_available_count'] )
				: null;

			if ( null !== $deleted ) {
				return sprintf(
					/* translators: %d: number of permanently deleted available codes */
					_n( 'Deleted %d available One-Time Code', 'Deleted %d available One-Time Codes', $deleted, 'voucher-manager' ),
					$deleted
				);
			}
		}

		return match ( $event_type ) {
			'import.completed'             => __( 'Import completed', 'voucher-manager' ),
			'import.failed'                => __( 'Import failed', 'voucher-manager' ),
			'import.rolled_back'           => __( 'Import rolled back', 'voucher-manager' ),
			'import.rollback_blocked'      => __( 'Import rollback blocked', 'voucher-manager' ),
			'distribution.completed'       => __( 'One-Time Code distributed', 'voucher-manager' ),
			'distribution.empty'           => __( 'Pool has no available One-Time Codes', 'voucher-manager' ),
			'distribution.failed'          => __( 'Distribution failed', 'voucher-manager' ),
			'admin.action_failed'          => __( 'Administrative action failed', 'voucher-manager' ),
			'pool.available_codes_deleted' => __( 'Available One-Time Codes deleted', 'voucher-manager' ),
			'pool.deleted'                 => __( 'Pool deleted', 'voucher-manager' ),
			'pool.delete_failed'           => __( 'Pool deletion failed', 'voucher-manager' ),
			default                        => __( 'Voucher Manager activity', 'voucher-manager' ),
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
			'import.rollback_blocked',
			'pool.available_codes_deleted',
			'pool.deleted' => 'warning',
			'import.failed',
			'distribution.failed',
			'admin.action_failed',
			'pool.delete_failed' => 'error',
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
				_n( '%d One-Time Code remains available.', '%d One-Time Codes remain available.', $remaining, 'voucher-manager' ),
				$remaining
			);
		}

		if ( 'import.completed' === $event_type ) {
			$imported = isset( $context['imported'] ) ? absint( $context['imported'] ) : 0;
			$skipped  = isset( $context['skipped'] ) ? absint( $context['skipped'] ) : 0;
			$invalid  = isset( $context['invalid'] ) ? absint( $context['invalid'] ) : 0;
			$parts    = array();

			if ( 0 < $pool_id ) {
				$parts[] = sprintf(
					/* translators: %d: internal pool ID */
					__( 'Pool #%d', 'voucher-manager' ),
					$pool_id
				);
			}

			$parts[] = sprintf(
				/* translators: %d: number of imported codes */
				_n( '%d One-Time Code added', '%d One-Time Codes added', $imported, 'voucher-manager' ),
				$imported
			);
			$parts[] = sprintf(
				/* translators: %d: number of skipped rows */
				_n( '%d skipped', '%d skipped', $skipped, 'voucher-manager' ),
				$skipped
			);
			$parts[] = sprintf(
				/* translators: %d: number of invalid rows */
				_n( '%d invalid', '%d invalid', $invalid, 'voucher-manager' ),
				$invalid
			);

			return implode( ' · ', $parts );
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
