<?php
/**
 * Error boundary for administrative operations.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Support;

use Throwable;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;

/**
 * Converts unexpected exceptions into controlled fallback results.
 */
final class ErrorBoundary {

	public function __construct(
		private readonly OperationalLogger $logger
	) {
	}

	/**
	 * Execute an operation and return a safe fallback when it throws.
	 *
	 * Exception messages and traces are intentionally not persisted.
	 *
	 * @template T
	 * @param callable():T $operation Protected operation.
	 * @param T            $fallback  Safe fallback result.
	 * @param array<string,int|float|string|bool|null> $context Operational context.
	 * @return T
	 */
	public function execute(
		callable $operation,
		mixed $fallback,
		array $context = array()
	): mixed {
		try {
			return $operation();
		} catch ( Throwable $exception ) {
			$this->logger->error(
				OperationalEvent::ADMIN_ACTION_FAILED,
				'An administrative action failed unexpectedly.',
				array_merge(
					$context,
					array(
						'exception_class' => $exception::class,
						'exception_code'  => (int) $exception->getCode(),
					)
				)
			);

			return $fallback;
		}
	}
}
