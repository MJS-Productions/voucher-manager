<?php
/**
 * Pool deleted event contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Pool deleted event assertion failed: ' . $message );
	}
};

$GLOBALS['voucher_manager_pool_deleted_test_calls'] = array();
$GLOBALS['voucher_manager_pool_deleted_test_throw'] = false;

if ( ! function_exists( 'do_action' ) ) {
	function do_action( string $hook_name, mixed ...$args ): void {
		$GLOBALS['voucher_manager_pool_deleted_test_calls'][] = array(
			'hook' => $hook_name,
			'args' => $args,
		);

		if ( $GLOBALS['voucher_manager_pool_deleted_test_throw'] ) {
			throw new RuntimeException( 'Injected extension listener failure.' );
		}
	}
}

require_once $root . '/src/Extension/PoolDeletedEvent.php';

use VoucherManager\Extension\PoolDeletedEvent;

PoolDeletedEvent::dispatch( 7 );

$assert(
	1 === count( $GLOBALS['voucher_manager_pool_deleted_test_calls'] ),
	'A valid pool deletion must publish exactly one event.'
);
$assert(
	PoolDeletedEvent::HOOK === $GLOBALS['voucher_manager_pool_deleted_test_calls'][0]['hook'],
	'The event must publish through the stable pool-deleted hook.'
);
$assert(
	array( 7 ) === $GLOBALS['voucher_manager_pool_deleted_test_calls'][0]['args'],
	'The event payload must identify the deleted pool by pool_id only.'
);

PoolDeletedEvent::dispatch( 0 );
$assert(
	1 === count( $GLOBALS['voucher_manager_pool_deleted_test_calls'] ),
	'Invalid pool IDs must not publish an event.'
);

$GLOBALS['voucher_manager_pool_deleted_test_throw'] = true;
PoolDeletedEvent::dispatch( 8 );
$assert(
	2 === count( $GLOBALS['voucher_manager_pool_deleted_test_calls'] ),
	'A throwing extension listener must not escape dispatch or suppress the completed operation.'
);

$event      = file_get_contents( $root . '/src/Extension/PoolDeletedEvent.php' );
$pool_admin = file_get_contents( $root . '/src/Admin/PoolAdmin.php' );
$inventory  = file_get_contents( $root . '/src/Extension/InventoryChangedEvent.php' );
$composer   = file_get_contents( $root . '/composer.json' );

$assert( is_string( $event ), 'PoolDeletedEvent.php must exist.' );
$assert(
	str_contains( $event, "public const HOOK = 'voucher_manager_pool_deleted';" )
	&& str_contains( $event, 'do_action( self::HOOK, $pool_id );' )
	&& str_contains( $event, 'catch ( \Throwable $exception )' ),
	'The public pool-deleted event must expose a stable WordPress hook and remain failure-safe.'
);
$assert(
	! str_contains( $event, '$pool_name' )
	&& ! str_contains( $event, '$deleted_code_count' )
	&& ! str_contains( $event, '$deleted_import_count' ),
	'The event must identify the deleted pool without duplicating deleted Free-owned data.'
);

$delete_call = '$this->lifecycle->delete_pool( $id, $pool->name() );';
$dispatch    = 'PoolDeletedEvent::dispatch( $id );';
$redirect    = '$this->redirect( \'deleted\' );';
$catch       = '} catch ( Throwable ) {';

$assert(
	is_string( $pool_admin )
	&& str_contains( $pool_admin, 'use VoucherManager\Extension\PoolDeletedEvent;' )
	&& 1 === substr_count( $pool_admin, $dispatch ),
	'Permanent Pool deletion must publish exactly one semantic pool-deleted event.'
);
$assert(
	strpos( $pool_admin, $delete_call ) < strpos( $pool_admin, $dispatch )
	&& strpos( $pool_admin, $dispatch ) < strpos( $pool_admin, $redirect ),
	'Pool-deleted dispatch must occur only after successful permanent deletion and before the success redirect.'
);
$dispatch_position = strpos( $pool_admin, $dispatch );
$catch_position    = false !== $dispatch_position ? strpos( $pool_admin, $catch, $dispatch_position ) : false;

$assert(
	false !== $dispatch_position
	&& false !== $catch_position
	&& $dispatch_position < $catch_position,
	'The failure path must not publish a pool-deleted event.'
);

$assert(
	is_string( $inventory )
	&& str_contains( $inventory, "public const REASON_DELETION     = 'deletion';" )
	&& ! str_contains( $inventory, 'voucher_manager_pool_deleted' ),
	'Existing inventory-change deletion semantics must remain separate and unchanged.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:pool-deleted-event": "php tests/Integration/PoolDeletedEventTest.php"' )
	&& str_contains( $composer, '"@test:pool-deleted-event"' ),
	'The pool-deleted event regression test must be registered in the quality gate.'
);

echo "Pool deleted event contract OK.\n";
