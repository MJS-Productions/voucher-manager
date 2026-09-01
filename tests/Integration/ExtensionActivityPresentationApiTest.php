<?php
/**
 * Extension Activity presentation API contract test.
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
		throw new RuntimeException( 'Extension Activity presentation API assertion failed: ' . $message );
	}
};

$api      = new VoucherManager\Extension\ActivityPresentationApi();
$source   = file_get_contents( $root . '/src/Extension/ActivityPresentationApi.php' );
$composer = file_get_contents( $root . '/composer.json' );

$assert(
	'Import completed' === $api->label( 'import.completed' ),
	'Voucher Manager-owned events must use the existing human-readable presentation.'
);

$assert(
	'Deleted 4 available One-Time Codes' === $api->label(
		'pool.available_codes_deleted',
		array( 'deleted_available_count' => 4 )
	),
	'Context-aware Voucher Manager labels must remain available through the supported API.'
);

$assert(
	'extension.future_event' === $api->label( 'extension.future_event' ),
	'Unknown extension events must preserve their technical identifier.'
);

$assert(
	is_string( $source )
	&& str_contains( $source, 'use VoucherManager\Admin\DashboardViewModel;' )
	&& str_contains( $source, 'return $this->events->activity_label( $event_type, $context );' ),
	'The supported API must reuse the central Voucher Manager Activity presentation instead of duplicating labels.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:extension-activity-presentation-api": "php tests/Integration/ExtensionActivityPresentationApiTest.php"' )
	&& str_contains( $composer, '"@test:extension-activity-presentation-api"' ),
	'The extension Activity presentation API test must be registered in the quality gate.'
);

echo "Extension Activity presentation API contract OK.\n";
