<?php
/**
 * Pool application service.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Domain\Pool;

use InvalidArgumentException;

final class PoolService {
	public function __construct( private readonly PoolRepository $repository ) {}

	public function create( string $name, string $description, int $warning_threshold, bool $active ): int {
		$name = trim( $name );
		if ( '' === $name ) { throw new InvalidArgumentException( 'Pool name is required.' ); }
		return $this->repository->create( $name, trim( $description ), max( 0, $warning_threshold ), $active );
	}

	public function update( int $id, string $name, string $description, int $warning_threshold, bool $active ): bool {
		if ( 1 > $id ) { throw new InvalidArgumentException( 'Invalid pool ID.' ); }
		$name = trim( $name );
		if ( '' === $name ) { throw new InvalidArgumentException( 'Pool name is required.' ); }
		return $this->repository->update( $id, $name, trim( $description ), max( 0, $warning_threshold ), $active );
	}
}
