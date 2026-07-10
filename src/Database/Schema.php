<?php
/**
 * Database schema definitions.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Database;

/**
 * Produces WordPress-compatible SQL statements for dbDelta().
 */
final class Schema {

	/**
	 * Return all table creation statements.
	 *
	 * @return array<string>
	 */
	public function statements(): array {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$pools            = $wpdb->prefix . 'vm_pools';
		$codes            = $wpdb->prefix . 'vm_codes';
		$logs             = $wpdb->prefix . 'vm_logs';

		return array(
			"CREATE TABLE {$pools} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(190) NOT NULL,
				slug varchar(190) NOT NULL,
				description text NULL,
				warning_threshold int(10) unsigned NOT NULL DEFAULT 10,
				status varchar(20) NOT NULL DEFAULT 'active',
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY slug (slug),
				KEY status (status)
			) {$charset_collate};",
			"CREATE TABLE {$codes} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				pool_id bigint(20) unsigned NOT NULL,
				code_hash char(64) NOT NULL,
				code longtext NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'available',
				imported_at datetime NOT NULL,
				assigned_at datetime NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY pool_code (pool_id,code_hash),
				KEY pool_status (pool_id,status),
				KEY assigned_at (assigned_at)
			) {$charset_collate};",
			"CREATE TABLE {$logs} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				event_type varchar(100) NOT NULL,
				message text NOT NULL,
				context longtext NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY event_type (event_type),
				KEY created_at (created_at)
			) {$charset_collate};",
		);
	}
}
