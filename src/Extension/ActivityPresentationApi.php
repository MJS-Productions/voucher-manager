<?php
/**
 * Public Activity presentation extension contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

use VoucherManager\Admin\DashboardViewModel;

/**
 * Provides supported access to Voucher Manager-owned Activity presentation.
 */
final class ActivityPresentationApi {

	private DashboardViewModel $events;

	public function __construct( ?DashboardViewModel $events = null ) {
		$this->events = $events ?? new DashboardViewModel();
	}

	/**
	 * Return the translated human-readable label for an Activity event.
	 *
	 * Unknown event types are returned unchanged so extensions can preserve
	 * transparent machine identifiers for events Voucher Manager does not own.
	 *
	 * @param array<string,mixed> $context Operational context.
	 */
	public function label( string $event_type, array $context = array() ): string {
		return $this->events->activity_label( $event_type, $context );
	}
}
