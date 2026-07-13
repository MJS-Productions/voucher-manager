<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Domain\Pool;

use RuntimeException;
use Throwable;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;

final class PoolLifecycleService {
	public function __construct(
		private readonly PoolLifecycleRepository $repository,
		private readonly OperationalLogger $logger
	) {}

	public function delete_available_codes( int $pool_id ): int {
		$deleted = $this->repository->delete_available_codes( $pool_id );
		$this->logger->info( OperationalEvent::POOL_AVAILABLE_CODES_DELETED, 'Available pool codes were permanently deleted.', array( 'pool_id' => $pool_id, 'deleted_available_count' => $deleted ) );
		return $deleted;
	}

	/** @return array{deleted_code_count:int,deleted_import_count:int} */
	public function delete_pool( int $pool_id ): array {
		try {
			$deleted = $this->repository->delete_pool_with_data( $pool_id );
		} catch ( Throwable $exception ) {
			$this->logger->error( OperationalEvent::POOL_DELETE_FAILED, 'Pool deletion failed and was rolled back.', array( 'pool_id' => $pool_id, 'exception_class' => $exception::class ) );
			throw $exception;
		}
		$this->logger->info( OperationalEvent::POOL_DELETED, 'Pool and associated data were permanently deleted.', array( 'pool_id' => $pool_id, 'deleted_code_count' => $deleted['deleted_code_count'], 'deleted_import_count' => $deleted['deleted_import_count'] ) );
		return $deleted;
	}
}
