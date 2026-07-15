<?php
/**
 * Framework-free final Distribution safety review.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Final Distribution safety assertion failed: ' . $message );
	}
};

$admin      = file_get_contents( $root . '/src/Admin/DistributionAdmin.php' );
$service    = file_get_contents( $root . '/src/Domain/Distribution/DistributionService.php' );
$intent     = file_get_contents( $root . '/src/Infrastructure/WordPress/WpDistributionIntentStore.php' );
$result     = file_get_contents( $root . '/src/Infrastructure/WordPress/WpDistributionResultStore.php' );
$repository = file_get_contents( $root . '/src/Infrastructure/WordPress/WpdbCodeRepository.php' );
$template   = file_get_contents( $root . '/templates/admin/distribution.php' );
$direct     = file_get_contents( $root . '/templates/admin/distribution-direct-result.php' );
$uninstall  = file_get_contents( $root . '/uninstall.php' );
$composer   = file_get_contents( $root . '/composer.json' );
$plugin     = file_get_contents( $root . '/voucher-manager.php' );

$assert(
	is_string( $admin )
	&& strpos( $admin, 'check_admin_referer' ) < strpos( $admin, '$this->intents->consume' )
	&& strpos( $admin, '$this->intents->consume' ) < strpos( $admin, '$this->service->distribute' ),
	'Capability/nonce and one-use intent boundaries must precede voucher claim execution.'
);

$assert(
	is_string( $repository )
	&& str_contains( $repository, "'START TRANSACTION'" )
	&& str_contains( $repository, 'FOR UPDATE' )
	&& str_contains( $repository, "CodeStatus::AVAILABLE->value" )
	&& str_contains( $repository, "CodeStatus::ASSIGNED->value" )
	&& str_contains( $repository, 'if ( 1 !== $updated )' )
	&& str_contains( $repository, "'ROLLBACK'" )
	&& str_contains( $repository, "'COMMIT'" ),
	'The authoritative voucher claim must remain transactional, locked and affected-row checked.'
);

$assert(
	is_string( $service )
	&& str_contains( $service, 'private function remaining_safely' )
	&& str_contains( $service, 'private function log_safely' )
	&& str_contains( $service, '$exception::class' )
	&& ! str_contains( $service, '$exception->getMessage()' )
	&& ! str_contains( $service, "'code' => \$claimed['code']" ),
	'Post-claim metadata and Activity failures must be contained without voucher leakage.'
);

$assert(
	is_string( $intent )
	&& str_contains( $intent, 'TTL_SECONDS = 600' )
	&& str_contains( $intent, 'bin2hex( random_bytes( 32 ) )' )
	&& str_contains( $intent, '$stored_user_id !== $user_id' )
	&& str_contains( $intent, 'return 1 === $deleted' ),
	'Distribution intents must remain opaque, owner-scoped, expiring and consume-once.'
);

$assert(
	is_string( $result )
	&& str_contains( $result, 'TTL_SECONDS = 60' )
	&& str_contains( $result, 'create_delivery_for_intent' )
	&& str_contains( $result, "'payload'    => \$payload" )
	&& str_contains( $result, 'if ( 1 !== $deleted )' )
	&& str_contains( $result, '$this->payload_belongs_to_user' ),
	'Result delivery must preserve authoritative replay payloads while keeping every delivery owner-scoped and consume-once.'
);

$assert(
	is_string( $admin )
	&& str_contains( $admin, 'wait_for_replay_delivery' )
	&& str_contains( $admin, 'create_delivery_for_intent' )
	&& str_contains( $admin, 'render_direct_result' )
	&& ! str_contains( $admin, 'set_transient' ),
	'Replay recovery and direct post-claim fallback must remain present without shared per-user transient state.'
);

$assert(
	is_string( $template )
	&& str_contains( $template, 'can_distribute' )
	&& str_contains( $template, 'selected( $selected_pool_id' )
	&& str_contains( $template, 'name="distribution_intent"' )
	&& ! str_contains( $template, 'get_transient' ),
	'Distribution presentation must filter selectable Pools, preserve Pool context and submit the one-use intent.'
);

$assert(
	is_string( $direct )
	&& str_contains( $direct, 'Copy it before leaving this page.' )
	&& str_contains( $direct, 'vm-distributed-code' )
	&& ! str_contains( $direct, 'result=' ),
	'A committed claim must have protected direct presentation when result persistence fails.'
);

$assert(
	is_string( $admin )
	&& str_contains( $admin, "'result' => \$result_token" )
	&& ! str_contains( $admin, "'code' => \$result_token" ),
	'Redirect navigation must carry only an opaque result token.'
);

$assert(
	is_string( $uninstall )
	&& str_contains( $uninstall, 'DISTRIBUTION_INTENT_OPTION_PREFIX' )
	&& str_contains( $uninstall, 'DISTRIBUTION_RESULT_OPTION_PREFIX' )
	&& str_contains( $uninstall, 'DISTRIBUTION_RESULT_INTENT_OPTION_PREFIX' ),
	'All ephemeral Distribution runtime state must remain inside the uninstall cleanup boundary.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '@test:distribution-safety-review' )
	&& strpos( $composer, '@test:distribution-safety-review' ) < strpos( $composer, '@build' ),
	'Final Distribution safety review coverage must run before build.'
);

$assert(
	is_string( $plugin )
	&& str_contains( $plugin, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Final Distribution safety review must not introduce a database migration.'
);

echo "Final Distribution safety review OK: intent, claim, post-claim and result-delivery invariants verified.\n";
