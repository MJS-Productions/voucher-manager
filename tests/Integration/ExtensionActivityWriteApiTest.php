<?php
/**
 * Extension Activity write API contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Extension Activity write API assertion failed: ' . $message );
	}
};

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( mixed $value ): string|false {
		return json_encode( $value );
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( string $type, bool $gmt = false ): string {
		unset( $type, $gmt );
		return '2026-09-09 12:00:00';
	}
}

final class ActivityWriteApiTestWpdb {
	public string $prefix = 'wp_';

	/** @var array<int,array<string,mixed>> */
	public array $rows = array();

	public bool $fail = false;

	/**
	 * @param array<string,mixed> $data
	 * @param array<int,string>   $format
	 */
	public function insert( string $table, array $data, array $format ): int|false {
		if ( $this->fail ) {
			return false;
		}

		$this->rows[] = array(
			'table'  => $table,
			'data'   => $data,
			'format' => $format,
		);

		return 1;
	}
}

require_once $root . '/src/Domain/Log/LogRepository.php';
require_once $root . '/src/Domain/Log/LogLevel.php';
require_once $root . '/src/Domain/Log/OperationalEvent.php';
require_once $root . '/src/Domain/Log/OperationalLogger.php';
require_once $root . '/src/Infrastructure/WordPress/WpdbLogRepository.php';
require_once $root . '/src/Extension/ActivityWriteApi.php';

$wpdb = new ActivityWriteApiTestWpdb();

$api = new VoucherManager\Extension\ActivityWriteApi();

$api->info(
	'extension.operation_completed',
	'Extension operation completed.',
	array(
		'pool_id' => 7,
		'email'   => 'private@example.com',
		'nested'  => array( 'not' => 'persisted' ),
		'flag'    => true,
	)
);

$assert( 1 === count( $wpdb->rows ), 'An informational event must be persisted exactly once.' );

$first = $wpdb->rows[0];
$assert( 'wp_vm_logs' === $first['table'], 'The API must use Voucher Manager-owned Activity persistence.' );
$assert(
	'extension.operation_completed' === $first['data']['event_type'],
	'The extension-provided event type must be preserved.'
);
$assert(
	'Extension operation completed.' === $first['data']['message'],
	'The extension-provided user-visible message must be preserved.'
);

$first_context = json_decode( (string) $first['data']['context'], true );
$assert( is_array( $first_context ), 'Persisted Activity context must be valid JSON.' );
$assert( 'info' === ( $first_context['level'] ?? null ), 'Informational events must use the info level.' );
$assert( 7 === ( $first_context['pool_id'] ?? null ), 'Privacy-safe scalar context must be preserved.' );
$assert( true === ( $first_context['flag'] ?? null ), 'Privacy-safe boolean context must be preserved.' );
$assert( ! array_key_exists( 'email', $first_context ), 'Sensitive context keys must be removed.' );
$assert( ! array_key_exists( 'nested', $first_context ), 'Unsupported nested context must not be persisted.' );

$api->warning( 'extension.warning', 'Extension warning.' );
$api->error( 'extension.failed', str_repeat( 'x', 250 ) );

$assert( 3 === count( $wpdb->rows ), 'Info, warning and error methods must each persist one event.' );

$warning_context = json_decode( (string) $wpdb->rows[1]['data']['context'], true );
$error_context   = json_decode( (string) $wpdb->rows[2]['data']['context'], true );

$assert( 'warning' === ( $warning_context['level'] ?? null ), 'Warning events must use the warning level.' );
$assert( 'error' === ( $error_context['level'] ?? null ), 'Error events must use the error level.' );
$assert(
	200 === strlen( (string) $wpdb->rows[2]['data']['message'] ),
	'Activity messages must keep the OperationalLogger length boundary.'
);

$wpdb->fail = true;

try {
	$api->error( 'extension.persistence_failed', 'This write is expected to fail internally.' );
} catch ( Throwable $exception ) {
	throw new RuntimeException(
		'Activity persistence failures must not cascade through the extension API.',
		0,
		$exception
	);
}

$source   = file_get_contents( $root . '/src/Extension/ActivityWriteApi.php' );
$composer = file_get_contents( $root . '/composer.json' );

$assert( is_string( $source ), 'ActivityWriteApi.php must exist.' );
$assert(
	str_contains( $source, 'final class ActivityWriteApi' )
	&& str_contains( $source, 'new OperationalLogger( new WpdbLogRepository() )' ),
	'The public API must delegate to Voucher Manager-owned operational logging infrastructure.'
);
$assert(
	str_contains( $source, 'public function info(' )
	&& str_contains( $source, 'public function warning(' )
	&& str_contains( $source, 'public function error(' ),
	'The public API must expose explicit informational, warning and error Activity writes.'
);
$assert(
	! str_contains( $source, 'global $wpdb' )
	&& ! str_contains( $source, '->insert(' )
	&& ! str_contains( $source, 'vm_logs' ),
	'The extension API must not implement or expose direct Activity database writes.'
);
$assert(
	is_string( $composer )
	&& str_contains(
		$composer,
		'"test:extension-activity-write-api": "php tests/Integration/ExtensionActivityWriteApiTest.php"'
	)
	&& str_contains( $composer, '"@test:extension-activity-write-api"' ),
	'The extension Activity write API test must be registered in the quality gate.'
);

echo "Extension Activity write API contract OK.\n";
