<?php
/**
 * Real-MySQL concurrency test for atomic One-Time Code claims.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use MJSProductions\Quality\Concurrency\ConcurrentProcessRunner;
use MJSProductions\Quality\Database\DatabaseConfig;
use MJSProductions\Quality\Database\PdoConnectionFactory;

$root = dirname( __DIR__, 2 );
require $root . '/vendor/autoload.php';

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Distribution atomic claim concurrency assertion failed: ' . $message );
	}
};

$config = DatabaseConfig::fromEnvironment();
$pdo    = ( new PdoConnectionFactory() )->create( $config );
$table  = 'wp_vm_codes';
$code   = 'Q5-ONLY-ONE-CODE';

$pdo->exec( "DROP TABLE IF EXISTS `{$table}`" );
$pdo->exec(
	"CREATE TABLE `{$table}` (
		`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		`pool_id` BIGINT UNSIGNED NOT NULL,
		`import_id` BIGINT UNSIGNED NULL,
		`code_hash` CHAR(64) NOT NULL,
		`code` TEXT NOT NULL,
		`status` VARCHAR(20) NOT NULL,
		`imported_at` DATETIME NOT NULL,
		`assigned_at` DATETIME NULL,
		PRIMARY KEY (`id`),
		KEY `pool_status_id` (`pool_id`, `status`, `id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$insert = $pdo->prepare(
	"INSERT INTO `{$table}` (`pool_id`, `import_id`, `code_hash`, `code`, `status`, `imported_at`)
	 VALUES (:pool_id, NULL, :code_hash, :code, 'available', UTC_TIMESTAMP())"
);
$insert->execute(
	array(
		'pool_id'   => 1,
		'code_hash' => hash( 'sha256', $code ),
		'code'      => $code,
	)
);

try {
	$worker  = $root . '/tests/Integration/Fixtures/DistributionAtomicClaimWorker.php';
	$runner  = new ConcurrentProcessRunner();
	$results = $runner->run(
		array(
			array( PHP_BINARY, $worker ),
			array( PHP_BINARY, $worker ),
		)
	);

	$assert( 2 === count( $results ), 'Exactly two worker results must be collected.' );

	$payloads = array();
	foreach ( $results as $result ) {
		$assert( 0 === $result->exitCode, 'Every worker process must exit successfully. Stderr: ' . trim( $result->stderr ) );
		$payloads[] = $result->payload;
	}

	$claims = array_values(
		array_filter(
			$payloads,
			static fn ( array $payload ): bool => true === ( $payload['claimed'] ?? false )
		)
	);
	$misses = array_values(
		array_filter(
			$payloads,
			static fn ( array $payload ): bool => false === ( $payload['claimed'] ?? null )
		)
	);

	$assert( 1 === count( $claims ), 'Exactly one concurrent worker must claim the only available code.' );
	$assert( 1 === count( $misses ), 'Exactly one concurrent worker must observe no remaining claim opportunity.' );
	$assert( $code === ( $claims[0]['code'] ?? null ), 'The successful worker must receive the seeded One-Time Code.' );

	$rows = $pdo->query( "SELECT `id`, `code`, `status`, `assigned_at` FROM `{$table}` ORDER BY `id`" )->fetchAll();
	$assert( 1 === count( $rows ), 'The test table must still contain exactly one code row.' );
	$assert( $code === $rows[0]['code'], 'Persistent state must contain the same code returned by the successful worker.' );
	$assert( 'assigned' === $rows[0]['status'], 'The only code must be assigned after the competing claims.' );
	$assert( null !== $rows[0]['assigned_at'], 'The successful claim must persist an assigned timestamp.' );

	$available = (int) $pdo->query( "SELECT COUNT(*) FROM `{$table}` WHERE `status` = 'available'" )->fetchColumn();
	$assigned  = (int) $pdo->query( "SELECT COUNT(*) FROM `{$table}` WHERE `status` = 'assigned'" )->fetchColumn();
	$assert( 0 === $available, 'No available code may remain after the successful claim.' );
	$assert( 1 === $assigned, 'Exactly one persistent assigned code must exist after the race.' );
} finally {
	$pdo->exec( "DROP TABLE IF EXISTS `{$table}`" );
}

echo "Distribution atomic claim concurrency OK.\n";
