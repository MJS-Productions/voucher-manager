<?php
/**
 * Voucher-code lifecycle states.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Code;

/**
 * Defines every state understood by the code domain.
 *
 * Only AVAILABLE and ASSIGNED are currently exposed by application workflows.
 * RESERVED, EXPIRED and CANCELLED are defined for future integrations and may
 * not be written without passing through CodeStateMachine.
 */
enum CodeStatus: string {

	case AVAILABLE = 'available';
	case RESERVED  = 'reserved';
	case ASSIGNED  = 'assigned';
	case EXPIRED   = 'expired';
	case CANCELLED = 'cancelled';
}
