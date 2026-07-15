<?php
/**
 * Voucher Manager settings value object.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Settings;

/**
 * Normalized site-level production-hardening settings.
 */
final class Settings {

	public const DEFAULT_ACTIVITY_RETENTION_DAYS = 90;

	/** @var array<int> */
	public const ALLOWED_ACTIVITY_RETENTION_DAYS = array( 0, 30, 90, 180 );

	public function __construct(
		private readonly int $activity_retention_days,
		private readonly bool $delete_data_on_uninstall
	) {
	}

	public static function defaults(): self {
		return new self( self::DEFAULT_ACTIVITY_RETENTION_DAYS, false );
	}

	/**
	 * @param array<string,mixed> $values Raw settings values.
	 */
	public static function from_array( array $values ): self {
		$retention = isset( $values['activity_retention_days'] )
			? (int) $values['activity_retention_days']
			: self::DEFAULT_ACTIVITY_RETENTION_DAYS;

		if ( ! in_array( $retention, self::ALLOWED_ACTIVITY_RETENTION_DAYS, true ) ) {
			$retention = self::DEFAULT_ACTIVITY_RETENTION_DAYS;
		}

		$delete_data = isset( $values['delete_data_on_uninstall'] )
			&& true === filter_var( $values['delete_data_on_uninstall'], FILTER_VALIDATE_BOOLEAN );

		return new self( $retention, $delete_data );
	}

	public function activity_retention_days(): int {
		return $this->activity_retention_days;
	}

	public function delete_data_on_uninstall(): bool {
		return $this->delete_data_on_uninstall;
	}

	/**
	 * @return array{activity_retention_days:int,delete_data_on_uninstall:bool}
	 */
	public function to_array(): array {
		return array(
			'activity_retention_days'  => $this->activity_retention_days,
			'delete_data_on_uninstall' => $this->delete_data_on_uninstall,
		);
	}
}
