<?php
/**
 * WordPress settings repository.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Infrastructure\WordPress;

use VoucherManager\Domain\Settings\Settings;
use VoucherManager\Domain\Settings\SettingsRepository;

/**
 * Persists all user-facing settings in one owned WordPress option.
 */
final class WpSettingsRepository implements SettingsRepository {

	public const OPTION_NAME = 'voucher_manager_settings';

	public function get(): Settings {
		$value = get_option( self::OPTION_NAME, array() );

		return Settings::from_array( is_array( $value ) ? $value : array() );
	}

	public function save( Settings $settings ): bool {
		return update_option( self::OPTION_NAME, $settings->to_array(), false );
	}
}
