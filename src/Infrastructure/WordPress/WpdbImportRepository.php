<?php
/** @package VoucherManager */
declare(strict_types=1);
namespace VoucherManager\Infrastructure\WordPress;

use VoucherManager\Domain\Import\ImportRecord;
use VoucherManager\Domain\Import\ImportRepository;

final class WpdbImportRepository implements ImportRepository {
	private function table(): string { global $wpdb; return $wpdb->prefix . 'vm_imports'; }
	private function pools_table(): string { global $wpdb; return $wpdb->prefix . 'vm_pools'; }

	public function start( int $pool_id, string $filename, string $file_type ): int {
		global $wpdb;
		$result = $wpdb->insert( $this->table(), array( 'pool_id'=>$pool_id, 'filename'=>$filename, 'file_type'=>$file_type, 'status'=>'processing', 'created_at'=>current_time( 'mysql', true ) ), array( '%d','%s','%s','%s','%s' ) );
		return false === $result ? 0 : (int) $wpdb->insert_id;
	}

	public function complete( int $id, int $total, int $imported, int $skipped, int $invalid ): bool { return $this->finish( $id, 'completed', $total, $imported, $skipped, $invalid ); }
	public function fail( int $id, int $total, int $imported, int $skipped, int $invalid ): bool { return $this->finish( $id, 'failed', $total, $imported, $skipped, $invalid ); }

	public function recent( int $limit = 20 ): array {
		global $wpdb;
		$imports=$this->table(); $pools=$this->pools_table(); $limit=max(1,min(100,$limit));
		$rows=$wpdb->get_results( $wpdb->prepare( 'SELECT i.*, p.name AS pool_name FROM %i i LEFT JOIN %i p ON p.id=i.pool_id ORDER BY i.id DESC LIMIT %d', $imports, $pools, $limit ), ARRAY_A );
		return array_map( array( $this, 'hydrate' ), is_array($rows)?$rows:array() );
	}

	public function find( int $id ): ?ImportRecord {
		global $wpdb;
		$imports=$this->table(); $pools=$this->pools_table();
		$row=$wpdb->get_row( $wpdb->prepare( 'SELECT i.*, p.name AS pool_name FROM %i i LEFT JOIN %i p ON p.id=i.pool_id WHERE i.id=%d', $imports, $pools, $id ), ARRAY_A );
		return is_array($row)?$this->hydrate($row):null;
	}

	public function mark_rolled_back( int $id ): bool {
		global $wpdb;
		$result=$wpdb->update($this->table(),array('status'=>'rolled_back','completed_at'=>current_time('mysql',true)),array('id'=>$id),array('%s','%s'),array('%d'));
		return false!==$result;
	}

	private function finish( int $id, string $status, int $total, int $imported, int $skipped, int $invalid ): bool {
		global $wpdb;
		$result=$wpdb->update($this->table(),array('status'=>$status,'total_rows'=>$total,'imported_rows'=>$imported,'skipped_rows'=>$skipped,'invalid_rows'=>$invalid,'completed_at'=>current_time('mysql',true)),array('id'=>$id),array('%s','%d','%d','%d','%d','%s'),array('%d'));
		return false!==$result;
	}

	/** @param array<string,mixed> $row */
	private function hydrate( array $row ): ImportRecord {
		return new ImportRecord((int)$row['id'],(int)$row['pool_id'],(string)($row['pool_name']??''),(string)$row['filename'],(string)$row['file_type'],(string)$row['status'],(int)$row['total_rows'],(int)$row['imported_rows'],(int)$row['skipped_rows'],(int)$row['invalid_rows'],(string)$row['created_at']);
	}
}
