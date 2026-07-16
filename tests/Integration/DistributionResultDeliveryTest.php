<?php
/**
 * Framework-free Distribution result delivery hardening test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Domain\Distribution\DistributionResult;
use VoucherManager\Domain\Distribution\DistributionResultStore;

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
		throw new RuntimeException( 'Distribution result delivery assertion failed: ' . $message );
	}
};

$memory = new class() implements DistributionResultStore {
	private int $sequence = 0;
	/** @var array<string,array{intent:string,user:int,result:array{success:bool,code:?string,message:string,remaining:?int,pool_id:int}}> */
	private array $results = array();
	/** @var array<string,array{user:int,result:array{success:bool,code:?string,message:string,remaining:?int,pool_id:int}}> */
	private array $intent_map = array();

	public function store( string $intent_token, int $user_id, DistributionResult $result, int $pool_id ): ?string {
		$token = 'result-' . ++$this->sequence;
		$this->results[ $token ] = array(
			'intent' => $intent_token,
			'user'   => $user_id,
			'result' => array(
				'success'   => $result->success(),
				'code'      => $result->code(),
				'message'   => $result->message(),
				'remaining' => $result->remaining(),
				'pool_id'   => $pool_id,
			),
		);
		$this->intent_map[ $intent_token ] = array(
			'user'   => $user_id,
			'result' => $this->results[ $token ]['result'],
		);
		return $token;
	}

	public function consume( string $result_token, int $user_id ): ?array {
		if ( ! isset( $this->results[ $result_token ] ) || $user_id !== $this->results[ $result_token ]['user'] ) {
			return null;
		}
		$result = $this->results[ $result_token ]['result'];
		unset( $this->results[ $result_token ] );
		return $result;
	}

	public function create_delivery_for_intent( string $intent_token, int $user_id ): ?string {
		if ( ! isset( $this->intent_map[ $intent_token ] ) || $user_id !== $this->intent_map[ $intent_token ]['user'] ) {
			return null;
		}

		$token = 'result-' . ++$this->sequence;
		$this->results[ $token ] = array(
			'intent' => $intent_token,
			'user'   => $user_id,
			'result' => $this->intent_map[ $intent_token ]['result'],
		);
		return $token;
	}
};

$first = $memory->store( str_repeat( 'a', 64 ), 7, new DistributionResult( true, 'CODE-A', 'ok', 4 ), 11 );
$second = $memory->store( str_repeat( 'b', 64 ), 7, new DistributionResult( true, 'CODE-B', 'ok', 3 ), 11 );

$assert( is_string( $first ) && is_string( $second ) && $first !== $second, 'Two successful requests must receive unique result tokens.' );
$replay_delivery = $memory->create_delivery_for_intent( str_repeat( 'a', 64 ), 7 );
$assert( is_string( $replay_delivery ) && $replay_delivery !== $first, 'Replay recovery must create an independent delivery token.' );
$assert( null === $memory->create_delivery_for_intent( str_repeat( 'a', 64 ), 8 ), 'Intent-result mappings must be owner-scoped.' );

$result_a = $memory->consume( $first, 7 );
$assert( is_array( $result_a ) && 'CODE-A' === $result_a['code'], 'The original request must receive the correct one-time voucher result.' );
$assert( null === $memory->consume( $first, 7 ), 'The original result token must be consumable only once.' );

$replay_result = $memory->consume( $replay_delivery, 7 );
$assert( is_array( $replay_result ) && 'CODE-A' === $replay_result['code'], 'A racing replay must receive its own delivery of the same claimed voucher.' );
$assert( null === $memory->consume( $replay_delivery, 7 ), 'The replay delivery token must also be consumable only once.' );

$result_b = $memory->consume( $second, 7 );
$assert( is_array( $result_b ) && 'CODE-B' === $result_b['code'], 'A second tab must retain its own independent voucher result.' );

