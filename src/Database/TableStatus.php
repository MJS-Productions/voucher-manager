<?php
/**
 * Database table health checks.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Database;

final class TableStatus {
	/** @return array<string,string> */
	public function names(): array {
		global $wpdb;
		return array(
			'pools'   => $wpdb->prefix . 'vm_pools',
			'imports' => $wpdb->prefix . 'vm_imports',
			'codes'   => $wpdb->prefix . 'vm_codes',
			'logs'    => $wpdb->prefix . 'vm_logs',
		);
	}

	public function is_healthy(): bool {
		global $wpdb;
		foreach ( $this->names() as $table ) {
			// Direct schema checks must reflect the current database state and are not cacheable.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
			if ( $table !== $found ) {
				return false;
			}
		}
		return true;
	}

	public function count( string $key ): int {
		global $wpdb;
		$tables = $this->names();
		if ( ! isset( $tables[ $key ] ) ) {
			return 0;
		}

		// Direct table counts are health/status snapshots and must reflect current state.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i',
				$tables[ $key ]
			)
		);
	}
}
