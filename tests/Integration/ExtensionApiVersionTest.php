<?php
/**
 * Extension API version contract.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/voucher-manager.php';

if ( ! defined( 'VOUCHER_MANAGER_EXTENSION_API_VERSION' ) ) {
	fwrite( STDERR, "VOUCHER_MANAGER_EXTENSION_API_VERSION is not defined.\n" );
	exit( 1 );
}

if ( '1' !== VOUCHER_MANAGER_EXTENSION_API_VERSION ) {
	fwrite(
		STDERR,
		sprintf(
			"Expected Extension API version 1, got %s.\n",
			(string) VOUCHER_MANAGER_EXTENSION_API_VERSION
		)
	);
	exit( 1 );
}

echo "Extension API version contract OK.\n";
