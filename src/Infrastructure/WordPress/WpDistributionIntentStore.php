<?php
/**
 * WordPress-backed one-use Distribution intent store.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Infrastructure\WordPress;

use RuntimeException;
use VoucherManager\Domain\Distribution\DistributionIntentStore;

/**
 * Stores short-lived opaque intents as non-autoloaded options and consumes
 * them with an affected-row check so the same intent cannot succeed twice.
 */
final class WpDistributionIntentStore implements DistributionIntentStore {

	private const OPTION_PREFIX = 'voucher_manager_distribution_intent_';
	private const TTL_SECONDS = 600;
	private const CLEANUP_LIMIT = 25;

	public function create( int $user_id ): string {
		if ( 1 > $user_id ) {
			throw new RuntimeException( 'Distribution intent requires an authenticated user.' );
		}

		$this->cleanup_expired();

		for ( $attempt = 0; $attempt < 3; ++$attempt ) {
			$token = bin2hex( random_bytes( 32 ) );
			$value = $this->value( $user_id, time() + self::TTL_SECONDS );

			if ( add_option( $this->option_name( $token ), $value, '', false ) ) {
				return $token;
			}
		}

		throw new RuntimeException( 'Distribution intent could not be created.' );
	}

	public function consume( string $token, int $user_id ): bool {
		global $wpdb;

		if ( 1 > $user_id || ! preg_match( '/^[a-f0-9]{64}$/', $token ) ) {
			return false;
		}

		$option_name = $this->option_name( $token );
		$value       = get_option( $option_name, false );

		if ( ! is_string( $value ) ) {
			return false;
		}

		$parts = explode( '|', $value, 2 );
		if ( 2 !== count( $parts ) ) {
			delete_option( $option_name );
			return false;
		}

		$stored_user_id = (int) $parts[0];
		$expires_at     = (int) $parts[1];

		if ( $stored_user_id !== $user_id || $expires_at < time() ) {
			if ( $expires_at < time() ) {
				delete_option( $option_name );
			}
			return false;
		}

		$deleted = $wpdb->delete(
			$wpdb->options,
			array(
				'option_name'  => $option_name,
				'option_value' => $value,
			),
			array( '%s', '%s' )
		);

		wp_cache_delete( $option_name, 'options' );

		return 1 === $deleted;
	}

	private function option_name( string $token ): string {
		return self::OPTION_PREFIX . hash( 'sha256', $token );
	}

	private function value( int $user_id, int $expires_at ): string {
		return $user_id . '|' . $expires_at;
	}

	private function cleanup_expired(): void {
		global $wpdb;

		$like = $wpdb->esc_like( self::OPTION_PREFIX ) . '%';
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				ORDER BY option_id ASC
				LIMIT %d",
				$like,
				self::CLEANUP_LIMIT
			),
			ARRAY_A
		);

		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$value = (string) ( $row['option_value'] ?? '' );
			$parts = explode( '|', $value, 2 );

			if ( 2 !== count( $parts ) || (int) $parts[1] < time() ) {
				delete_option( (string) ( $row['option_name'] ?? '' ) );
			}
		}
	}
}
