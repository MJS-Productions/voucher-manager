<?php
/**
 * Extension pool read API contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Extension pool read API assertion failed: ' . $message );
	}
};

$api        = file_get_contents( $root . '/src/Extension/PoolReadApi.php' );
$pool       = file_get_contents( $root . '/src/Domain/Pool/Pool.php' );
$repository = file_get_contents( $root . '/src/Domain/Pool/PoolRepository.php' );
$composer   = file_get_contents( $root . '/composer.json' );

$assert( is_string( $api ), 'PoolReadApi.php must exist.' );
$assert(
	str_contains( $api, 'final class PoolReadApi' )
	&& str_contains( $api, 'public function all(): array' )
	&& str_contains( $api, 'public function find( int $pool_id ): ?Pool' ),
	'The public API must expose the supported pool read operations.'
);
$assert(
	str_contains( $api, 'new WpdbPoolRepository()' )
	&& str_contains( $api, 'return $this->pools->all();' )
	&& str_contains( $api, 'return $this->pools->find( $pool_id );' ),
	'The API must delegate reads to the existing WordPress pool repository.'
);
$assert(
	! str_contains( $api, 'public function create(' )
	&& ! str_contains( $api, 'public function update(' )
	&& ! str_contains( $api, 'public function set_active(' )
	&& ! str_contains( $api, 'public function delete(' ),
	'The extension pool API must remain read-only.'
);
$assert(
	! str_contains( $api, '$wpdb' ),
	'The extension pool API must not perform direct database access.'
);

$assert(
	is_string( $pool )
	&& str_contains( $pool, 'final class Pool' )
	&& str_contains( $pool, 'private readonly' ),
	'PoolReadApi must return the existing immutable Pool entity.'
);
$assert(
	is_string( $repository )
	&& str_contains( $repository, 'public function create(' )
	&& str_contains( $repository, 'public function delete(' ),
	'Internal pool mutation capabilities must remain behind the repository boundary rather than the extension API.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:extension-pool-read-api": "php tests/Integration/ExtensionPoolReadApiTest.php"' )
	&& str_contains( $composer, '"@test:extension-pool-read-api"' ),
	'The extension pool read API test must be registered in the quality gate.'
);

echo "Extension pool read API contract OK.\n";
