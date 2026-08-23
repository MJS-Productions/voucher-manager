<?php
/**
 * Extension Activity retention archive hand-off contract test.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

$assert = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Extension Activity retention archive hand-off assertion failed: ' . $message );
	}
};

$handoff    = file_get_contents( $root . '/src/Extension/ActivityRetentionArchiveHandoff.php' );
$service    = file_get_contents( $root . '/src/Domain/Activity/ActivityRetentionService.php' );
$repository = file_get_contents( $root . '/src/Domain/Activity/ActivityRetentionRepository.php' );
$scheduler  = file_get_contents( $root . '/src/Lifecycle/ActivityRetentionScheduler.php' );
$composer   = file_get_contents( $root . '/composer.json' );

$assert(
	is_string( $handoff )
	&& str_contains( $handoff, 'final class ActivityRetentionArchiveHandoff' )
	&& str_contains( $handoff, "public const FILTER = 'voucher_manager_activity_retention_archive'" )
	&& str_contains( $handoff, 'public static function is_active(): bool' )
	&& str_contains( $handoff, 'public static function archive( array $candidates ): array' ),
	'A stable public archive hand-off boundary must exist.'
);

$assert(
	str_contains( $handoff, 'has_filter( self::FILTER )' )
	&& str_contains( $handoff, 'apply_filters( self::FILTER, array(), $candidates )' ),
	'The archive hand-off must use one narrow WordPress extension point.'
);

$assert(
	str_contains( $handoff, 'id:int' )
	&& str_contains( $handoff, 'event_type:string' )
	&& str_contains( $handoff, 'message:string' )
	&& str_contains( $handoff, 'context:?string' )
	&& str_contains( $handoff, 'created_at:string' ),
	'Archive consumers must receive the complete active Activity business record.'
);

$assert(
	is_string( $repository )
	&& str_contains( $repository, 'public function find_oldest_before( string $utc_cutoff, int $limit ): array;' )
	&& str_contains( $repository, 'public function delete_by_ids( array $ids ): int;' )
	&& ! str_contains( $repository, 'delete_oldest_before' ),
	'Retention persistence must separate candidate selection from confirmed deletion.'
);

$assert(
	is_string( $service )
	&& str_contains( $service, '$candidates = $this->repository->find_oldest_before(' )
	&& str_contains( $service, '$confirmed = $archive_handoff( $candidates );' )
	&& str_contains( $service, '$this->confirmed_candidate_ids( $confirmed, $candidate_ids )' )
	&& str_contains( $service, 'return $this->repository->delete_by_ids( $delete_ids );' ),
	'Free must delete only candidate IDs allowed by the archive confirmation boundary.'
);

$assert(
	str_contains( $service, 'if ( null !== $archive_handoff )' )
	&& str_contains( $service, '$delete_ids    = $candidate_ids;' ),
	'Free-alone retention behaviour must remain unchanged when no archive consumer is active.'
);

$assert(
	str_contains( $service, 'UnexpectedValueException' )
	&& str_contains( $service, 'Activity archive hand-off confirmed an invalid candidate ID.' ),
	'Invalid archive confirmation must fail closed instead of deleting unrelated Activity.'
);

$assert(
	is_string( $scheduler )
	&& str_contains( $scheduler, 'ActivityRetentionArchiveHandoff::is_active()' )
	&& str_contains( $scheduler, 'ActivityRetentionArchiveHandoff::archive( $candidates )' )
	&& str_contains( $scheduler, 'catch ( \\Throwable $exception )' ),
	'The existing Free scheduler must own retention execution and its failure boundary.'
);

$assert(
	is_string( $composer )
	&& str_contains( $composer, '"test:extension-activity-retention-handoff": "php tests/Integration/ExtensionActivityRetentionArchiveHandoffTest.php"' )
	&& str_contains( $composer, '"@test:extension-activity-retention-handoff"' ),
	'The archive hand-off contract test must be registered in the quality gate.'
);

echo "Extension Activity retention archive hand-off contract OK.\n";
