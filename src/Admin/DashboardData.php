<?php
/**
 * Dashboard data provider.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Database\TableStatus;
use VoucherManager\Domain\Code\CodeStatus;

/**
 * Loads the operational snapshot shown on the administration dashboard.
 */
final class DashboardData {

	/**
	 * Return the current dashboard snapshot.
	 *
	 * @return array<string,mixed>
	 */
	public function get(): array {
		global $wpdb;

		$tables  = new TableStatus();
		$healthy = $tables->is_healthy();
		$names   = $tables->names();

		$counts = array(
			'pools'     => 0,
			'imports'   => 0,
			'codes'     => 0,
			'available' => 0,
			'assigned'  => 0,
			'logs'      => 0,
		);

		$activity = array();

		if ( $healthy ) {
			$counts['pools']   = $tables->count( 'pools' );
			$counts['imports'] = $tables->count( 'imports' );
			$counts['codes']   = $tables->count( 'codes' );
			$counts['logs']    = $tables->count( 'logs' );

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$counts['available'] = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$names['codes']} WHERE status = %s",
					CodeStatus::AVAILABLE->value
				)
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$counts['assigned'] = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$names['codes']} WHERE status = %s",
					CodeStatus::ASSIGNED->value
				)
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				"SELECT event_type, message, context, created_at
				FROM {$names['logs']}
				ORDER BY id DESC
				LIMIT 5",
				ARRAY_A
			);

			foreach ( is_array( $rows ) ? $rows : array() as $row ) {
				$context = json_decode( (string) ( $row['context'] ?? '' ), true );

				$activity[] = array(
					'event_type' => sanitize_text_field( (string) ( $row['event_type'] ?? '' ) ),
					'message'    => sanitize_text_field( (string) ( $row['message'] ?? '' ) ),
					'context'    => is_array( $context ) ? $context : array(),
					'created_at' => (string) ( $row['created_at'] ?? '' ),
				);
			}
		}

		return array(
			'plugin_version'    => VOUCHER_MANAGER_VERSION,
			'database_version'  => (string) get_option( 'voucher_manager_database_version', '0' ),
			'php_version'       => PHP_VERSION,
			'wordpress_version' => get_bloginfo( 'version' ),
			'database_healthy'  => $healthy,
			'counts'            => $counts,
			'activity'          => $activity,
		);
	}
}
