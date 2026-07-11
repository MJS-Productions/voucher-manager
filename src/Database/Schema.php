<?php
/**
 * Database schema definitions.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Database;

final class Schema {
	/** @return array<string> */
	public function statements(): array {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$pools   = $wpdb->prefix . 'vm_pools';
		$imports = $wpdb->prefix . 'vm_imports';
		$codes   = $wpdb->prefix . 'vm_codes';
		$logs    = $wpdb->prefix . 'vm_logs';

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
			"CREATE TABLE {$imports} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				pool_id bigint(20) unsigned NOT NULL,
				filename varchar(255) NOT NULL,
				file_type varchar(20) NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'processing',
				total_rows int(10) unsigned NOT NULL DEFAULT 0,
				imported_rows int(10) unsigned NOT NULL DEFAULT 0,
				skipped_rows int(10) unsigned NOT NULL DEFAULT 0,
				invalid_rows int(10) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				completed_at datetime NULL,
				PRIMARY KEY  (id),
				KEY pool_created (pool_id,created_at),
				KEY status (status)
			) {$charset_collate};",
			"CREATE TABLE {$codes} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				pool_id bigint(20) unsigned NOT NULL,
				import_id bigint(20) unsigned NULL,
				code_hash char(64) NOT NULL,
				code longtext NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'available',
				imported_at datetime NOT NULL,
				assigned_at datetime NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY pool_code (pool_id,code_hash),
				KEY pool_status (pool_id,status),
				KEY import_id (import_id),
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
