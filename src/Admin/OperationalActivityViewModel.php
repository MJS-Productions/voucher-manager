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
			'success' => __( 'Success', 'mjs-productions-voucher-manager' ),
			'warning' => __( 'Attention', 'mjs-productions-voucher-manager' ),
			'error'   => __( 'Error', 'mjs-productions-voucher-manager' ),
			default   => __( 'Information', 'mjs-productions-voucher-manager' ),
		};
	}

	public function guidance( string $event_type ): string {
		return match ( $event_type ) {
			'distribution.empty' =>
				__( 'Import additional One-Time Codes into this pool before the next distribution.', 'mjs-productions-voucher-manager' ),
			'import.rollback_blocked' =>
				__( 'This import contains assigned One-Time Codes and cannot be rolled back safely.', 'mjs-productions-voucher-manager' ),
			'import.failed' =>
				__( 'Review the source file and retry the import. Technical details remain in the WordPress error log.', 'mjs-productions-voucher-manager' ),
			'distribution.failed' =>
				__( 'Retry the distribution and confirm that the selected pool is active and has inventory.', 'mjs-productions-voucher-manager' ),
			'admin.action_failed' =>
				__( 'Retry the administrative action. If it fails again, review the WordPress error log.', 'mjs-productions-voucher-manager' ),
			'pool.delete_failed' =>
				__( 'The deletion was rolled back. The pool data should remain intact; retry or review the WordPress error log.', 'mjs-productions-voucher-manager' ),
			'pool.available_codes_deleted',
			'pool.deleted' =>
				__( 'No One-Time Code values were retained in Activity.', 'mjs-productions-voucher-manager' ),
			default => '',
		};
	}


	public function has_active_filters( string $family, string $tone ): bool {
		return 'all' !== $family || 'all' !== $tone;
	}

	public function family_label( string $family ): string {
		return match ( $family ) {
			'import'       => __( 'Imports', 'mjs-productions-voucher-manager' ),
			'distribution' => __( 'Distribution', 'mjs-productions-voucher-manager' ),
			'pool'         => __( 'Pools', 'mjs-productions-voucher-manager' ),
			'settings'     => __( 'Settings', 'mjs-productions-voucher-manager' ),
			'admin'        => __( 'Administration', 'mjs-productions-voucher-manager' ),
			default        => __( 'All activity', 'mjs-productions-voucher-manager' ),
		};
	}
}
