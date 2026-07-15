<?php
/**
 * Settings repository contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Settings;

interface SettingsRepository {

	public function get(): Settings;

	public function save( Settings $settings ): bool;
}
