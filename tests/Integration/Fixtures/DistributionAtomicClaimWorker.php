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

$wpSettings = rtrim( $wordpressPath, '/\\' ) . '/wp-settings.php';
if ( ! is_file( $wpSettings ) ) {
	throw new RuntimeException( 'WordPress runtime is missing.' );
}

$config = DatabaseConfig::fromEnvironment();

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', rtrim( $wordpressPath, '/\\' ) . '/' );
}

define( 'DB_NAME', $config->database );
define( 'DB_USER', $config->user );
define( 'DB_PASSWORD', $config->password );
define(
	'DB_HOST',
	$config->host . ( 3306 === $config->port ? '' : ':' . $config->port )
);
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'SHORTINIT', true );
define( 'WP_DEBUG', false );

$table_prefix = 'wp_';

require $wpSettings;

if ( ! isset( $wpdb ) || ! $wpdb instanceof wpdb ) {
	throw new RuntimeException( 'WordPress wpdb bootstrap failed.' );
}

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
