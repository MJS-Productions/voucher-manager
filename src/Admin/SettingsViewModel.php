<?php
/**
 * Settings presentation rules.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Domain\Settings\Settings;

/**
 * Provides labels and warning copy for the minimal Settings surface.
 */
final class SettingsViewModel {

	/**
	 * @return array<int,string>
	 */
	public function retention_options(): array {
		return array(
			30  => __( '30 days', 'voucher-manager' ),
			90  => __( '90 days', 'voucher-manager' ),
			180 => __( '180 days', 'voucher-manager' ),
			0   => __( 'Keep indefinitely', 'voucher-manager' ),
		);
	}

	public function retention_description( int $days ): string {
		return 0 === $days
			? __( 'Operational Activity will not be removed automatically.', 'voucher-manager' )
			: sprintf(
				/* translators: %d: number of retention days */
				__( 'Operational Activity older than %d days will become eligible for scheduled cleanup.', 'voucher-manager' ),
				$days
			);
	}

	public function uninstall_warning(): string {
		return __( 'When enabled, uninstalling Voucher Manager will permanently delete all Pools, Imports, Codes, Activity and plugin settings. Deactivation never deletes data.', 'voucher-manager' );
	}

	public function defaults(): Settings {
		return Settings::defaults();
	}
}
