<?php
/**
 * Shared Activity presentation and classification metadata.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Activity;

/**
 * Keeps Voucher Manager-owned Activity classification and registered
 * extension metadata in one source of truth.
 */
final class ActivityMetadata {

	/**
	 * @var array<string,array{family:string,tone:string}>
	 */
	private const CORE_EVENTS = array(
		'import.completed'             => array( 'family' => 'import', 'tone' => 'success' ),
		'import.failed'                => array( 'family' => 'import', 'tone' => 'error' ),
		'import.rolled_back'           => array( 'family' => 'import', 'tone' => 'success' ),
		'import.rollback_blocked'      => array( 'family' => 'import', 'tone' => 'warning' ),
		'distribution.completed'       => array( 'family' => 'distribution', 'tone' => 'success' ),
		'distribution.empty'           => array( 'family' => 'distribution', 'tone' => 'warning' ),
		'distribution.failed'          => array( 'family' => 'distribution', 'tone' => 'error' ),
		'admin.action_failed'          => array( 'family' => 'admin', 'tone' => 'error' ),
		'settings.updated'             => array( 'family' => 'settings', 'tone' => 'success' ),
		'activity.cleanup_completed'   => array( 'family' => 'all', 'tone' => 'success' ),
		'activity.cleanup_failed'      => array( 'family' => 'all', 'tone' => 'error' ),
		'plugin.installed'             => array( 'family' => 'all', 'tone' => 'neutral' ),
		'plugin.activated'             => array( 'family' => 'all', 'tone' => 'neutral' ),
		'plugin.deactivated'           => array( 'family' => 'all', 'tone' => 'neutral' ),
		'plugin.uninstalled'           => array( 'family' => 'all', 'tone' => 'neutral' ),
		'pool.created'                 => array( 'family' => 'pool', 'tone' => 'success' ),
		'pool.updated'                 => array( 'family' => 'pool', 'tone' => 'success' ),
		'pool.activated'               => array( 'family' => 'pool', 'tone' => 'success' ),
		'pool.deactivated'             => array( 'family' => 'pool', 'tone' => 'success' ),
		'pool.available_codes_deleted' => array( 'family' => 'pool', 'tone' => 'warning' ),
		'pool.deleted'                 => array( 'family' => 'pool', 'tone' => 'warning' ),
		'pool.delete_failed'           => array( 'family' => 'pool', 'tone' => 'error' ),
	);

	private const FAMILIES = array(
		'all',
		'import',
		'distribution',
		'pool',
		'settings',
		'admin',
	);

	private const TONES = array(
		'neutral',
		'success',
		'warning',
		'error',
	);

	/**
	 * @var array<string,array{
	 *   label:string,
	 *   family:string,
	 *   tone:string,
	 *   detail:(callable(array<string,mixed>):string)|null
	 * }>
	 */
	private static array $extension_events = array();

	/**
	 * Register metadata for an extension-owned Activity event type.
	 *
	 * Voucher Manager-owned event types cannot be overridden.
	 *
	 * @param callable(array<string,mixed>):string|null $detail Detail resolver.
	 */
	public static function register_extension(
		string $event_type,
		string $label,
		string $family,
		string $tone,
		?callable $detail = null
	): bool {
		$event_type = trim( $event_type );
		$label      = trim( $label );
		$family     = trim( $family );
		$tone       = trim( $tone );

		if (
			'' === $event_type
			|| '' === $label
			|| isset( self::CORE_EVENTS[ $event_type ] )
			|| isset( self::$extension_events[ $event_type ] )
			|| 1 !== preg_match( '/^[a-z][a-z0-9_]*(?:\.[a-z0-9_]+)+$/', $event_type )
			|| ! in_array( $family, self::FAMILIES, true )
			|| ! in_array( $tone, self::TONES, true )
		) {
			return false;
		}

		self::$extension_events[ $event_type ] = array(
			'label'  => $label,
			'family' => $family,
			'tone'   => $tone,
			'detail' => $detail,
		);

		return true;
	}

	/**
	 * Return an extension label, or null when the event is not registered.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public static function extension_label( string $event_type, array $context = array() ): ?string {
		unset( $context );

		return self::$extension_events[ $event_type ]['label'] ?? null;
	}

	/**
	 * Return an extension detail, or null when the event is not registered.
	 *
	 * @param array<string,mixed> $context Event context.
	 */
	public static function extension_detail( string $event_type, array $context = array() ): ?string {
		if ( ! isset( self::$extension_events[ $event_type ] ) ) {
			return null;
		}

		$resolver = self::$extension_events[ $event_type ]['detail'];

		if ( null === $resolver ) {
			return '';
		}

		try {
			return (string) $resolver( $context );
		} catch ( \Throwable ) {
			return '';
		}
	}

	/**
	 * Return the visual tone for a core, extension, or unknown event.
	 */
	public static function tone( string $event_type ): string {
		if ( isset( self::CORE_EVENTS[ $event_type ] ) ) {
			return self::CORE_EVENTS[ $event_type ]['tone'];
		}

		return self::$extension_events[ $event_type ]['tone'] ?? 'neutral';
	}


	/**
	 * Normalize an Activity family filter.
	 */
	public static function normalize_family( string $family ): string {
		return in_array( $family, self::FAMILIES, true ) ? $family : 'all';
	}

	/**
	 * Normalize an Activity outcome filter.
	 */
	public static function normalize_filter_tone( string $tone ): string {
		return in_array( $tone, array( 'all', 'success', 'warning', 'error' ), true )
			? $tone
			: 'all';
	}

	/**
	 * Return the shared SQL-oriented family classification descriptor.
	 *
	 * Core families retain the existing prefix semantics. Administration uses
	 * its existing explicit event type. Registered extension events are added
	 * explicitly to the selected family.
	 *
	 * @return array{prefix:?string,event_types:array<string>}
	 */
	public static function family_filter( string $family ): array {
		$family = self::normalize_family( $family );

		if ( 'all' === $family ) {
			return array(
				'prefix'      => null,
				'event_types' => array(),
			);
		}

		$core_events = 'admin' === $family
			? array( 'admin.action_failed' )
			: array();

		return array(
			'prefix'      => 'admin' === $family ? null : $family . '.',
			'event_types' => array_values(
				array_unique(
					array_merge(
						$core_events,
						self::extension_event_types_for_family( $family )
					)
				)
			),
		);
	}

	/**
	 * Return registered extension event types assigned to a family.
	 *
	 * @return array<string>
	 */
	public static function extension_event_types_for_family( string $family ): array {
		if ( 'all' === $family || ! in_array( $family, self::FAMILIES, true ) ) {
			return array();
		}

		$events = array();

		foreach ( self::$extension_events as $event_type => $metadata ) {
			if ( $family === $metadata['family'] ) {
				$events[] = $event_type;
			}
		}

		sort( $events );

		return $events;
	}

	/**
	 * Return all known event types assigned to a selectable outcome tone.
	 *
	 * @return array<string>
	 */
	public static function event_types_for_tone( string $tone ): array {
		if ( ! in_array( $tone, array( 'success', 'warning', 'error' ), true ) ) {
			return array();
		}

		$events = array();

		foreach ( self::CORE_EVENTS as $event_type => $metadata ) {
			if ( $tone === $metadata['tone'] ) {
				$events[] = $event_type;
			}
		}

		foreach ( self::$extension_events as $event_type => $metadata ) {
			if ( $tone === $metadata['tone'] ) {
				$events[] = $event_type;
			}
		}

		sort( $events );

		return $events;
	}
}
