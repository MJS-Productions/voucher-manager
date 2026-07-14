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
	public function has_active_filters( string $state, ?int $import_id ): bool {
		return 'all' !== $state || null !== $import_id;
	}

	/**
	 * @param array<int,array{id:int,filename:string}> $options Import options.
	 */
	public function active_filter_summary( string $state, ?int $import_id, array $options ): string {
		$parts = array();

		if ( 'available' === $state ) {
			$parts[] = __( 'Available', 'voucher-manager' );
		} elseif ( 'assigned' === $state ) {
			$parts[] = __( 'Assigned', 'voucher-manager' );
		}

		if ( null !== $import_id ) {
			foreach ( $options as $option ) {
				if ( $import_id === $option['id'] ) {
					$parts[] = sprintf(
						/* translators: 1: import ID, 2: filename */
						__( 'Import #%1$d — %2$s', 'voucher-manager' ),
						$option['id'],
						$option['filename']
					);
					break;
				}
			}
		}

		return implode( ' · ', $parts );
	}

	public function result_range( int $page, int $per_page, int $total ): string {
		if ( 0 === $total ) {
			return __( '0 matching records', 'voucher-manager' );
		}

		$first = ( ( $page - 1 ) * $per_page ) + 1;
		$last  = min( $total, $page * $per_page );

		return sprintf(
			/* translators: 1: first result, 2: last result, 3: total matching results */
			__( 'Showing %1$d–%2$d of %3$d matching records', 'voucher-manager' ),
			$first,
			$last,
			$total
		);
	}

	public function empty_state_title( bool $pool_empty, string $state, ?int $import_id ): string {
		if ( $pool_empty ) {
			return __( 'This pool has no inventory yet.', 'voucher-manager' );
		}

		if ( null !== $import_id ) {
			return __( 'No codes match this import filter.', 'voucher-manager' );
		}

		return match ( $state ) {
			'available' => __( 'No available codes match this filter.', 'voucher-manager' ),
			'assigned'  => __( 'No assigned codes match this filter.', 'voucher-manager' ),
			default     => __( 'No matching inventory found.', 'voucher-manager' ),
		};
	}

	public function empty_state_message( bool $pool_empty, string $state, ?int $import_id ): string {
		if ( $pool_empty ) {
			return __( 'Import codes to make this pool ready for distribution.', 'voucher-manager' );
		}

		if ( null !== $import_id ) {
			return __( 'Reset the filters to return to the complete pool inventory.', 'voucher-manager' );
		}

		return match ( $state ) {
			'available' => __( 'This pool has inventory, but none of it is currently available.', 'voucher-manager' ),
			'assigned'  => __( 'This pool has inventory, but no codes have been assigned yet.', 'voucher-manager' ),
			default     => __( 'Reset the filters to return to the complete pool inventory.', 'voucher-manager' ),
		};
	}

}
