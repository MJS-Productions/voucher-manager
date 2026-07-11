<?php
/**
 * WordPress database pool repository.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Infrastructure\WordPress;

use VoucherManager\Domain\Pool\Pool;
use VoucherManager\Domain\Pool\PoolRepository;

final class WpdbPoolRepository implements PoolRepository {

	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'vm_pools';
	}

	private function codes_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'vm_codes';
	}

	public function all(): array {
		global $wpdb;
		$table = $this->table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY name ASC", ARRAY_A );
		return array_map( array( $this, 'hydrate' ), is_array( $rows ) ? $rows : array() );
	}

	public function find( int $id ): ?Pool {
		global $wpdb;
		$table = $this->table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A );
		return is_array( $row ) ? $this->hydrate( $row ) : null;
	}

	public function create( string $name, string $description, int $warning_threshold, bool $active ): int {
		global $wpdb;
		$now = current_time( 'mysql', true );
		$inserted = $wpdb->insert(
			$this->table(),
			array(
				'name'              => $name,
				'slug'              => $this->unique_slug( $name ),
				'description'       => $description,
				'warning_threshold' => $warning_threshold,
				'status'            => $active ? 'active' : 'inactive',
				'created_at'        => $now,
				'updated_at'        => $now,
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
		return false === $inserted ? 0 : (int) $wpdb->insert_id;
	}

	public function update( int $id, string $name, string $description, int $warning_threshold, bool $active ): bool {
		global $wpdb;
		$current = $this->find( $id );
		if ( null === $current ) { return false; }
		$result = $wpdb->update(
			$this->table(),
			array(
				'name'              => $name,
				'slug'              => $this->unique_slug( $name, $id ),
				'description'       => $description,
				'warning_threshold' => $warning_threshold,
				'status'            => $active ? 'active' : 'inactive',
				'updated_at'        => current_time( 'mysql', true ),
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%s', '%d', '%s', '%s' ),
			array( '%d' )
		);
		return false !== $result;
	}

	public function set_active( int $id, bool $active ): bool {
		global $wpdb;
		$result = $wpdb->update(
			$this->table(),
			array( 'status' => $active ? 'active' : 'inactive', 'updated_at' => current_time( 'mysql', true ) ),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		return false !== $result;
	}

	public function delete( int $id ): bool {
		global $wpdb;
		if ( 0 < $this->code_count( $id ) ) { return false; }
		return false !== $wpdb->delete( $this->table(), array( 'id' => $id ), array( '%d' ) );
	}

	public function code_count( int $id ): int {
		global $wpdb;
		$table = $this->codes_table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE pool_id = %d", $id ) );
	}

	/** @param array<string,mixed> $row */
	private function hydrate( array $row ): Pool {
		return new Pool(
			(int) $row['id'],
			(string) $row['name'],
			(string) $row['slug'],
			(string) $row['description'],
			(int) $row['warning_threshold'],
			(string) $row['status'],
			(string) $row['created_at'],
			(string) $row['updated_at']
		);
	}

	private function unique_slug( string $name, int $excluded_id = 0 ): string {
		global $wpdb;
		$table = $this->table();
		$base = sanitize_title( $name );
		if ( '' === $base ) { $base = 'pool'; }
		$slug = $base;
		$index = 2;
		do {
			if ( 0 < $excluded_id ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$found = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s AND id != %d", $slug, $excluded_id ) );
			} else {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$found = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $slug ) );
			}
			if ( null !== $found ) { $slug = $base . '-' . $index; ++$index; }
		} while ( null !== $found );
		return $slug;
	}
}
