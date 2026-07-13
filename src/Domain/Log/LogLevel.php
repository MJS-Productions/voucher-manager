<?php
/**
 * Operational log severity.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Log;

/**
 * Severity vocabulary for operational events.
 */
enum LogLevel: string {

	case INFO    = 'info';
	case WARNING = 'warning';
	case ERROR   = 'error';
}
