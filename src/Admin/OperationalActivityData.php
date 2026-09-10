<?php
/**
 * Operational activity data provider.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use VoucherManager\Activity\ActivityMetadata;
use VoucherManager\Database\TableStatus;

/**
 * Loads a filtered, paginated operational event history.
 */
final class OperationalActivityData {

	/**
	 * @return array{
	 *   events:array<int,array<string,mixed>>,
	 *   total:int,
	 *   page:int,
	 *   per_page:int,
	 *   pages:int,
	 *   filters:array{family:string,tone:string},
	 *   counts:array{all:int,attention:int,error:int}
	 * }
	 */
	public function get( string $family = 'all', string $tone = 'all', int $page = 1, int $per_page = 20 ): array {
		global $wpdb;

		$tables = new TableStatus();
		$names  = $tables->names();

		$family   = $this->normalize_family( $family );
		$tone     = $this->normalize_tone( $tone );
		$page     = max( 1, $page );
		$per_page = min( 50, max( 10, $per_page ) );

		if ( ! $tables->is_healthy() ) {
			return array(
				'events'   => array(),
				'total'    => 0,
				'page'     => 1,
				'per_page' => $per_page,
				'pages'    => 1,
				'filters'  => array( 'family' => $family, 'tone' => $tone ),
				'counts'   => array( 'all' => 0, 'attention' => 0, 'error' => 0 ),
			);
		}

		$where = array( '1=1' );
		$args  = array();

		$this->add_family_filter( $where, $args, $family );

		$tone_events = ActivityMetadata::event_types_for_tone( $tone );
		if ( array() !== $tone_events ) {
			$where[] = 'event_type IN (' . implode( ',', array_fill( 0, count( $tone_events ), '%s' ) ) . ')';
			$args     = array_merge( $args, $tone_events );
		}

		$where_sql = implode( ' AND ', $where );
		$table     = $names['logs'];

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( array() === $args ? $count_sql : $wpdb->prepare( $count_sql, $args ) );

		$pages  = max( 1, (int) ceil( $total / $per_page ) );
		$page   = min( $page, $pages );
		$offset = ( $page - 1 ) * $per_page;

		$list_args = array_merge( $args, array( $per_page, $offset ) );
		$list_sql  = "SELECT id, event_type, message, context, created_at
			FROM {$table}
			WHERE {$where_sql}
			ORDER BY id DESC
			LIMIT %d OFFSET %d";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_args ), ARRAY_A );

		$events = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$context  = json_decode( (string) ( $row['context'] ?? '' ), true );
			$events[] = array(
				'id'         => absint( $row['id'] ?? 0 ),
				'event_type' => sanitize_text_field( (string) ( $row['event_type'] ?? '' ) ),
				'context'    => is_array( $context ) ? $context : array(),
				'created_at' => sanitize_text_field( (string) ( $row['created_at'] ?? '' ) ),
			);
		}

		$error_events   = ActivityMetadata::event_types_for_tone( 'error' );
		$warning_events = ActivityMetadata::event_types_for_tone( 'warning' );
		$all_count      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$error          = $this->count_events( $table, $error_events );
		$attention      = $this->count_events( $table, array_merge( $error_events, $warning_events ) );

		return array(
			'events'   => $events,
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
			'pages'    => $pages,
			'filters'  => array( 'family' => $family, 'tone' => $tone ),
			'counts'   => array( 'all' => $all_count, 'attention' => $attention, 'error' => $error ),
		);
	}


	private function normalize_family( string $family ): string {
		return ActivityMetadata::normalize_family( $family );
	}

	private function normalize_tone( string $tone ): string {
		return ActivityMetadata::normalize_filter_tone( $tone );
	}

	/**
	 * Add the existing core family filter plus explicitly registered extension events.
	 *
	 * @param array<int,string> $where SQL clauses.
	 * @param array<int,string> $args  Prepared SQL arguments.
	 */
	private function add_family_filter( array &$where, array &$args, string $family ): void {
		global $wpdb;

		if ( 'all' === $family ) {
			return;
		}

		$filter = ActivityMetadata::family_filter( $family );
		$parts  = array();

		if ( null !== $filter['prefix'] ) {
			$parts[] = 'event_type LIKE %s';
			$args[]  = $wpdb->esc_like( $filter['prefix'] ) . '%';
		}

		if ( array() !== $filter['event_types'] ) {
			$parts[] = 'event_type IN (' . implode( ',', array_fill( 0, count( $filter['event_types'] ), '%s' ) ) . ')';
			$args     = array_merge( $args, $filter['event_types'] );
		}

		if ( array() !== $parts ) {
			$where[] = '(' . implode( ' OR ', $parts ) . ')';
		}
	}

	/**
	 * @param array<string> $events Event names.
	 */
	private function count_events( string $table, array $events ): int {
		global $wpdb;

		if ( array() === $events ) {
			return 0;
		}

		$placeholders = implode( ',', array_fill( 0, count( $events ), '%s' ) );
		$sql          = "SELECT COUNT(*) FROM {$table} WHERE event_type IN ({$placeholders})";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $events ) );
	}
}
