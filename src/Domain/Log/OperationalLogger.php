<?php
/**
 * Privacy-aware operational logger.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Log;

use Throwable;

/**
 * Sanitizes operational context and prevents logging failures from cascading.
 */
final class OperationalLogger implements LogRepository {

	private const MAX_STRING_LENGTH = 200;

	/**
	 * Context keys that must never be persisted by operational logging.
	 *
	 * @var array<string>
	 */
	private const SENSITIVE_KEYS = array(
		'code',
		'voucher_code',
		'email',
		'email_address',
		'ip',
		'ip_address',
		'user_agent',
		'password',
		'token',
		'secret',
		'authorization',
		'cookie',
	);

	public function __construct(
		private readonly LogRepository $repository
	) {
	}

	/**
	 * Add an informational event.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public function info(
		OperationalEvent|string $event,
		string $message,
		array $context = array()
	): void {
		$this->write( LogLevel::INFO, $event, $message, $context );
	}

	/**
	 * Add a warning event.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public function warning(
		OperationalEvent|string $event,
		string $message,
		array $context = array()
	): void {
		$this->write( LogLevel::WARNING, $event, $message, $context );
	}

	/**
	 * Add an error event.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public function error(
		OperationalEvent|string $event,
		string $message,
		array $context = array()
	): void {
		$this->write( LogLevel::ERROR, $event, $message, $context );
	}

	/**
	 * Preserve the existing repository interface for application services.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public function add(
		string $event_type,
		string $message,
		array $context = array()
	): void {
		$this->info( $event_type, $message, $context );
	}

	/**
	 * Return privacy-safe context for persistence.
	 *
	 * @param array<string,mixed> $context Raw context.
	 * @return array<string,int|float|string|bool|null>
	 */
	public function sanitize_context( array $context ): array {
		$safe = array();

		foreach ( $context as $key => $value ) {
			$normalized_key = strtolower( (string) $key );

			if ( in_array( $normalized_key, self::SENSITIVE_KEYS, true ) ) {
				continue;
			}

			if ( null === $value || is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
				$safe[ $normalized_key ] = $value;
				continue;
			}

			if ( is_string( $value ) ) {
				$safe[ $normalized_key ] = substr( $value, 0, self::MAX_STRING_LENGTH );
			}
		}

		ksort( $safe );

		return $safe;
	}

	/**
	 * Persist a sanitized event without allowing log failures to cascade.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	private function write(
		LogLevel $level,
		OperationalEvent|string $event,
		string $message,
		array $context
	): void {
		$event_name = $event instanceof OperationalEvent ? $event->value : $event;
		$safe       = $this->sanitize_context( $context );
		$safe       = array_merge(
			array(
				'level' => $level->value,
			),
			$safe
		);

		try {
			$this->repository->add(
				$event_name,
				substr( $message, 0, self::MAX_STRING_LENGTH ),
				$safe
			);
		} catch ( Throwable ) {
			// Operational logging must never turn a handled error into a fatal one.
		}
	}
}
