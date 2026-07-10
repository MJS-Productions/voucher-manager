<?php
/**
 * Database table health checks.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Database;

/**
 * Provides database table names, health information, and row counts.
 */
final class TableStatus {

	/**
	 * Return plugin table names without exposing SQL construction elsewhere.
	 *
	 * @return array<string,string>
	 */
	public function names(): array {
		global $wpdb;

		return array(
			'pools' => $wpdb->prefix . 'vm_pools',
			'codes' => $wpdb->prefix . 'vm_codes',
			'logs'  => $wpdb->prefix . 'vm_logs',
		);
	}

	/**
	 * Determine whether every required table exists.
	 */
	public function is_healthy(): bool {
		global $wpdb;

		foreach ( $this->names() as $table ) {
			$found = $wpdb->get_var(
				$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) )
			);

			if ( $table !== $found ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Count rows in a known table.
	 *
	 * @param string $key Table key: pools, codes, or logs.
	 */
	public function count( string $key ): int {
		global $wpdb;

		$tables = $this->names();

		if ( ! isset( $tables[ $key ] ) ) {
			return 0;
		}

		// Table names are selected only from the internal allow-list above.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tables[$key]}" );
	}
}
