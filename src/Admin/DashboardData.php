<?php
/**
 * Dashboard data provider.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Database\TableStatus;

final class DashboardData {
	/** @return array<string,mixed> */
	public function get(): array {
		$tables  = new TableStatus();
		$healthy = $tables->is_healthy();
		return array(
			'plugin_version'    => VOUCHER_MANAGER_VERSION,
			'database_version'  => (string) get_option( 'voucher_manager_database_version', '0' ),
			'php_version'       => PHP_VERSION,
			'wordpress_version' => get_bloginfo( 'version' ),
			'database_healthy'  => $healthy,
			'counts'            => array(
				'pools'   => $healthy ? $tables->count( 'pools' ) : 0,
				'imports' => $healthy ? $tables->count( 'imports' ) : 0,
				'codes'   => $healthy ? $tables->count( 'codes' ) : 0,
				'logs'    => $healthy ? $tables->count( 'logs' ) : 0,
			),
		);
	}
}
