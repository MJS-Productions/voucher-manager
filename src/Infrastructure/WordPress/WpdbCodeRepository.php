<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Infrastructure\WordPress;

use VoucherManager\Domain\Code\CodeRepository;
use VoucherManager\Domain\Code\CodeStateMachine;
use VoucherManager\Domain\Code\CodeStatus;

final class WpdbCodeRepository implements CodeRepository {
	private function table(): string { global $wpdb; return $wpdb->prefix . 'vm_codes'; }

	public function insert_batch( int $pool_id, int $import_id, array $codes ): int {
		global $wpdb;
		if ( array() === $codes ) { return 0; }
		$values = array();
		$args = array();
		$now = current_time( 'mysql', true );
		foreach ( $codes as $code ) {
			$values[] = '(%d,%d,%s,%s,%s,%s)';
			$args[] = $pool_id;
			$args[] = $import_id;
			$args[] = hash( 'sha256', $code );
			$args[] = $code;
			$args[] = CodeStatus::AVAILABLE->value;
			$args[] = $now;
		}
		$table = $this->table();
		$sql = 'INSERT IGNORE INTO %i (pool_id,import_id,code_hash,code,status,imported_at) VALUES ' . implode( ',', $values );
		$query_args = array_merge( array( $table ), $args );
		// The VALUES placeholder list is generated internally; identifier and values are prepared.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $wpdb->prepare( $sql, $query_args ) );
		return false === $result ? 0 : (int) $result;
	}

	public function delete_available_by_import( int $import_id ): int {
		global $wpdb;
		$result = $wpdb->delete( $this->table(), array( 'import_id' => $import_id, 'status' => CodeStatus::AVAILABLE->value ), array( '%d', '%s' ) );
		return false === $result ? 0 : (int) $result;
	}

	public function count_assigned_by_import( int $import_id ): int {
		global $wpdb;
		$table = $this->table();
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE import_id = %d AND status != %s', $table, $import_id, CodeStatus::AVAILABLE->value ) );
	}

	/**
	 * Return assigned-code counts for a set of import IDs.
	 *
	 * @param array<int,int> $import_ids Import IDs.
	 * @return array<int,int> Assigned counts keyed by import ID.
	 */
	public function assigned_counts_by_import( array $import_ids ): array {
		global $wpdb;

		$ids = array_values( array_unique( array_filter( array_map( 'absint', $import_ids ) ) ) );
		if ( array() === $ids ) {
			return array();
		}

		$counts = array_fill_keys( $ids, 0 );
		$table  = $this->table();
		$in     = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
		$args   = array_merge( array( $table ), $ids, array( CodeStatus::AVAILABLE->value ) );

		// The IN placeholder list is generated from validated integer IDs.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT import_id, COUNT(*) AS assigned_count FROM %i WHERE import_id IN ({$in}) AND status != %s GROUP BY import_id",
				...$args
			),
			ARRAY_A
		);

		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$import_id = isset( $row['import_id'] ) ? absint( $row['import_id'] ) : 0;
			if ( 0 < $import_id && array_key_exists( $import_id, $counts ) ) {
				$counts[ $import_id ] = isset( $row['assigned_count'] ) ? absint( $row['assigned_count'] ) : 0;
			}
		}

		return $counts;
	}

	public function claim_next_available( int $pool_id ): ?array {
		( new CodeStateMachine() )->assert_transition( CodeStatus::AVAILABLE, CodeStatus::ASSIGNED );

		global $wpdb;
		$table = $this->table();

		// Keep selection and state transition in one transaction so concurrent
		// requests cannot successfully claim the same available row.
		$wpdb->query( 'START TRANSACTION' );
		try {
			$row = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT id, code FROM %i WHERE pool_id = %d AND status = %s ORDER BY id ASC LIMIT 1 FOR UPDATE',
					$table,
					$pool_id,
					CodeStatus::AVAILABLE->value
				),
				ARRAY_A
			);
			if ( ! is_array( $row ) ) {
				$wpdb->query( 'COMMIT' );
				return null;
			}

			$updated = $wpdb->update(
				$table,
				array( 'status' => CodeStatus::ASSIGNED->value, 'assigned_at' => current_time( 'mysql', true ) ),
				array( 'id' => (int) $row['id'], 'status' => CodeStatus::AVAILABLE->value ),
				array( '%s', '%s' ),
				array( '%d', '%s' )
			);
			if ( 1 !== $updated ) {
				$wpdb->query( 'ROLLBACK' );
				return null;
			}
			$wpdb->query( 'COMMIT' );
			return array( 'id' => (int) $row['id'], 'code' => (string) $row['code'] );
		} catch ( \Throwable $exception ) {
			$wpdb->query( 'ROLLBACK' );
			throw $exception;
		}
	}

	public function count_available( int $pool_id ): int {
		global $wpdb;
		$table = $this->table();
		return (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE pool_id = %d AND status = %s', $table, $pool_id, CodeStatus::AVAILABLE->value )
		);
	}
}
