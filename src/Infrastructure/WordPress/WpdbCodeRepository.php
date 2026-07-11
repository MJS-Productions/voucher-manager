<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Infrastructure\WordPress;

use VoucherManager\Domain\Code\CodeRepository;

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
			$args[] = 'available';
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
		$result = $wpdb->delete( $this->table(), array( 'import_id' => $import_id, 'status' => 'available' ), array( '%d', '%s' ) );
		return false === $result ? 0 : (int) $result;
	}

	public function count_assigned_by_import( int $import_id ): int {
		global $wpdb;
		$table = $this->table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE import_id = %d AND status != %s", $import_id, 'available' ) );
	}
}
