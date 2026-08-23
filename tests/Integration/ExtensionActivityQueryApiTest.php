<?php
/**
 * Extension Activity query API contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Extension Activity query API assertion failed: ' . $message );
	}
};

$api      = file_get_contents( $root . '/src/Extension/ActivityQueryApi.php' );
$activity = file_get_contents( $root . '/src/Admin/OperationalActivityData.php' );
$composer = file_get_contents( $root . '/composer.json' );

$assert( is_string( $api ), 'ActivityQueryApi.php must exist.' );

$assert(
	str_contains( $api, 'final class ActivityQueryApi' )
	&& str_contains( $api, 'public function query(' )
	&& str_contains( $api, "string \$family = 'all'" )
	&& str_contains( $api, "string \$tone = 'all'" )
	&& str_contains( $api, 'int $before_id = 0' )
	&& str_contains( $api, 'int $limit = 250' ),
	'The public API must expose a cursor-based Activity query operation.'
);

$assert(
	str_contains( $api, 'SELECT id, event_type, message, context, created_at' ),
	'The Activity query contract must expose the complete active Activity business record.'
);

$assert(
	str_contains( $api, "id < %d" )
	&& str_contains( $api, 'ORDER BY id DESC' )
	&& str_contains( $api, '$limit + 1' )
	&& str_contains( $api, "'has_more'" )
	&& str_contains( $api, "'next_before_id'" ),
	'The query contract must support complete traversal independently of admin pagination.'
);

$assert(
	! str_contains( $api, 'INSERT ' )
	&& ! str_contains( $api, 'UPDATE ' )
	&& ! str_contains( $api, 'DELETE ' ),
	'The extension Activity API must remain read-only.'
);

$assert(
	is_string( $activity )
	&& str_contains( $activity, "array( 'all', 'import', 'distribution', 'pool', 'settings', 'admin' )" )
	&& str_contains( $api, "array( 'all', 'import', 'distribution', 'pool', 'settings', 'admin' )" ),
	'The extension API must preserve the current Activity family filter semantics.'
);

foreach (
	array(
		'import.failed',
		'distribution.failed',
		'admin.action_failed',
		'activity.cleanup_failed',
		'pool.delete_failed',
		'import.rollback_blocked',
		'distribution.empty',
		'pool.available_codes_deleted',
		'pool.deleted',
		'import.completed',
		'import.rolled_back',
		'distribution.completed',
		'settings.updated',
		'activity.cleanup_completed',
		'pool.created',
		'pool.updated',
		'pool.activated',
		'pool.deactivated',
	) as $event
) {
	$assert(
		str_contains( $activity, "'" . $event . "'" )
		&& str_contains( $api, "'" . $event . "'" ),
		'The extension API must preserve Activity tone semantics for ' . $event . '.'
	);
}

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:extension-activity-query-api": "php tests/Integration/ExtensionActivityQueryApiTest.php"' )
	&& str_contains( $composer, '"@test:extension-activity-query-api"' ),
	'The extension Activity query API test must be registered in the quality gate.'
);

echo "Extension Activity query API contract OK.\n";
