<?php
/**
 * Public Activity metadata extension contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

use VoucherManager\Activity\ActivityMetadata;

/**
 * Lets extensions register presentation and classification metadata for
 * extension-owned Activity event types.
 */
final class ActivityMetadataApi {

	/**
	 * Register one extension-owned Activity event type.
	 *
	 * Voucher Manager-owned event types cannot be overridden. The family must
	 * be one of all, import, distribution, pool, settings, or admin. The tone
	 * must be neutral, success, warning, or error.
	 *
	 * @param callable(array<string,mixed>):string|null $detail Detail resolver.
	 */
	public function register(
		string $event_type,
		string $label,
		string $family,
		string $tone,
		?callable $detail = null
	): bool {
		return ActivityMetadata::register_extension(
			$event_type,
			$label,
			$family,
			$tone,
			$detail
		);
	}
}
