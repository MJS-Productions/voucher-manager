<?php
/**
 * WordPress-backed one-time Distribution result store.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Infrastructure\WordPress;

use VoucherManager\Domain\Distribution\DistributionResult;
use VoucherManager\Domain\Distribution\DistributionResultStore;

/**
 * Stores unique, owner-scoped, consume-once Distribution results.
 */
final class WpDistributionResultStore implements DistributionResultStore {

	private const RESULT_PREFIX = 'voucher_manager_distribution_result_';
	private const INTENT_MAP_PREFIX = 'voucher_manager_distribution_result_intent_';
	private const TTL_SECONDS = 60;

	public function store( string $intent_token, int $user_id, DistributionResult $result, int $pool_id ): ?string {
		if ( 1 > $user_id || ! preg_match( '/^[a-f0-9]{64}$/', $intent_token ) ) {
			return null;
		}

		for ( $attempt = 0; $attempt < 3; ++$attempt ) {
			$result_token = bin2hex( random_bytes( 32 ) );
			$payload      = array(
				'user_id'    => $user_id,
				'expires_at' => time() + self::TTL_SECONDS,
				'success'    => $result->success(),
				'code'       => $result->code(),
				'message'    => $result->message(),
				'remaining'  => $result->remaining(),
				'pool_id'    => $pool_id,
			);

			if ( ! add_option( $this->result_option( $result_token ), $payload, '', false ) ) {
				continue;
			}

			$map_value = array(
				'user_id'    => $user_id,
				'expires_at' => time() + self::TTL_SECONDS,
				'payload'    => $payload,
			);

			if ( add_option( $this->intent_map_option( $intent_token ), $map_value, '', false ) ) {
				return $result_token;
			}

			delete_option( $this->result_option( $result_token ) );
		}

		return null;
	}

	public function consume( string $result_token, int $user_id ): ?array {
		global $wpdb;

		if ( 1 > $user_id || ! preg_match( '/^[a-f0-9]{64}$/', $result_token ) ) {
			return null;
		}

		$option_name = $this->result_option( $result_token );
		$payload     = get_option( $option_name, false );

		if ( ! is_array( $payload ) || ! $this->payload_belongs_to_user( $payload, $user_id ) ) {
			return null;
		}

		if ( (int) ( $payload['expires_at'] ?? 0 ) < time() ) {
			delete_option( $option_name );
			return null;
		}

		$serialized = maybe_serialize( $payload );
		$deleted    = $wpdb->delete(
			$wpdb->options,
			array(
				'option_name'  => $option_name,
				'option_value' => $serialized,
			),
			array( '%s', '%s' )
		);

		wp_cache_delete( $option_name, 'options' );

		if ( 1 !== $deleted ) {
			return null;
		}

		return array(
			'success'   => ! empty( $payload['success'] ),
			'code'      => is_string( $payload['code'] ?? null ) ? $payload['code'] : null,
			'message'   => is_string( $payload['message'] ?? null ) ? $payload['message'] : '',
			'remaining' => isset( $payload['remaining'] ) ? (int) $payload['remaining'] : null,
			'pool_id'   => (int) ( $payload['pool_id'] ?? 0 ),
		);
	}

	public function create_delivery_for_intent( string $intent_token, int $user_id ): ?string {
		if ( 1 > $user_id || ! preg_match( '/^[a-f0-9]{64}$/', $intent_token ) ) {
			return null;
		}

		$option_name = $this->intent_map_option( $intent_token );
		$map         = get_option( $option_name, false );

		if ( ! is_array( $map ) || (int) ( $map['user_id'] ?? 0 ) !== $user_id ) {
			return null;
		}

		if ( (int) ( $map['expires_at'] ?? 0 ) < time() ) {
			delete_option( $option_name );
			return null;
		}

		$payload = $map['payload'] ?? null;
		if ( ! is_array( $payload ) || ! $this->payload_belongs_to_user( $payload, $user_id ) ) {
			return null;
		}

		for ( $attempt = 0; $attempt < 3; ++$attempt ) {
			$result_token = bin2hex( random_bytes( 32 ) );

			if ( add_option( $this->result_option( $result_token ), $payload, '', false ) ) {
				return $result_token;
			}
		}

		return null;
	}

	private function result_option( string $token ): string {
		return self::RESULT_PREFIX . hash( 'sha256', $token );
	}

	private function intent_map_option( string $intent_token ): string {
		return self::INTENT_MAP_PREFIX . hash( 'sha256', $intent_token );
	}

	/** @param array<string,mixed> $payload */
	private function payload_belongs_to_user( array $payload, int $user_id ): bool {
		return (int) ( $payload['user_id'] ?? 0 ) === $user_id;
	}
}
