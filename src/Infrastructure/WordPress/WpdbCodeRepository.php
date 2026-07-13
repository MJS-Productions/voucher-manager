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
		$sql = "INSERT IGNORE INTO {$table} (pool_id,import_id,code_hash,code,status,imported_at) VALUES " . implode( ',', $values );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query( $wpdb->prepare( $sql, $args ) );
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
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE import_id = %d AND status != %s", $import_id, CodeStatus::AVAILABLE->value ) );
	}

	public function claim_next_available( int $pool_id ): ?array {
		( new CodeStateMachine() )->assert_transition( CodeStatus::AVAILABLE, CodeStatus::ASSIGNED );

		global $wpdb;
		$table = $this->table();

		// Keep selection and state transition in one transaction so concurrent
		// requests cannot successfully claim the same available row.
		$wpdb->query( 'START TRANSACTION' );
		try {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, code FROM {$table} WHERE pool_id = %d AND status = %s ORDER BY id ASC LIMIT 1 FOR UPDATE",
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
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE pool_id = %d AND status = %s", $pool_id, CodeStatus::AVAILABLE->value )
		);
	}
}
