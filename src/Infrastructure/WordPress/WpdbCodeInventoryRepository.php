<?php
/**
 * WordPress code inventory repository.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Infrastructure\WordPress;

use VoucherManager\Domain\Code\CodeInventoryRecord;
use VoucherManager\Domain\Code\CodeInventoryRepository;
use VoucherManager\Domain\Code\CodeStatus;

/**
 * Loads privacy-safe, pool-scoped inventory data.
 */
final class WpdbCodeInventoryRepository implements CodeInventoryRepository {

	private function codes_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'vm_codes';
	}

	private function imports_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'vm_imports';
	}

	public function search(
		int $pool_id,
		?CodeStatus $status,
		?int $import_id,
		int $limit,
		int $offset
	): array {
		global $wpdb;

		[$where, $args] = $this->where( $pool_id, $status, $import_id );
		$args[]         = max( 1, min( 100, $limit ) );
		$args[]         = max( 0, $offset );
		$table          = $this->codes_table();

		$sql = "SELECT id, pool_id, import_id, CASE WHEN CHAR_LENGTH(code) > 4 THEN RIGHT(code, 4) ELSE '' END AS code_suffix, status, imported_at, assigned_at
			FROM {$table}
			WHERE {$where}
			ORDER BY id DESC
			LIMIT %d OFFSET %d";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A );

		$records = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$status_value = CodeStatus::tryFrom( (string) ( $row['status'] ?? '' ) );
			if ( null === $status_value ) {
				continue;
			}

			$records[] = new CodeInventoryRecord(
				(int) $row['id'],
				(int) $row['pool_id'],
				null === $row['import_id'] ? null : (int) $row['import_id'],
				(string) ( $row['code_suffix'] ?? '' ),
				$status_value,
				(string) $row['imported_at'],
				empty( $row['assigned_at'] ) ? null : (string) $row['assigned_at']
			);
		}

		return $records;
	}

	public function count_matching( int $pool_id, ?CodeStatus $status, ?int $import_id ): int {
		global $wpdb;

		[$where, $args] = $this->where( $pool_id, $status, $import_id );
		$table          = $this->codes_table();
		$sql            = "SELECT COUNT(*) FROM {$table} WHERE {$where}";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) );
	}

	public function counts( int $pool_id ): array {
		global $wpdb;

		$table = $this->codes_table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT status, COUNT(*) AS amount FROM {$table} WHERE pool_id = %d AND status IN (%s, %s) GROUP BY status",
				$pool_id,
				CodeStatus::AVAILABLE->value,
				CodeStatus::ASSIGNED->value
			),
			ARRAY_A
		);

		$counts = array( 'total' => 0, 'available' => 0, 'assigned' => 0 );
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$amount          = (int) $row['amount'];
			$counts['total'] += $amount;

			if ( CodeStatus::AVAILABLE->value === $row['status'] ) {
				$counts['available'] = $amount;
			}

			if ( CodeStatus::ASSIGNED->value === $row['status'] ) {
				$counts['assigned'] = $amount;
			}
		}

		return $counts;
	}

	public function import_options( int $pool_id ): array {
		global $wpdb;

		$codes   = $this->codes_table();
		$imports = $this->imports_table();

		$sql = "SELECT DISTINCT i.id, i.filename
			FROM {$imports} i
			INNER JOIN {$codes} c ON c.import_id = i.id
			WHERE c.pool_id = %d AND c.status IN (%s, %s)
			ORDER BY i.id DESC";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				$pool_id,
				CodeStatus::AVAILABLE->value,
				CodeStatus::ASSIGNED->value
			),
			ARRAY_A
		);

		return array_map(
			static fn( array $row ): array => array(
				'id'       => (int) $row['id'],
				'filename' => sanitize_file_name( (string) $row['filename'] ),
			),
			is_array( $rows ) ? $rows : array()
		);
	}

	/**
	 * @return array{0:string,1:array<int,int|string>}
	 */
	private function where( int $pool_id, ?CodeStatus $status, ?int $import_id ): array {
		$where = array( 'pool_id = %d', 'status IN (%s, %s)' );
		$args  = array( $pool_id, CodeStatus::AVAILABLE->value, CodeStatus::ASSIGNED->value );

		if ( null !== $status ) {
			$where[] = 'status = %s';
			$args[]  = $status->value;
		}

		if ( null !== $import_id ) {
			$where[] = 'import_id = %d';
			$args[]  = $import_id;
		}

		return array( implode( ' AND ', $where ), $args );
	}
}
