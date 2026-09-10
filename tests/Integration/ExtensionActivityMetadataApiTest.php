<?php
/**
 * Extension Activity metadata API contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n(
		string $single,
		string $plural,
		int $number,
		string $domain = 'default'
	): string {
		unset( $domain );
		return 1 === $number ? $single : $plural;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( mixed $value ): int {
		return abs( (int) $value );
	}
}

$root = dirname( __DIR__, 2 );

spl_autoload_register(
	static function ( string $class ) use ( $root ): void {
		$prefix = 'VoucherManager\\';

		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$file     = $root . '/src/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Extension Activity metadata API assertion failed: ' . $message );
	}
};

$api          = new VoucherManager\Extension\ActivityMetadataApi();
$presentation = new VoucherManager\Extension\ActivityPresentationApi();
$view         = new VoucherManager\Admin\DashboardViewModel();
$source       = file_get_contents( $root . '/src/Extension/ActivityMetadataApi.php' );
$metadata     = file_get_contents( $root . '/src/Activity/ActivityMetadata.php' );
$composer     = file_get_contents( $root . '/composer.json' );

$assert(
	$api->register(
		'voucher_manager_pro.frontend_offer.failed',
		'Public Distribution failed',
		'distribution',
		'error',
		static fn ( array $context ): string =>
			isset( $context['offer_id'] ) ? 'Public Distribution #' . absint( $context['offer_id'] ) : ''
	),
	'A valid extension-owned event must be registerable through the supported API.'
);

$assert(
	! $api->register( 'pool.created', 'Overridden Pool created', 'pool', 'error' ),
	'Extensions must not be able to override Voucher Manager-owned Activity events.'
);

$assert(
	! $api->register( 'extension.invalid_family', 'Invalid family', 'future', 'warning' )
	&& ! $api->register( 'extension.invalid_tone', 'Invalid tone', 'admin', 'critical' )
	&& ! $api->register( 'invalid event', 'Invalid event', 'admin', 'warning' ),
	'Invalid metadata must be rejected instead of weakening the shared Activity contract.'
);

$context = array( 'offer_id' => 42 );

$assert(
	'Public Distribution failed' === $view->activity_label( 'voucher_manager_pro.frontend_offer.failed', $context )
	&& 'Public Distribution #42' === $view->activity_detail( 'voucher_manager_pro.frontend_offer.failed', $context )
	&& 'error' === $view->activity_tone( 'voucher_manager_pro.frontend_offer.failed' ),
	'The Voucher Manager-owned Dashboard presentation must resolve registered extension metadata.'
);

$assert(
	'Public Distribution failed' === $presentation->label( 'voucher_manager_pro.frontend_offer.failed', $context )
	&& 'Public Distribution #42' === $presentation->detail( 'voucher_manager_pro.frontend_offer.failed', $context ),
	'The existing presentation API must expose the same registered extension presentation.'
);

$assert(
	in_array(
		'voucher_manager_pro.frontend_offer.failed',
		VoucherManager\Activity\ActivityMetadata::extension_event_types_for_family( 'distribution' ),
		true
	)
	&& in_array(
		'voucher_manager_pro.frontend_offer.failed',
		VoucherManager\Activity\ActivityMetadata::event_types_for_tone( 'error' ),
		true
	)
	&& in_array(
		'distribution.failed',
		VoucherManager\Activity\ActivityMetadata::event_types_for_tone( 'error' ),
		true
	),
	'Family and outcome classification must share the same metadata source for core and extension events.'
);

$assert(
	'extension.unregistered' === $view->activity_label( 'extension.unregistered' )
	&& '' === $view->activity_detail( 'extension.unregistered', array() )
	&& 'neutral' === $view->activity_tone( 'extension.unregistered' ),
	'Unregistered extension events must retain the transparent safe fallback.'
);

$assert(
	is_string( $source )
	&& str_contains( $source, 'use VoucherManager\\Activity\\ActivityMetadata;' )
	&& str_contains( $source, 'ActivityMetadata::register_extension(' )
	&& ! str_contains( $source, 'Wpdb' )
	&& ! str_contains( $source, '$wpdb' ),
	'The public metadata API must delegate to the shared Activity contract without exposing database internals.'
);

$assert(
	is_string( $metadata )
	&& str_contains( $metadata, 'private const CORE_EVENTS' )
	&& str_contains( $metadata, 'extension_event_types_for_family' )
	&& str_contains( $metadata, 'event_types_for_tone' ),
	'Core and extension Activity classification must be centralized in one metadata source.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:extension-activity-metadata-api": "php tests/Integration/ExtensionActivityMetadataApiTest.php"' )
	&& str_contains( $composer, '"@test:extension-activity-metadata-api"' ),
	'The extension Activity metadata contract test must be registered in the quality gate.'
);

echo "Extension Activity metadata API contract OK.\n";
