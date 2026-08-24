<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Distribution;

use VoucherManager\Domain\Code\CodeRepository;
use VoucherManager\Domain\Log\LogRepository;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Pool\PoolRepository;

final class DistributionService {
	public function __construct(
		private readonly PoolRepository $pools,
		private readonly CodeRepository $codes,
		private readonly LogRepository $logs
	) {}

	public function distribute( int $pool_id ): DistributionResult {
		$pool = $this->pools->find( $pool_id );
		if ( null === $pool || ! $pool->is_active() ) {
			return new DistributionResult( false, null, __( 'Pool is unavailable.', 'mjs-productions-voucher-manager' ), 0 );
		}

		$claimed = $this->codes->claim_next_available( $pool_id );
		if ( null === $claimed ) {
			$this->log_safely(
				OperationalEvent::DISTRIBUTION_EMPTY->value,
				'No available code could be distributed.',
				array( 'pool_id' => $pool_id )
			);
			return new DistributionResult( false, null, __( 'No available One-Time Codes remain in this pool.', 'mjs-productions-voucher-manager' ), 0 );
		}

		// Once the atomic claim commits, the successful business outcome is
		// authoritative. Post-claim metadata must never hide the voucher.
		$remaining = $this->remaining_safely( $pool_id );

		$this->log_safely(
			OperationalEvent::DISTRIBUTION_COMPLETED->value,
			'An available code was distributed.',
			array(
				'pool_id'   => $pool_id,
				'pool_name' => $pool->name(),
				'code_id'   => $claimed['id'],
				'remaining' => $remaining,
			)
		);

		return new DistributionResult( true, $claimed['code'], __( 'One-Time Code distributed.', 'mjs-productions-voucher-manager' ), $remaining );
	}

	private function remaining_safely( int $pool_id ): ?int {
		try {
			return $this->codes->count_available( $pool_id );
		} catch ( \Throwable $exception ) {
			$this->report_post_claim_failure( 'remaining_count', $exception );
			return null;
		}
	}

	/**
	 * @param array<string,mixed> $context
	 */
	private function log_safely( string $event_type, string $message, array $context ): void {
		try {
			$this->logs->add( $event_type, $message, $context );
		} catch ( \Throwable $exception ) {
			$this->report_post_claim_failure( 'activity_log', $exception );
		}
	}

	private function report_post_claim_failure( string $stage, \Throwable $exception ): void {
		// Intentional last-resort diagnostic: this path is used when normal
		// post-claim persistence or Activity logging has already failed.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log(
			sprintf(
				'Voucher Manager distribution post-claim failure [%s]: %s',
				$stage,
				$exception::class
			)
		);
	}
}
