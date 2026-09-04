<?php
/**
 * Voucher Manager administrator-only and delegated access policy.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Authorization;

use VoucherManager\Admin\Capabilities;
use VoucherManager\Extension\DelegatedAccessApi;

/**
 * Keeps standalone Voucher Manager administrator-only while allowing
 * compatible extensions to activate granular capability delegation.
 */
final class AccessPolicy {

	public function register(): void {
		add_filter( 'user_has_cap', array( $this, 'filter_user_capabilities' ), 10, 4 );
	}

	/**
	 * Remove Voucher Manager capabilities from non-administrators unless a
	 * compatible extension explicitly enabled delegation for this request.
	 *
	 * @param array<string,bool> $allcaps Effective capabilities.
	 * @param array<int,string>  $caps    Primitive capabilities being checked.
	 * @param array<int,mixed>   $args    Capability check arguments.
	 * @param object             $user    WordPress user object.
	 * @return array<string,bool>
	 */
	public function filter_user_capabilities( array $allcaps, array $caps, array $args, object $user ): array {
		unset( $caps, $args );

		if ( $this->user_is_administrator( $user ) || DelegatedAccessApi::is_enabled() ) {
			return $allcaps;
		}

		foreach ( Capabilities::all() as $capability ) {
			unset( $allcaps[ $capability ] );
		}

		return $allcaps;
	}

	public static function is_administrator(): bool {
		$user = wp_get_current_user();

		return is_object( $user )
			&& method_exists( $user, 'exists' )
			&& $user->exists()
			&& in_array( 'administrator', (array) $user->roles, true );
	}

	private function user_is_administrator( object $user ): bool {
		return in_array( 'administrator', (array) ( $user->roles ?? array() ), true );
	}
}
