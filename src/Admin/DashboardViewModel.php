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
					_n( 'Deleted %d available One-Time Code', 'Deleted %d available One-Time Codes', $deleted, 'mjs-productions-voucher-manager' ),
					$deleted
				);
			}
		}

		return match ( $event_type ) {
			'import.completed'             => __( 'Import completed', 'mjs-productions-voucher-manager' ),
			'import.failed'                => __( 'Import failed', 'mjs-productions-voucher-manager' ),
			'import.rolled_back'           => __( 'Import rolled back', 'mjs-productions-voucher-manager' ),
			'import.rollback_blocked'      => __( 'Import rollback blocked', 'mjs-productions-voucher-manager' ),
			'distribution.completed'       => __( 'One-Time Code distributed', 'mjs-productions-voucher-manager' ),
			'distribution.empty'           => __( 'Pool has no available One-Time Codes', 'mjs-productions-voucher-manager' ),
			'distribution.failed'          => __( 'Distribution failed', 'mjs-productions-voucher-manager' ),
			'admin.action_failed'          => __( 'Administrative action failed', 'mjs-productions-voucher-manager' ),
			'settings.updated'             => __( 'Settings updated', 'mjs-productions-voucher-manager' ),
			'activity.cleanup_completed'    => __( 'Activity cleanup completed', 'mjs-productions-voucher-manager' ),
			'activity.cleanup_failed'       => __( 'Activity cleanup failed', 'mjs-productions-voucher-manager' ),
			'plugin.installed'            => __( 'Voucher Manager installed', 'mjs-productions-voucher-manager' ),
			'plugin.activated'            => __( 'Voucher Manager activated', 'mjs-productions-voucher-manager' ),
			'plugin.deactivated'          => __( 'Voucher Manager deactivated', 'mjs-productions-voucher-manager' ),
			'plugin.uninstalled'          => __( 'Voucher Manager uninstalled', 'mjs-productions-voucher-manager' ),
			'pool.created'                 => __( 'Pool created', 'mjs-productions-voucher-manager' ),
			'pool.updated'                 => __( 'Pool updated', 'mjs-productions-voucher-manager' ),
			'pool.activated'               => __( 'Pool activated', 'mjs-productions-voucher-manager' ),
			'pool.deactivated'             => __( 'Pool deactivated', 'mjs-productions-voucher-manager' ),
			'pool.available_codes_deleted' => __( 'Available One-Time Codes deleted', 'mjs-productions-voucher-manager' ),
			'pool.deleted'                 => __( 'Pool deleted', 'mjs-productions-voucher-manager' ),
			'pool.delete_failed'           => __( 'Pool deletion failed', 'mjs-productions-voucher-manager' ),
			default                        => __( 'Voucher Manager activity', 'mjs-productions-voucher-manager' ),
		};
	}

	/**
	 * Return the visual tone for an operational event.
	 */
	public function activity_tone( string $event_type ): string {
		return match ( $event_type ) {
			'import.completed',
			'import.rolled_back',
			'distribution.completed',
			'settings.updated',
			'activity.cleanup_completed',
			'pool.created',
			'pool.updated',
			'pool.activated',
			'pool.deactivated' => 'success',
			'distribution.empty',
			'import.rollback_blocked',
			'pool.available_codes_deleted',
			'pool.deleted' => 'warning',
			'import.failed',
			'distribution.failed',
			'admin.action_failed',
			'activity.cleanup_failed',
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
		$pool_name = isset( $context['pool_name'] )
			? trim( (string) $context['pool_name'] )
			: '';

		if ( 'distribution.completed' === $event_type && null !== $remaining ) {
			$inventory = sprintf(
				/* translators: %d: number of available One-Time Codes remaining in the pool */
				_n( 'Remaining inventory: %d One-Time Code', 'Remaining inventory: %d One-Time Codes', $remaining, 'mjs-productions-voucher-manager' ),
				$remaining
			);

			if ( '' === $pool_name ) {
				return $inventory;
			}

			$pool = sprintf(
				/* translators: %s: Pool name */
				__( 'Pool: %s', 'mjs-productions-voucher-manager' ),
				$pool_name
			);

			return $pool . "\n\n" . $inventory;
		}

		if ( 'import.completed' === $event_type ) {
			$imported = isset( $context['imported'] ) ? absint( $context['imported'] ) : 0;
			$skipped  = isset( $context['skipped'] ) ? absint( $context['skipped'] ) : 0;
			$invalid  = isset( $context['invalid'] ) ? absint( $context['invalid'] ) : 0;
			$parts    = array();

			if ( '' !== $pool_name ) {
				$parts[] = sprintf(
					/* translators: %s: Pool name */
					__( 'Pool: %s', 'mjs-productions-voucher-manager' ),
					$pool_name
				);
			} elseif ( 0 < $pool_id ) {
				$parts[] = sprintf(
					/* translators: %d: internal pool ID */
					__( 'Pool #%d', 'mjs-productions-voucher-manager' ),
					$pool_id
				);
			}

			$parts[] = sprintf(
				/* translators: %d: number of imported codes */
				_n( '%d One-Time Code added', '%d One-Time Codes added', $imported, 'mjs-productions-voucher-manager' ),
				$imported
			);
			$parts[] = sprintf(
				/* translators: %d: number of skipped rows */
				_n( '%d skipped', '%d skipped', $skipped, 'mjs-productions-voucher-manager' ),
				$skipped
			);
			$parts[] = sprintf(
				/* translators: %d: number of invalid rows */
				_n( '%d invalid', '%d invalid', $invalid, 'mjs-productions-voucher-manager' ),
				$invalid
			);

			return implode( ' · ', $parts );
		}

		if ( '' !== $pool_name ) {
			return sprintf(
				/* translators: %s: Pool name */
				__( 'Pool: %s', 'mjs-productions-voucher-manager' ),
				$pool_name
			);
		}

		if ( 0 < $pool_id ) {
			return sprintf(
				/* translators: %d: internal pool ID */
				__( 'Pool #%d', 'mjs-productions-voucher-manager' ),
				$pool_id
			);
		}

		if ( 0 < $import_id ) {
			return sprintf(
				/* translators: %d: internal import ID */
				__( 'Import #%d', 'mjs-productions-voucher-manager' ),
				$import_id
			);
		}

		return '';
	}
}
