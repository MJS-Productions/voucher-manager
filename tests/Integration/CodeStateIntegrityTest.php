<?php
/**
 * Framework-free code-state integrity test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Domain\Code\CodeStateMachine;
use VoucherManager\Domain\Code\CodeStatus;
use VoucherManager\Domain\Code\InvalidCodeStatusTransition;

$root = dirname(__DIR__, 2);

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
		throw new RuntimeException( 'State-integrity assertion failed: ' . $message );
	}
};

$machine = new CodeStateMachine();

$allowed = array(
	array( CodeStatus::AVAILABLE, CodeStatus::RESERVED ),
	array( CodeStatus::AVAILABLE, CodeStatus::ASSIGNED ),
	array( CodeStatus::AVAILABLE, CodeStatus::EXPIRED ),
	array( CodeStatus::AVAILABLE, CodeStatus::CANCELLED ),
	array( CodeStatus::RESERVED, CodeStatus::AVAILABLE ),
	array( CodeStatus::RESERVED, CodeStatus::ASSIGNED ),
	array( CodeStatus::RESERVED, CodeStatus::EXPIRED ),
	array( CodeStatus::RESERVED, CodeStatus::CANCELLED ),
);

foreach ( $allowed as [ $from, $to ] ) {
	$assert(
		$machine->can_transition( $from, $to ),
		sprintf( '%s -> %s should be allowed.', $from->value, $to->value )
	);
	$machine->assert_transition( $from, $to );
}

$forbidden = array(
	array( CodeStatus::AVAILABLE, CodeStatus::AVAILABLE ),
	array( CodeStatus::RESERVED, CodeStatus::RESERVED ),
	array( CodeStatus::ASSIGNED, CodeStatus::AVAILABLE ),
	array( CodeStatus::ASSIGNED, CodeStatus::RESERVED ),
	array( CodeStatus::ASSIGNED, CodeStatus::EXPIRED ),
	array( CodeStatus::EXPIRED, CodeStatus::AVAILABLE ),
	array( CodeStatus::CANCELLED, CodeStatus::AVAILABLE ),
);

foreach ( $forbidden as [ $from, $to ] ) {
	$assert(
		! $machine->can_transition( $from, $to ),
		sprintf( '%s -> %s should be forbidden.', $from->value, $to->value )
	);

	$thrown = false;

	try {
		$machine->assert_transition( $from, $to );
	} catch ( InvalidCodeStatusTransition ) {
		$thrown = true;
	}

	$assert(
		$thrown,
		sprintf( '%s -> %s must throw.', $from->value, $to->value )
	);
}

$assert(
	array_map(
		static fn( CodeStatus $status ): string => $status->value,
		CodeStatus::cases()
	) === array( 'available', 'reserved', 'assigned', 'expired', 'cancelled' ),
	'The public status vocabulary changed unexpectedly.'
);

fwrite(
	STDOUT,
	"Code-state integrity OK: allowed transitions accepted; terminal and invalid transitions rejected." . PHP_EOL
);
