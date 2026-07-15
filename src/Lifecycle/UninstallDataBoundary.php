<?php
/**
 * Uninstall data boundary.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Lifecycle;

/**
 * Defines exactly which site-scoped resources Voucher Manager owns.
 */
final class UninstallDataBoundary {

	public const SETTINGS_OPTION = 'voucher_manager_settings';
	public const VERSION_OPTION = 'voucher_manager_version';
	public const DATABASE_VERSION_OPTION = 'voucher_manager_database_version';
	public const ACTIVITY_CRON_HOOK = 'voucher_manager_cleanup_activity';
	public const DISTRIBUTION_INTENT_OPTION_PREFIX = 'voucher_manager_distribution_intent_';

	/**
	 * @return array<int,string>
	 */
	public static function tables( string $prefix ): array {
		return array(
			$prefix . 'vm_logs',
			$prefix . 'vm_codes',
			$prefix . 'vm_imports',
			$prefix . 'vm_pools',
		);
	}

	/**
	 * @return array<int,string>
	 */
	public static function options(): array {
		return array(
			self::SETTINGS_OPTION,
			self::VERSION_OPTION,
			self::DATABASE_VERSION_OPTION,
		);
	}
}
