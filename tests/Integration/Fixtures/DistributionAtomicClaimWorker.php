<?php
/**
 * Concurrent worker for the real-MySQL atomic distribution claim test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use MJSProductions\Quality\Concurrency\WorkerBarrier;
use MJSProductions\Quality\Database\DatabaseConfig;
use VoucherManager\Infrastructure\WordPress\WpdbCodeRepository;

$root = dirname( __DIR__, 3 );
require $root . '/vendor/autoload.php';

$wordpressPath = getenv( 'MJS_QUALITY_WORDPRESS_PATH' );
if ( false === $wordpressPath || '' === $wordpressPath ) {
	throw new RuntimeException( 'MJS_QUALITY_WORDPRESS_PATH is required.' );
}

$wpdbFile = rtrim( $wordpressPath, '/\\' ) . '/wp-includes/class-wpdb.php';
if ( ! is_file( $wpdbFile ) ) {
	throw new RuntimeException( 'WordPress wpdb runtime is missing.' );
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}
if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
	define( 'WP_DEBUG_DISPLAY', false );
}

require_once $wpdbFile;

if ( ! function_exists( 'current_time' ) ) {
	function current_time( string $type, bool $gmt = false ): string { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.typeFound
		if ( 'mysql' !== $type ) {
			throw new RuntimeException( 'The Q5 worker supports only current_time("mysql").' );
		}

		return gmdate( 'Y-m-d H:i:s' );
	}
}

$config = DatabaseConfig::fromEnvironment();
$dbhost = $config->host . ( 3306 === $config->port ? '' : ':' . $config->port );

global $wpdb;
$wpdb = new wpdb(
	$config->user,
	$config->password,
	$config->database,
	$dbhost
);
$wpdb->prefix = 'wp_';

$barrier = WorkerBarrier::fromEnvironment();
$barrier->waitForRelease();

$claimed = ( new WpdbCodeRepository() )->claim_next_available( 1 );
$barrier->writeResult(
	array(
		'claimed' => null !== $claimed,
		'id'      => $claimed['id'] ?? null,
		'code'    => $claimed['code'] ?? null,
	)
);
