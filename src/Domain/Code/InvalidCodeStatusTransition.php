<?php
/**
 * Invalid code-state transition exception.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Code;

use DomainException;

/**
 * Raised when application code attempts a forbidden state transition.
 */
final class InvalidCodeStatusTransition extends DomainException {

	/**
	 * Create an exception for a forbidden transition.
	 */
	public static function from_statuses( CodeStatus $from, CodeStatus $to ): self {
		return new self(
			sprintf(
				'Code status transition from "%s" to "%s" is not allowed.',
				$from->value,
				$to->value
			)
		);
	}
}
