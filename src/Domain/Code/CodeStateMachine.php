<?php
/**
 * Code lifecycle state machine.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Code;

/**
 * Central authority for allowed code-state transitions.
 */
final class CodeStateMachine {

	/**
	 * Determine whether a transition is allowed.
	 */
	public function can_transition( CodeStatus $from, CodeStatus $to ): bool {
		if ( $from === $to ) {
			return false;
		}

		return match ( $from ) {
			CodeStatus::AVAILABLE => in_array(
				$to,
				array(
					CodeStatus::RESERVED,
					CodeStatus::ASSIGNED,
					CodeStatus::EXPIRED,
					CodeStatus::CANCELLED,
				),
				true
			),
			CodeStatus::RESERVED => in_array(
				$to,
				array(
					CodeStatus::AVAILABLE,
					CodeStatus::ASSIGNED,
					CodeStatus::EXPIRED,
					CodeStatus::CANCELLED,
				),
				true
			),
			CodeStatus::ASSIGNED,
			CodeStatus::EXPIRED,
			CodeStatus::CANCELLED => false,
		};
	}

	/**
	 * Guard a state transition.
	 *
	 * @throws InvalidCodeStatusTransition When the transition is forbidden.
	 */
	public function assert_transition( CodeStatus $from, CodeStatus $to ): void {
		if ( ! $this->can_transition( $from, $to ) ) {
			throw InvalidCodeStatusTransition::from_statuses( $from, $to );
		}
	}
}
