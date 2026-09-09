<?php
/**
 * Public Activity write extension contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;

/**
 * Provides supported write access to Voucher Manager Activity History.
 */
final class ActivityWriteApi {

	private readonly OperationalLogger $logger;

	public function __construct() {
		$this->logger = new OperationalLogger( new WpdbLogRepository() );
	}

	/**
	 * Write an informational Activity event.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public function info(
		string $event_type,
		string $message,
		array $context = array()
	): void {
		$this->logger->info( $event_type, $message, $context );
	}

	/**
	 * Write a warning Activity event.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public function warning(
		string $event_type,
		string $message,
		array $context = array()
	): void {
		$this->logger->warning( $event_type, $message, $context );
	}

	/**
	 * Write an error Activity event.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public function error(
		string $event_type,
		string $message,
		array $context = array()
	): void {
		$this->logger->error( $event_type, $message, $context );
	}
}
