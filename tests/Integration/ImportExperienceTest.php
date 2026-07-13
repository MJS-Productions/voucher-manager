<?php
/** @package VoucherManager */
declare(strict_types=1);
$root = dirname( __DIR__, 2 );
spl_autoload_register( static function ( string $class ) use ( $root ): void { $prefix='VoucherManager\\'; if ( str_starts_with( $class, $prefix ) ) { $file=$root.'/src/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php'; if ( is_readable($file) ) { require_once $file; } } } );
if ( ! function_exists( '__' ) ) { function __( string $text, string $domain = '' ): string { unset($domain); return $text; } }
$assert = static function ( bool $condition, string $message ): void { if ( ! $condition ) { throw new RuntimeException( 'Import experience assertion failed: ' . $message ); } };

$record = new VoucherManager\Domain\Import\ImportRecord( 5, 7, 'Summer', 'codes.csv', 'csv', 'completed', 12, 8, 3, 1, '2026-07-13 10:00:00' );
$view = new VoucherManager\Admin\ImportViewModel();
$assert( 'Completed' === $view->status_label( $record ), 'Completed status label changed.' );
$assert( 'success' === $view->status_tone( $record ), 'Completed status tone changed.' );
$assert( str_contains( $view->result_summary( $record ), '8 added' ) && str_contains( $view->result_summary( $record ), '12 rows processed' ), 'Result summary must explain added and processed counts.' );
$assert( $view->can_review_rollback( $record ), 'Completed imports with added codes must expose rollback review.' );
$rolled_back = new VoucherManager\Domain\Import\ImportRecord( 6, 7, 'Summer', 'old.txt', 'txt', 'rolled_back', 2, 2, 0, 0, '2026-07-13 10:00:00' );
$assert( ! $view->can_review_rollback( $rolled_back ), 'Rolled-back imports must not offer rollback again.' );

$pool_a = new VoucherManager\Domain\Pool\Pool( 3, 'Pool3', 'pool3', '', 10, 'active', '', '' );
$pool_b = new VoucherManager\Domain\Pool\Pool( 7, 'Pool7', 'pool7', '', 10, 'active', '', '' );
$pool_rows = array(
	array( 'pool' => $pool_a, 'total' => 0, 'available' => 0, 'assigned' => 0 ),
	array( 'pool' => $pool_b, 'total' => 5, 'available' => 5, 'assigned' => 0 ),
);
$assert( 3 === $view->selected_pool_id( 3, $pool_rows ), 'A valid pool navigation context must be preserved as the import preselection.' );
$assert( 0 === $view->selected_pool_id( 99, $pool_rows ), 'An unknown pool ID must not become a trusted import preselection.' );
$assert( 0 === $view->selected_pool_id( 0, $pool_rows ), 'Missing pool context must retain the normal dropdown fallback.' );

$admin = file_get_contents( $root . '/src/Admin/ImportAdmin.php' );
$template = file_get_contents( $root . '/templates/admin/import.php' );
$confirmation = file_get_contents( $root . '/templates/admin/import-rollback-confirmation.php' );
$pools_template = file_get_contents( $root . '/templates/admin/pools.php' );
$composer = file_get_contents( $root . '/composer.json' );

$assert( str_contains( $template, 'What happens during import' ) && str_contains( $template, 'Duplicate codes are skipped' ), 'Import screen must explain file handling before upload.' );
$assert( str_contains( $template, 'available, %3$d total' ), 'Pool selection must include inventory context.' );
$assert( str_contains( $pools_template, "'page'    => 'voucher-manager-import'" ) && str_contains( $pools_template, "'pool_id' => \$pool_id" ), 'Pool Import Codes actions must carry the source pool as navigation context.' );
$assert( str_contains( $admin, '$requested_pool_id' ) && str_contains( $admin, 'selected_pool_id( $requested_pool_id, $pool_rows )' ), 'Import rendering must validate requested pool context against loaded pools.' );
$assert( str_contains( $template, 'selected( $selected_pool_id, (int) $pool->id() )' ), 'The validated source pool must be preselected without locking the dropdown.' );

$assert( str_contains( $template, "'confirm-rollback'" ) && ! str_contains( $template, 'onclick="return confirm' ), 'Rollback must route through a dedicated review page without JavaScript confirm.' );
$assert( ! str_contains( $template, 'wp_nonce_url' ) && ! str_contains( $template, "'action'=>'voucher_manager_rollback_import'" ), 'Import history must not expose destructive rollback as a GET action.' );
$assert( str_contains( $confirmation, 'method="post"' ) && str_contains( $confirmation, 'confirm_rollback' ) && str_contains( $confirmation, 'required' ), 'Rollback confirmation must use POST and explicit acknowledgement.' );
$assert( str_contains( $admin, "isset( \$_POST['import_id'] )" ) && str_contains( $admin, 'check_admin_referer' ) && str_contains( $admin, "current_user_can( 'manage_options' )" ), 'Rollback execution must retain POST, nonce and capability protection.' );
$assert( str_contains( $admin, "isset( \$_POST['confirm_rollback'] )" ) && str_contains( $admin, "'rollback_confirmation_required'" ), 'Server must reject unacknowledged rollback requests.' );
$assert( str_contains( $confirmation, 'If any code from this import has already been distributed, the rollback is blocked' ), 'Confirmation must explain protected rollback semantics.' );
$assert( str_contains( $composer, '@test:import-experience' ) && strpos( $composer, '@test:import-experience' ) < strpos( $composer, '@build' ), 'Import Experience test must run before build.' );

echo "Import experience OK: guided upload, result clarity, rollback review and security boundary verified.\n";
