<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Infrastructure\WordPress;

use RuntimeException;
use Throwable;
use VoucherManager\Domain\Code\CodeStatus;
use VoucherManager\Domain\Pool\PoolLifecycleRepository;

final class WpdbPoolLifecycleRepository implements PoolLifecycleRepository {
	private function table( string $name ): string { global $wpdb; return $wpdb->prefix . 'vm_' . $name; }

	public function deletion_summary( int $pool_id ): ?array {
		global $wpdb;
		$pools = $this->table( 'pools' ); $codes = $this->table( 'codes' ); $imports = $this->table( 'imports' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$pools} WHERE id = %d", $pool_id ) );
		if ( null === $exists ) { return null; }
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$codes} WHERE pool_id = %d", $pool_id ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$available = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$codes} WHERE pool_id = %d AND status = %s", $pool_id, CodeStatus::AVAILABLE->value ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$import_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$imports} WHERE pool_id = %d", $pool_id ) );
		return array( 'total' => $total, 'available' => $available, 'assigned' => $total - $available, 'imports' => $import_count );
	}

	public function delete_available_codes( int $pool_id ): int {
		global $wpdb;
		$result = $wpdb->delete( $this->table( 'codes' ), array( 'pool_id' => $pool_id, 'status' => CodeStatus::AVAILABLE->value ), array( '%d', '%s' ) );
		if ( false === $result ) { throw new RuntimeException( 'Available code deletion failed.' ); }
		return (int) $result;
	}

	public function delete_pool_with_data( int $pool_id ): array {
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
		try {
			$codes = $wpdb->delete( $this->table( 'codes' ), array( 'pool_id' => $pool_id ), array( '%d' ) );
			if ( false === $codes ) { throw new RuntimeException( 'Code deletion failed.' ); }
			$imports = $wpdb->delete( $this->table( 'imports' ), array( 'pool_id' => $pool_id ), array( '%d' ) );
			if ( false === $imports ) { throw new RuntimeException( 'Import deletion failed.' ); }
			$pool = $wpdb->delete( $this->table( 'pools' ), array( 'id' => $pool_id ), array( '%d' ) );
			if ( 1 !== $pool ) { throw new RuntimeException( 'Pool deletion failed.' ); }
			$wpdb->query( 'COMMIT' );
			return array( 'deleted_code_count' => (int) $codes, 'deleted_import_count' => (int) $imports );
		} catch ( Throwable $exception ) {
			$wpdb->query( 'ROLLBACK' );
			throw $exception;
		}
	}
}
