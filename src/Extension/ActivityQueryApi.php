<?php
/**
 * Public Activity query extension contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Extension;

use VoucherManager\Activity\ActivityMetadata;
use VoucherManager\Database\TableStatus;

/**
 * Provides supported read-only access to active Activity History records.
 */
final class ActivityQueryApi {

	/**
	 * Returns one cursor-based batch of active Activity History records.
	 *
	 * Results are ordered newest first, matching the existing Activity History
	 * presentation. Pass the returned next_before_id into the next call to
	 * continue traversing the complete matching result set without depending
	 * on admin-page pagination.
	 *
	 * @return array{
	 *   events:array<int,array{
	 *     id:int,
	 *     event_type:string,
	 *     message:string,
	 *     context:string,
	 *     created_at:string
	 *   }>,
	 *   filters:array{family:string,tone:string},
	 *   before_id:int,
	 *   limit:int,
	 *   has_more:bool,
	 *   next_before_id:?int
	 * }
	 */
	public function query(
		string $family = 'all',
		string $tone = 'all',
		int $before_id = 0,
		int $limit = 250
	): array {
		global $wpdb;

		$family    = ActivityMetadata::normalize_family( $family );
		$tone      = ActivityMetadata::normalize_filter_tone( $tone );
		$before_id = max( 0, $before_id );
		$limit     = min( 1000, max( 1, $limit ) );

		$empty = array(
			'events'         => array(),
			'filters'        => array(
				'family' => $family,
				'tone'   => $tone,
			),
			'before_id'      => $before_id,
			'limit'          => $limit,
			'has_more'       => false,
			'next_before_id' => null,
		);

		$tables = new TableStatus();
		if ( ! $tables->is_healthy() ) {
			return $empty;
		}

		$names = $tables->names();
		$table = $names['logs'];

		$where = array( '1=1' );
		$args  = array();

		if ( $before_id > 0 ) {
			$where[] = 'id < %d';
			$args[]  = $before_id;
		}

		$this->add_family_filter( $where, $args, $family );

		$tone_events = ActivityMetadata::event_types_for_tone( $tone );
		if ( array() !== $tone_events ) {
			$where[] = 'event_type IN (' . implode( ',', array_fill( 0, count( $tone_events ), '%s' ) ) . ')';
			$args     = array_merge( $args, $tone_events );
		}

		$where_sql  = implode( ' AND ', $where );
		$query_args = array_merge( $args, array( $limit + 1 ) );

		$sql = "SELECT id, event_type, message, context, created_at
			FROM {$table}
			WHERE {$where_sql}
			ORDER BY id DESC
			LIMIT %d";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $query_args ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$has_more = count( $rows ) > $limit;
		if ( $has_more ) {
			$rows = array_slice( $rows, 0, $limit );
		}

		$events = array();
		foreach ( $rows as $row ) {
			$events[] = array(
				'id'         => absint( $row['id'] ?? 0 ),
				'event_type' => sanitize_text_field( (string) ( $row['event_type'] ?? '' ) ),
				'message'    => (string) ( $row['message'] ?? '' ),
				'context'    => (string) ( $row['context'] ?? '' ),
				'created_at' => sanitize_text_field( (string) ( $row['created_at'] ?? '' ) ),
			);
		}

		$next_before_id = null;
		if ( $has_more && array() !== $events ) {
			$last           = $events[ count( $events ) - 1 ];
			$next_before_id = $last['id'];
		}

		return array(
			'events'         => $events,
			'filters'        => array(
				'family' => $family,
				'tone'   => $tone,
			),
			'before_id'      => $before_id,
			'limit'          => $limit,
			'has_more'       => $has_more,
			'next_before_id' => $next_before_id,
		);
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
}
