<?php
/**
 * Framework-free Distribution intent idempotency test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Domain\Distribution\DistributionIntentStore;

$root = dirname( __DIR__, 2 );

spl_autoload_register(
	static function ( string $class ) use ( $root ): void {
		$prefix = 'VoucherManager\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}

		$file = $root . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Distribution intent assertion failed: ' . $message );
	}
};

$memory = new class() implements DistributionIntentStore {
	/** @var array<string,int> */
	private array $tokens = array();
	private int $sequence = 0;

	public function create( int $user_id ): string {
		$token = 'intent-' . ++$this->sequence;
		$this->tokens[ $token ] = $user_id;
		return $token;
	}

	public function consume( string $token, int $user_id ): bool {
		if ( ! isset( $this->tokens[ $token ] ) || $user_id !== $this->tokens[ $token ] ) {
			return false;
		}

		unset( $this->tokens[ $token ] );
		return true;
	}
};

$intent_a = $memory->create( 7 );
$intent_b = $memory->create( 7 );

$assert( $intent_a !== $intent_b, 'Distinct rendered forms must receive distinct Distribution intents.' );
$assert( $memory->consume( $intent_a, 7 ), 'A fresh intent must be consumable by its owner.' );
$assert( ! $memory->consume( $intent_a, 7 ), 'The same intent must not be consumed twice.' );
$assert( $memory->consume( $intent_b, 7 ), 'A distinct intent must remain independently consumable.' );

$foreign = $memory->create( 8 );
$assert( ! $memory->consume( $foreign, 7 ), 'An intent must not be consumable by another administrator.' );
$assert( $memory->consume( $foreign, 8 ), 'Failed foreign consumption must not destroy the owner intent.' );

$store_source    = file_get_contents( $root . '/src/Infrastructure/WordPress/WpDistributionIntentStore.php' );
$admin_source    = file_get_contents( $root . '/src/Admin/DistributionAdmin.php' );
$template_source = file_get_contents( $root . '/templates/admin/distribution.php' );
$composer_source = file_get_contents( $root . '/composer.json' );
$plugin_source   = file_get_contents( $root . '/voucher-manager.php' );

$assert(
	is_string( $store_source )
	&& str_contains( $store_source, "OPTION_PREFIX = 'voucher_manager_distribution_intent_'" )
	&& str_contains( $store_source, 'TTL_SECONDS = 600' )
	&& str_contains( $store_source, 'bin2hex( random_bytes( 32 ) )' )
	&& str_contains( $store_source, "hash( 'sha256', \$token )" )
	&& str_contains( $store_source, 'add_option( $this->option_name( $token ), $value, \'\', false )' ),
	'Production intents must be opaque, short-lived, hashed in option names and non-autoloaded.'
);

$assert(
	str_contains( $store_source, "\$wpdb->delete(" )
	&& str_contains( $store_source, "'option_name'  => \$option_name" )
	&& str_contains( $store_source, "'option_value' => \$value" )
	&& str_contains( $store_source, 'return 1 === $deleted' ),
	'Intent consumption must use an affected-row check so only one replay can succeed.'
);

$assert(
	str_contains( $store_source, '$stored_user_id !== $user_id' )
	&& str_contains( $store_source, '$expires_at < time()' )
	&& str_contains( $store_source, 'CLEANUP_LIMIT = 25' ),
	'Intent consumption must enforce ownership, expiry and bounded stale-option cleanup.'
);

$consume_position = strpos( $admin_source, '$this->intents->consume' );
$service_position = strpos( $admin_source, '$this->service->distribute' );

$assert(
	is_int( $consume_position )
	&& is_int( $service_position )
	&& $consume_position < $service_position
	&& str_contains( $admin_source, 'already been used or expired' ),
	'The controller must consume the intent before any voucher claim and reject replay safely.'
);

$assert(
	is_string( $template_source )
	&& str_contains( $template_source, 'name="distribution_intent"' )
	&& str_contains( $template_source, 'esc_attr( $intent_token )' )
	&& str_contains( $template_source, 'A secure distribution request could not be prepared' ),
	'Every rendered form must carry an escaped intent and fail closed if intent creation fails.'
);

$assert(
	! str_contains( $admin_source, "'distribution_intent' =>" )
	&& ! str_contains( $admin_source, "'code' => \$intent_token" ),
	'Intent tokens must not be written to Activity context or confused with voucher values.'
);

$assert(
	is_string( $composer_source )
	&& str_contains( $composer_source, '@test:distribution-intent-idempotency' )
	&& strpos( $composer_source, '@test:distribution-intent-idempotency' ) < strpos( $composer_source, '@build' ),
	'Distribution intent coverage must run before build.'
);

$assert(
	is_string( $plugin_source )
	&& str_contains( $plugin_source, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Distribution intent idempotency must not introduce a database migration.'
);

echo "Distribution intent idempotency OK: one-use, owner-scoped and replay-safe request intents verified.\n";
