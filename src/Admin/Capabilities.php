<?php
/**
 * Voucher Manager capability definitions.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

/**
 * Stable Voucher Manager capability identifiers.
 */
final class Capabilities {

	public const VIEW_DASHBOARD = 'voucher_manager_view_dashboard';
	public const VIEW_INVENTORY = 'voucher_manager_view_inventory';
	public const MANAGE_POOLS = 'voucher_manager_manage_pools';
	public const DELETE_POOLS = 'voucher_manager_delete_pools';
	public const IMPORT_CODES = 'voucher_manager_import_codes';
	public const ROLLBACK_IMPORTS = 'voucher_manager_rollback_imports';
	public const DISTRIBUTE_CODES = 'voucher_manager_distribute_codes';
	public const VIEW_ACTIVITY = 'voucher_manager_view_activity';

	/**
	 * Return all Voucher Manager capabilities.
	 *
	 * @return list<string>
	 */
	public static function all(): array {
		return array(
			self::VIEW_DASHBOARD,
			self::VIEW_INVENTORY,
			self::MANAGE_POOLS,
			self::DELETE_POOLS,
			self::IMPORT_CODES,
			self::ROLLBACK_IMPORTS,
			self::DISTRIBUTE_CODES,
			self::VIEW_ACTIVITY,
		);
	}
}