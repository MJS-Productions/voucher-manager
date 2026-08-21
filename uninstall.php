<?php
/**
 * Voucher Manager uninstall handler.
 *
 * Business data is preserved unless the administrator explicitly enabled
 * destructive uninstall in Voucher Manager Settings.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once __DIR__ . '/src/Lifecycle/UninstallDataBoundary.php';
require_once __DIR__ . '/src/Domain/Log/LogRepository.php';
require_once __DIR__ . '/src/Domain/Log/LogLevel.php';
require_once __DIR__ . '/src/Domain/Log/OperationalEvent.php';
require_once __DIR__ . '/src/Domain/Log/OperationalLogger.php';
require_once __DIR__ . '/src/Infrastructure/WordPress/WpdbLogRepository.php';

use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;
use VoucherManager\Lifecycle\UninstallDataBoundary;

$raw_settings = get_option( UninstallDataBoundary::SETTINGS_OPTION, array() );
$delete_data  = is_array( $raw_settings )
	&& isset( $raw_settings['delete_data_on_uninstall'] )
	&& true === filter_var( $raw_settings['delete_data_on_uninstall'], FILTER_VALIDATE_BOOLEAN );

wp_clear_scheduled_hook( UninstallDataBoundary::ACTIVITY_CRON_HOOK );

global $wpdb;

$runtime_prefixes = array(
	UninstallDataBoundary::DISTRIBUTION_INTENT_OPTION_PREFIX,
	UninstallDataBoundary::DISTRIBUTION_RESULT_OPTION_PREFIX,
	UninstallDataBoundary::DISTRIBUTION_RESULT_INTENT_OPTION_PREFIX,
);

foreach ( $runtime_prefixes as $runtime_prefix ) {
	$runtime_like = $wpdb->esc_like( $runtime_prefix ) . '%';
	// Runtime-only Distribution state never survives uninstall.
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE option_name LIKE %s', $wpdb->options, $runtime_like ) );
}

if ( $delete_data ) {
	foreach ( UninstallDataBoundary::tables( $wpdb->prefix ) as $table ) {
		// Table names are generated only from the active site's trusted prefix
		// and the fixed Voucher Manager ownership allowlist above.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );
	}

	foreach ( UninstallDataBoundary::options() as $option ) {
		delete_option( $option );
	}

	return;
}

( new OperationalLogger( new WpdbLogRepository() ) )->info(
	OperationalEvent::PLUGIN_UNINSTALLED,
	'Voucher Manager was uninstalled with data retained.'
);

// Preserve Pools, Imports, Codes, Activity and user Settings by default.
// Runtime identity options are safe to recreate on a later activation.
delete_option( UninstallDataBoundary::VERSION_OPTION );
delete_option( UninstallDataBoundary::DATABASE_VERSION_OPTION );
