<?php
/**
 * Extension distribution API contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Extension distribution API assertion failed: ' . $message );
	}
};

$api      = file_get_contents( $root . '/src/Extension/DistributionApi.php' );
$admin    = file_get_contents( $root . '/src/Admin/DistributionAdmin.php' );
$service  = file_get_contents( $root . '/src/Domain/Distribution/DistributionService.php' );
$composer = file_get_contents( $root . '/composer.json' );

$assert( is_string( $api ), 'DistributionApi.php must exist.' );
$assert(
	str_contains( $api, 'final class DistributionApi' )
	&& str_contains( $api, 'public function distribute( int $pool_id ): DistributionResult' ),
	'The public API must expose exactly the supported typed distribution entry point.'
);
$assert(
	str_contains( $api, 'new DistributionService(' )
	&& str_contains( $api, 'new WpdbPoolRepository()' )
	&& str_contains( $api, 'new WpdbCodeRepository()' )
	&& str_contains( $api, 'new OperationalLogger( new WpdbLogRepository() )' ),
	'The API must wire the existing Voucher Manager distribution domain to the WordPress repositories.'
);
$assert(
	str_contains( $api, 'return $this->service->distribute( $pool_id );' ),
	'The API must delegate distribution to the existing DistributionService.'
);
$assert(
	! str_contains( $api, 'claim_next_available' )
	&& ! str_contains( $api, '$wpdb' ),
	'The extension API must not duplicate atomic claim logic or perform direct database access.'
);

$assert( is_string( $service ) && str_contains( $service, 'claim_next_available( $pool_id )' ), 'Atomic claim ownership must remain in DistributionService.' );

$assert(
	is_string( $admin )
	&& str_contains( $admin, 'use VoucherManager\Extension\DistributionApi;' )
	&& str_contains( $admin, 'private DistributionApi $service;' )
	&& str_contains( $admin, '$this->service  = new DistributionApi();' ),
	'Manual administration must use the same supported DistributionApi entry point.'
);
$assert(
	! str_contains( $admin, 'new DistributionService(' )
	&& ! str_contains( $admin, 'new WpdbCodeRepository()' ),
	'DistributionAdmin must no longer assemble the distribution engine directly.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:extension-distribution-api": "php tests/Integration/ExtensionDistributionApiTest.php"' )
	&& str_contains( $composer, '"@test:extension-distribution-api"' ),
	'The extension distribution API test must be registered in the quality gate.'
);

echo "Extension distribution API contract OK.\n";
