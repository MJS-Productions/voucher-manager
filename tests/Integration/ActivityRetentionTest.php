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
	public int $find_calls = 0;
	public int $delete_calls = 0;
	/** @var array<int> */
	public array $deleted_ids = array();

	/** @return array<int,array{id:int,event_type:string,message:string,context:?string,created_at:string}> */
	public function find_oldest_before( string $utc_cutoff, int $limit ): array {
		$this->cutoff = $utc_cutoff;
		$this->limit  = $limit;
		++$this->find_calls;

		return array(
			array(
				'id'         => 11,
				'event_type' => 'import.completed',
				'message'    => 'Import completed.',
				'context'    => '{"pool_id":4}',
				'created_at' => '2026-04-01 09:00:00',
			),
			array(
				'id'         => 12,
				'event_type' => 'pool.created',
				'message'    => 'Pool created.',
				'context'    => '{"pool_id":4}',
				'created_at' => '2026-04-02 09:00:00',
			),
			array(
				'id'         => 13,
				'event_type' => 'settings.updated',
				'message'    => 'Settings updated.',
				'context'    => '{}',
				'created_at' => '2026-04-03 09:00:00',
			),
		);
	}

	public function delete_by_ids( array $ids ): int {
		++$this->delete_calls;
		$this->deleted_ids = $ids;

		return count( $ids );
	}
};

$service = new ActivityRetentionService( $repository );
$now     = strtotime( '2026-07-15 12:00:00 UTC' );

$assert(
	'2026-04-16 12:00:00' === $service->cutoff( 90, $now ),
	'Retention cutoff must use deterministic UTC calculation.'
);

$deleted = $service->cleanup( 90, $now );
$assert( 3 === $deleted, 'Standalone cleanup must delete the complete selected candidate batch.' );
$assert( array( 11, 12, 13 ) === $repository->deleted_ids, 'Standalone cleanup must preserve Free deletion behaviour.' );
$assert( 500 === $repository->limit, 'Every cleanup run must select at most 500 Activity rows.' );
$assert( '2026-04-16 12:00:00' === $repository->cutoff, 'Cleanup must pass the UTC cutoff to persistence.' );

$repository->deleted_ids = array();
$deleted = $service->cleanup(
	90,
	$now,
	static function ( array $candidates ) use ( $assert ): array {
		$assert( 3 === count( $candidates ), 'Archive hand-off must receive concrete retention candidates.' );
		$assert(
			array( 'id', 'event_type', 'message', 'context', 'created_at' ) === array_keys( $candidates[0] ),
			'Archive hand-off candidates must contain the complete Activity business record.'
		);

		return array( 11, 13 );
	}
);
$assert( 2 === $deleted, 'Archive-enabled cleanup must delete only confirmed archived candidates.' );
$assert( array( 11, 13 ) === $repository->deleted_ids, 'Unconfirmed candidate IDs must remain active.' );

$repository->delete_calls = 0;
$thrown = false;
try {
	$service->cleanup(
		90,
		$now,
		static fn( array $candidates ): array => array( 999 )
	);
} catch ( UnexpectedValueException ) {
	$thrown = true;
}
$assert( $thrown, 'Confirming an ID outside the selected candidate batch must fail closed.' );
$assert( 0 === $repository->delete_calls, 'Invalid archive confirmation must not delete Activity.' );

$repository->delete_calls = 0;
$thrown = false;
try {
	$service->cleanup(
		90,
		$now,
		static function ( array $candidates ): array {
			throw new RuntimeException( 'Archive unavailable.' );
		}
	);
} catch ( RuntimeException ) {
	$thrown = true;
}
$assert( $thrown, 'Archive hand-off failures must propagate to the scheduler error boundary.' );
$assert( 0 === $repository->delete_calls, 'Archive hand-off failure must retain all active candidates.' );

$repository->find_calls = 0;
$repository->delete_calls = 0;
$assert( 0 === $service->cleanup( 0, $now ), 'Keep indefinitely must perform no deletion.' );
$assert( 0 === $repository->find_calls, 'Keep indefinitely must not select retention candidates.' );
$assert( 0 === $repository->delete_calls, 'Keep indefinitely must not call Activity deletion.' );

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
	&& str_contains( $repository_source, 'SELECT id, event_type, message, context, created_at' )
	&& str_contains( $repository_source, 'ORDER BY id ASC' )
	&& str_contains( $repository_source, 'LIMIT %d' )
	&& str_contains( $repository_source, 'public function delete_by_ids( array $ids ): int' )
	&& ! str_contains( $repository_source, 'vm_codes' )
	&& ! str_contains( $repository_source, 'vm_imports' )
	&& ! str_contains( $repository_source, 'vm_pools' ),
	'Retention persistence must select bounded oldest-first candidates and touch only Activity.'
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
	'Activity cleanup must remain scheduled daily, idempotently and removed for indefinite retention.'
);

$assert(
	is_string( $scheduler_source )
	&& str_contains( $scheduler_source, 'ActivityRetentionArchiveHandoff::is_active()' )
	&& str_contains( $scheduler_source, 'ActivityRetentionArchiveHandoff::archive( $candidates )' )
	&& str_contains( $scheduler_source, '->cleanup( $retention_days, null, $archive_handoff )' ),
	'Free must remain retention-policy owner while allowing the supported archive hand-off.'
);

$assert(
	is_string( $scheduler_source )
	&& str_contains( $scheduler_source, 'OperationalEvent::ACTIVITY_CLEANUP_COMPLETED' )
	&& str_contains( $scheduler_source, 'if ( 0 < $deleted )' )
	&& str_contains( $scheduler_source, "'deleted_count'  => \$deleted" )
	&& str_contains( $scheduler_source, "'retention_days' => \$retention_days" ),
	'Successful automatic cleanup must record Activity only when at least one expired entry was deleted.'
);

$assert(
	is_string( $scheduler_source )
	&& str_contains( $scheduler_source, 'OperationalEvent::ACTIVITY_CLEANUP_FAILED' )
	&& str_contains( $scheduler_source, "'exception_class' => \$exception::class" )
	&& ! str_contains( $scheduler_source, '$exception->getMessage()' )
	&& ! str_contains( $scheduler_source, '$exception->getTrace' )
	&& str_contains( $scheduler_source, 'Voucher Manager Activity cleanup failed: %s' ),
	'Failed automatic cleanup must retain the existing bounded error boundary.'
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
	&& str_contains( $template_source, 'Cleanup runs automatically through WordPress Cron' )
	&& str_contains( $template_source, 'does not delete Activity immediately' )
	&& str_contains( $template_source, '0 < $settings->activity_retention_days()' ),
	'Settings must explain asynchronous bounded cleanup.'
);

$assert(
	is_string( $schema_source )
	&& str_contains( $schema_source, "VOUCHER_MANAGER_DATABASE_VERSION', '2'" ),
	'Activity retention hand-off must not introduce a database migration.'
);

$assert(
	is_string( $composer_source )
	&& str_contains( $composer_source, '@test:activity-retention' )
	&& strpos( $composer_source, '@test:activity-retention' ) < strpos( $composer_source, '@build' ),
	'Activity retention coverage must run before the release build.'
);

echo "Activity retention OK: standalone cleanup and failure-safe archive hand-off verified.\n";
