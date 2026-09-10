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
$metadata = file_get_contents( $root . '/src/Activity/ActivityMetadata.php' );
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
	str_contains( $api, 'id < %d' )
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
	&& is_string( $metadata )
	&& str_contains( $activity, 'use VoucherManager\\Activity\\ActivityMetadata;' )
	&& str_contains( $api, 'use VoucherManager\\Activity\\ActivityMetadata;' )
	&& str_contains( $activity, 'ActivityMetadata::family_filter( $family )' )
	&& str_contains( $api, 'ActivityMetadata::family_filter( $family )' )
	&& str_contains( $activity, 'ActivityMetadata::event_types_for_tone( $tone )' )
	&& str_contains( $api, 'ActivityMetadata::event_types_for_tone( $tone )' ),
	'The active Activity UI and public query API must consume the same extension classification source.'
);

$assert(
	str_contains( $activity, "\$wpdb->esc_like( \$filter['prefix'] ) . '%'" )
	&& str_contains( $api, "\$wpdb->esc_like( \$filter['prefix'] ) . '%'" )
	&& str_contains( $metadata, "'admin.action_failed'" )
	&& str_contains( $metadata, "'prefix'      => 'admin' === \$family ? null : \$family . '.'" ),
	'Existing core family-prefix and administration filter semantics must remain centralized and intact.'
);

$assert(
	! str_contains( $activity, 'private const ERROR_EVENTS' )
	&& ! str_contains( $activity, 'private const WARNING_EVENTS' )
	&& ! str_contains( $api, 'private const ERROR_EVENTS' )
	&& ! str_contains( $api, 'private const WARNING_EVENTS' )
	&& str_contains( $metadata, "'distribution.failed'" )
	&& str_contains( $metadata, "'pool.deleted'" )
	&& str_contains( $metadata, "'distribution.completed'" ),
	'Core outcome classification must no longer be duplicated between the active UI and query API.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:extension-activity-query-api": "php tests/Integration/ExtensionActivityQueryApiTest.php"' )
	&& str_contains( $composer, '"@test:extension-activity-query-api"' ),
	'The extension Activity query API test must remain registered in the quality gate.'
);

echo "Extension Activity query API contract OK.\n";
