<?php
/**
 * Voucher Manager uninstall handler.
 *
 * Persistent voucher data is preserved by default to prevent accidental loss.
 * A destructive uninstall option will be introduced only with explicit consent.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'voucher_manager_version' );
delete_option( 'voucher_manager_database_version' );
