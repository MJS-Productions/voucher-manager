<?php
/**
 * Public Activity retention archive hand-off extension contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

use UnexpectedValueException;

/**
 * Bridges retention candidates to an optional external archive consumer.
 */
final class ActivityRetentionArchiveHandoff {

	public const FILTER = 'voucher_manager_activity_retention_archive';

	public static function is_active(): bool {
		return false !== has_filter( self::FILTER );
	}

	/**
	 * Offers concrete retention candidates for safe external archiving.
	 *
	 * Consumers must persist the supplied Activity business records before
	 * returning their IDs. Returning an ID confirms that the corresponding
	 * archive record is safely persisted and the active record may be deleted.
	 *
	 * @param array<int,array{
	 *   id:int,
	 *   event_type:string,
	 *   message:string,
	 *   context:?string,
	 *   created_at:string
	 * }> $candidates Activity retention candidates.
	 * @return array<int> Confirmed candidate IDs.
	 */
	public static function archive( array $candidates ): array {
		$confirmed_ids = apply_filters( self::FILTER, array(), $candidates );

		if ( ! is_array( $confirmed_ids ) ) {
			throw new UnexpectedValueException( 'Activity retention archive filter must return confirmed Activity IDs.' );
		}

		return $confirmed_ids;
	}
}
