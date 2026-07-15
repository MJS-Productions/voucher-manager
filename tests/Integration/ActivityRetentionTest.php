<?php
/**
 * Framework-free Activity retention test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

use VoucherManager\Domain\Activity\ActivityRetentionRepository;
use VoucherManager\Domain\Activity\ActivityRetentionService;

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

$root = dirname( __DIR__, 2 );

spl_autoload_register(
	static function ( string $class ) use ( $root ): void {
		$prefix = 'VoucherManager\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}

		$file = $root . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Activity retention assertion failed: ' . $message );
	}
};

$repository = new class() implements ActivityRetentionRepository {
	public string $cutoff = '';
	public int $limit = 0;
	public int $calls = 0;

	public function delete_oldest_before( string $utc_cutoff, int $limit ): int {
		$this->cutoff = $utc_cutoff;
		$this->limit  = $limit;
		++$this->calls;
		return 17;
	}
};

$service = new ActivityRetentionService( $repository );
$now     = strtotime( '2026-07-15 12:00:00 UTC' );

$assert(
	'2026-04-16 12:00:00' === $service->cutoff( 90, $now ),
	'Retention cutoff must use a deterministic UTC day boundary.'
);

$deleted = $service->cleanup( 90, $now );
$assert( 17 === $deleted, 'Cleanup must return the bounded repository deletion count.' );
$assert( 500 === $repository->limit, 'Every cleanup run must delete at most 500 Activity rows.' );
$assert( '2026-04-16 12:00:00' === $repository->cutoff, 'Cleanup must pass the UTC cutoff to persistence.' );

$repository->calls = 0;
$assert( 0 === $service->cleanup( 0, $now ), 'Keep indefinitely must perform no deletion.' );
$assert( 0 === $repository->calls, 'Keep indefinitely must not call the deletion repository.' );

$repository_source = file_get_contents( $root . '/src/Infrastructure/WordPress/WpdbActivityRetentionRepository.php' );
$scheduler_source  = file_get_contents( $root . '/src/Lifecycle/ActivityRetentionScheduler.php' );
$deactivator       = file_get_contents( $root . '/src/Lifecycle/Deactivator.php' );
$bootstrap         = file_get_contents( $root . '/voucher-manager.php' );
$plugin_source     = file_get_contents( $root . '/src/Core/Plugin.php' );
$settings_admin    = file_get_contents( $root . '/src/Admin/SettingsAdmin.php' );
$template_source   = file_get_contents( $root . '/templates/admin/settings.php' );
$schema_source     = file_get_contents( $root . '/voucher-manager.php' );
$composer_source   = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $repository_source )
	&& str_contains( $repository_source, "\$wpdb->prefix . 'vm_logs'" )
	&& str_contains( $repository_source, 'ORDER BY id ASC' )
	&& str_contains( $repository_source, 'LIMIT %d' )
	&& ! str_contains( $repository_source, 'vm_codes' )
	&& ! str_contains( $repository_source, 'vm_imports' )
	&& ! str_contains( $repository_source, 'vm_pools' ),
	'Retention persistence must use bounded oldest-first deletion and touch only Activity.'
);

$assert(
	is_string( $scheduler_source )
	&& str_contains( $scheduler_source, "HOOK = 'voucher_manager_cleanup_activity'" )
	&& str_contains( $scheduler_source, "add_action( 'plugins_loaded', array( \$this, 'reconcile_from_wordpress' ), 20 )" )
	&& str_contains( $scheduler_source, 'public function reconcile_from_wordpress(): void' )
	&& str_contains( $scheduler_source, '$this->reconcile();' )
	&& ! str_contains( $scheduler_source, "add_action( 'plugins_loaded', array( \$this, 'reconcile' ), 20 )" )
	&& str_contains( $scheduler_source, "wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::HOOK )" )
	&& str_contains( $scheduler_source, "false === wp_next_scheduled( self::HOOK )" )
	&& str_contains( $scheduler_source, 'wp_clear_scheduled_hook( self::HOOK )' )
	&& str_contains( $scheduler_source, '0 === $settings->activity_retention_days()' ),
	'Activity cleanup must be scheduled daily, idempotently and removed for indefinite retention.'
);

$assert(
	is_string( $deactivator )
	&& str_contains( $deactivator, 'ActivityRetentionScheduler' )
	&& str_contains( $deactivator, 'unschedule' )
	&& ! str_contains( $deactivator, 'delete' ),
	'Deactivation must clear scheduled work without deleting data.'
);

$assert(
	is_string( $bootstrap )
	&& str_contains( $bootstrap, 'register_deactivation_hook' )
	&& str_contains( $bootstrap, 'Deactivator::class' ),
	'Plugin bootstrap must register the deactivation lifecycle.'
);

$assert(
	is_string( $plugin_source )
	&& str_contains( $plugin_source, '( new ActivityRetentionScheduler() )->register()' ),
	'Plugin boot must register the Cron callback and schedule reconciliation.'
);

$assert(
	is_string( $settings_admin )
	&& str_contains( $settings_admin, '( new ActivityRetentionScheduler() )->reconcile( $settings )' ),
	'Changing retention settings must reconcile scheduling immediately.'
);

$assert(
	is_string( $template_source )
	&& str_contains( $template_source, 'WordPress Cron in bounded daily batches' )
	&& str_contains( $template_source, 'does not delete Activity immediately' ),
	'Settings must explain asynchronous bounded cleanup.'
);

$assert(
	is_string( $schema_source )
	&& str_contains( $schema_source, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Activity retention must not introduce a database migration.'
);

$assert(
	is_string( $composer_source )
	&& str_contains( $composer_source, '@test:activity-retention' )
	&& strpos( $composer_source, '@test:activity-retention' ) < strpos( $composer_source, '@build' ),
	'Activity retention coverage must run before the release build.'
);

echo "Activity retention OK: UTC cutoff, 500-row bound, daily scheduling and data-preserving deactivation verified.\n";
