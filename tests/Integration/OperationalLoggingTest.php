<?php
/**
 * Framework-free operational logging test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Domain\Log\LogRepository;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Support\ErrorBoundary;

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
		throw new RuntimeException( 'Operational logging assertion failed: ' . $message );
	}
};

$repository = new class() implements LogRepository {
	/** @var array<int,array{event:string,message:string,context:array<string,mixed>}> */
	public array $events = array();

	public function add(
		string $event_type,
		string $message,
		array $context = array()
	): void {
		$this->events[] = array(
			'event'   => $event_type,
			'message' => $message,
			'context' => $context,
		);
	}
};

$logger = new OperationalLogger( $repository );
$logger->info(
	OperationalEvent::DISTRIBUTION_COMPLETED,
	'Code distributed.',
	array(
		'pool_id'       => 12,
		'code_id'       => 99,
		'remaining'     => 8,
		'source'        => 'manual',
		'code'          => 'SECRET-CODE',
		'email'         => 'person@example.com',
		'ip_address'    => '192.0.2.1',
		'nested_object' => array( 'not' => 'persisted' ),
	)
);

$event = $repository->events[0] ?? null;
$assert( is_array( $event ), 'An event should be persisted.' );
$assert(
	OperationalEvent::DISTRIBUTION_COMPLETED->value === $event['event'],
	'The stable event name should be persisted.'
);
$assert( 'info' === $event['context']['level'], 'The severity should be included.' );
$assert( 12 === $event['context']['pool_id'], 'Pool ID should be retained.' );
$assert( 99 === $event['context']['code_id'], 'Internal code ID should be retained.' );
$assert( ! isset( $event['context']['code'] ), 'Code values must be removed.' );
$assert( ! isset( $event['context']['email'] ), 'Email addresses must be removed.' );
$assert( ! isset( $event['context']['ip_address'] ), 'IP addresses must be removed.' );
$assert( ! isset( $event['context']['nested_object'] ), 'Complex context must be removed.' );

$boundary = new ErrorBoundary( $logger );
$fallback = $boundary->execute(
	static function (): string {
		throw new RuntimeException( 'Sensitive runtime detail.' );
	},
	'safe-fallback',
	array(
		'action' => 'test.failure',
		'code'   => 'MUST-NOT-LOG',
	)
);

$assert( 'safe-fallback' === $fallback, 'The boundary should return its safe fallback.' );

$failure = $repository->events[1] ?? null;
$assert( is_array( $failure ), 'The boundary should log an operational error.' );
$assert(
	OperationalEvent::ADMIN_ACTION_FAILED->value === $failure['event'],
	'The boundary should use the stable admin failure event.'
);
$assert(
	RuntimeException::class === $failure['context']['exception_class'],
	'The exception class should be retained for diagnostics.'
);
$assert(
	! str_contains( json_encode( $failure ), 'Sensitive runtime detail' ),
	'Exception messages must not be persisted.'
);
$assert(
	! str_contains( json_encode( $failure ), 'MUST-NOT-LOG' ),
	'Sensitive context must not be persisted.'
);

$failing_repository = new class() implements LogRepository {
	public function add(
		string $event_type,
		string $message,
		array $context = array()
	): void {
		throw new RuntimeException( 'Database unavailable.' );
	}
};

$resilient_logger = new OperationalLogger( $failing_repository );
$resilient_logger->error(
	OperationalEvent::DISTRIBUTION_FAILED,
	'This logging attempt must not escape.'
);

fwrite(
	STDOUT,
	"Operational logging OK: context sanitized, exceptions bounded, log failures contained." . PHP_EOL
);
