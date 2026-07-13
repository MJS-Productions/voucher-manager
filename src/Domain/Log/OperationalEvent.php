<?php
/**
 * Operational event vocabulary.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Log;

/**
 * Stable, machine-readable event names.
 */
enum OperationalEvent: string {

	case IMPORT_COMPLETED          = 'import.completed';
	case IMPORT_FAILED             = 'import.failed';
	case IMPORT_ROLLED_BACK        = 'import.rolled_back';
	case IMPORT_ROLLBACK_BLOCKED   = 'import.rollback_blocked';
	case DISTRIBUTION_COMPLETED    = 'distribution.completed';
	case DISTRIBUTION_EMPTY        = 'distribution.empty';
	case DISTRIBUTION_FAILED       = 'distribution.failed';
	case ADMIN_ACTION_FAILED       = 'admin.action_failed';
	case POOL_AVAILABLE_CODES_DELETED = 'pool.available_codes_deleted';
	case POOL_DELETED              = 'pool.deleted';
	case POOL_DELETE_FAILED        = 'pool.delete_failed';
}