$store_source    = file_get_contents( $root . '/src/Infrastructure/WordPress/WpDistributionResultStore.php' );
$admin_source    = file_get_contents( $root . '/src/Admin/DistributionAdmin.php' );
$template_source = file_get_contents( $root . '/templates/admin/distribution.php' );
$direct_template = file_get_contents( $root . '/templates/admin/distribution-direct-result.php' );
$composer_source = file_get_contents( $root . '/composer.json' );
$plugin_source   = file_get_contents( $root . '/voucher-manager.php' );

$assert(
	is_string( $store_source )
	&& str_contains( $store_source, "RESULT_PREFIX = 'voucher_manager_distribution_result_'" )
	&& str_contains( $store_source, "INTENT_MAP_PREFIX = 'voucher_manager_distribution_result_intent_'" )
	&& str_contains( $store_source, 'bin2hex( random_bytes( 32 ) )' )
	&& str_contains( $store_source, 'TTL_SECONDS = 60' )
	&& str_contains( $store_source, 'add_option( $this->result_option( $result_token ), $payload, \'\', false )' ),
	'Production results must be unique, opaque, short-lived and non-autoloaded.'
);

$assert(
	str_contains( $store_source, '$this->payload_belongs_to_user' )
	&& str_contains( $store_source, 'return 1 === $deleted' ) === false
	&& str_contains( $store_source, 'if ( 1 !== $deleted )' ),
	'Result consumption must enforce ownership and an affected-row consume-once boundary.'
);

$assert(
	is_string( $admin_source )
	&& str_contains( $admin_source, 'wait_for_replay_delivery' )
	&& str_contains( $admin_source, 'create_delivery_for_intent' )
	&& str_contains( $admin_source, 'redirect_to_result' )
	&& str_contains( $admin_source, 'render_direct_result' )
	&& ! str_contains( $admin_source, 'set_transient' ),
	'Replay must receive an independent successful delivery and shared per-user transient delivery must be removed.'
);

$store_position = strpos( $admin_source, '$result_token = $this->results->store' );
$null_check_position = strpos( $admin_source, 'if ( null === $result_token )', $store_position );
$success_redirect_position = strpos( $admin_source, '$this->redirect_to_result( $result_token )', $null_check_position );

$assert(
	is_int( $store_position )
	&& is_int( $null_check_position )
	&& is_int( $success_redirect_position )
	&& $store_position < $null_check_position
	&& $null_check_position < $success_redirect_position,
	'Result persistence must be checked and given a direct fallback before the success redirect.'
);

$assert(
	is_string( $template_source )
	&& ! str_contains( $template_source, 'get_transient' )
	&& str_contains( $template_source, 'This distribution request was already used or expired. No additional One-Time Code was distributed.' ),
	'Normal presentation must no longer consume a shared transient and must explain unrecoverable replay safely.'
);

$assert(
	is_string( $direct_template )
	&& str_contains( $direct_template, 'normal one-time result could not be stored' )
	&& str_contains( $direct_template, 'vm-distributed-code' )
	&& ! str_contains( $direct_template, 'result=' ),
	'Storage failure must directly present the claimed voucher without placing it in a URL.'
);

$assert(
	! str_contains( $admin_source, "'code' => \$result_token" )
	&& str_contains( $admin_source, "'result' => \$result_token" ),
	'Redirect URLs may contain only opaque result tokens, never One-Time Code values.'
);

$assert(
	is_string( $composer_source )
	&& str_contains( $composer_source, '@test:distribution-result-delivery' )
	&& strpos( $composer_source, '@test:distribution-result-delivery' ) < strpos( $composer_source, '@build' ),
	'Distribution result delivery coverage must run before build.'
);

$assert(
	is_string( $plugin_source )
	&& str_contains( $plugin_source, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Distribution result delivery hardening must not introduce a database migration.'
);

echo "Distribution result delivery OK: unique tokens, replay recovery, multi-tab isolation and direct fallback verified.\n";
