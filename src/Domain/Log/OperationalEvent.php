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
	case SETTINGS_UPDATED          = 'settings.updated';
	case ACTIVITY_CLEANUP_COMPLETED = 'activity.cleanup_completed';
	case ACTIVITY_CLEANUP_FAILED    = 'activity.cleanup_failed';
	case PLUGIN_INSTALLED          = 'plugin.installed';
	case PLUGIN_ACTIVATED          = 'plugin.activated';
	case PLUGIN_DEACTIVATED        = 'plugin.deactivated';
	case PLUGIN_UNINSTALLED        = 'plugin.uninstalled';
	case POOL_CREATED              = 'pool.created';
	case POOL_UPDATED              = 'pool.updated';
	case POOL_ACTIVATED            = 'pool.activated';
	case POOL_DEACTIVATED          = 'pool.deactivated';
	case POOL_AVAILABLE_CODES_DELETED = 'pool.available_codes_deleted';
	case POOL_DELETED              = 'pool.deleted';
	case POOL_DELETE_FAILED        = 'pool.delete_failed';
}
