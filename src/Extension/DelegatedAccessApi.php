<?php
/**
 * Delegated Voucher Manager access extension API.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

/**
 * Runtime-only opt-in for compatible extensions that delegate Voucher Manager
 * capabilities to non-administrator WordPress roles.
 */
final class DelegatedAccessApi {

	private static bool $enabled = false;

	public static function enable(): void {
		self::$enabled = true;
	}

	public static function is_enabled(): bool {
		return self::$enabled;
	}

	private function __construct() {}
}
