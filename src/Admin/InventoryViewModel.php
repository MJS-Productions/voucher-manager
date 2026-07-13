<?php
/**
 * Inventory presentation rules.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Code\CodeInventoryRecord;
use VoucherManager\Domain\Code\CodeStatus;

/**
 * Converts privacy-safe inventory data into administrator presentation.
 */
final class InventoryViewModel {

	public function reference( CodeInventoryRecord $record ): string {
		$suffix = trim( $record->code_suffix() );

		if ( '' === $suffix ) {
			return sprintf(
				/* translators: %d: internal code ID */
				__( 'Code #%d', 'voucher-manager' ),
				$record->id()
			);
		}

		return '••••••••' . $suffix;
	}

	public function status_label( CodeStatus $status ): string {
		return match ( $status ) {
			CodeStatus::AVAILABLE => __( 'Available', 'voucher-manager' ),
			CodeStatus::ASSIGNED  => __( 'Assigned', 'voucher-manager' ),
			default               => __( 'Other', 'voucher-manager' ),
		};
	}

	public function status_tone( CodeStatus $status ): string {
		return match ( $status ) {
			CodeStatus::AVAILABLE => 'success',
			CodeStatus::ASSIGNED  => 'neutral',
			default               => 'neutral',
		};
	}

	public function state_from_request( string $state ): ?CodeStatus {
		return match ( $state ) {
			'available' => CodeStatus::AVAILABLE,
			'assigned'  => CodeStatus::ASSIGNED,
			default     => null,
		};
	}

	public function normalized_state( string $state ): string {
		return in_array( $state, array( 'all', 'available', 'assigned' ), true )
			? $state
			: 'all';
	}

	/**
	 * @param array<int,array{id:int,filename:string}> $options Import options.
	 */
	public function normalized_import_id( int $requested_import_id, array $options ): ?int {
		if ( 0 >= $requested_import_id ) {
			return null;
		}

		foreach ( $options as $option ) {
			if ( $requested_import_id === $option['id'] ) {
				return $requested_import_id;
			}
		}

		return null;
	}
}
