<?php
/**
 * Operational activity presentation rules.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

/**
 * Converts privacy-safe operational events into useful administrator guidance.
 */
final class OperationalActivityViewModel {

	private DashboardViewModel $events;

	public function __construct() {
		$this->events = new DashboardViewModel();
	}

	/** @param array<string,mixed> $context Event context. */
	public function label( string $event_type, array $context ): string {
		return $this->events->activity_label( $event_type, $context );
	}

	/** @param array<string,mixed> $context Event context. */
	public function detail( string $event_type, array $context ): string {
		return $this->events->activity_detail( $event_type, $context );
	}

	public function tone( string $event_type ): string {
		return $this->events->activity_tone( $event_type );
	}

	public function severity_label( string $event_type ): string {
		return match ( $this->tone( $event_type ) ) {
			'success' => __( 'Success', 'voucher-manager' ),
			'warning' => __( 'Attention', 'voucher-manager' ),
			'error'   => __( 'Error', 'voucher-manager' ),
			default   => __( 'Information', 'voucher-manager' ),
		};
	}

	public function guidance( string $event_type ): string {
		return match ( $event_type ) {
			'distribution.empty' =>
				__( 'Import additional codes into this pool before the next distribution.', 'voucher-manager' ),
			'import.rollback_blocked' =>
				__( 'This import contains assigned codes and cannot be rolled back safely.', 'voucher-manager' ),
			'import.failed' =>
				__( 'Review the source file and retry the import. Technical details remain in the WordPress error log.', 'voucher-manager' ),
			'distribution.failed' =>
				__( 'Retry the distribution and confirm that the selected pool is active and has inventory.', 'voucher-manager' ),
			'admin.action_failed' =>
				__( 'Retry the administrative action. If it fails again, review the WordPress error log.', 'voucher-manager' ),
			'pool.delete_failed' =>
				__( 'The deletion was rolled back. The pool data should remain intact; retry or review the WordPress error log.', 'voucher-manager' ),
			'pool.available_codes_deleted',
			'pool.deleted' =>
				__( 'This was a permanent lifecycle action. No voucher values were retained in activity.', 'voucher-manager' ),
			default => '',
		};
	}


	public function has_active_filters( string $family, string $tone ): bool {
		return 'all' !== $family || 'all' !== $tone;
	}

	public function family_label( string $family ): string {
		return match ( $family ) {
			'import'       => __( 'Imports', 'voucher-manager' ),
			'distribution' => __( 'Distribution', 'voucher-manager' ),
			'pool'         => __( 'Pools', 'voucher-manager' ),
			'admin'        => __( 'Administration', 'voucher-manager' ),
			default        => __( 'All activity', 'voucher-manager' ),
		};
	}
}
