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

$admin = file_get_contents( $root . '/src/Admin/ImportAdmin.php' );
$template = file_get_contents( $root . '/templates/admin/import.php' );
$confirmation = file_get_contents( $root . '/templates/admin/import-rollback-confirmation.php' );
$composer = file_get_contents( $root . '/composer.json' );

$assert( str_contains( $template, 'What happens during import' ) && str_contains( $template, 'Duplicate codes are skipped' ), 'Import screen must explain file handling before upload.' );
$assert( str_contains( $template, 'available, %3$d total' ), 'Pool selection must include inventory context.' );
$assert( str_contains( $template, "'confirm-rollback'" ) && ! str_contains( $template, 'onclick="return confirm' ), 'Rollback must route through a dedicated review page without JavaScript confirm.' );
$assert( ! str_contains( $template, 'wp_nonce_url' ) && ! str_contains( $template, "'action'=>'voucher_manager_rollback_import'" ), 'Import history must not expose destructive rollback as a GET action.' );
$assert( str_contains( $confirmation, 'method="post"' ) && str_contains( $confirmation, 'confirm_rollback' ) && str_contains( $confirmation, 'required' ), 'Rollback confirmation must use POST and explicit acknowledgement.' );
$assert( str_contains( $admin, "isset( \$_POST['import_id'] )" ) && str_contains( $admin, 'check_admin_referer' ) && str_contains( $admin, "current_user_can( 'manage_options' )" ), 'Rollback execution must retain POST, nonce and capability protection.' );
$assert( str_contains( $admin, "isset( \$_POST['confirm_rollback'] )" ) && str_contains( $admin, "'rollback_confirmation_required'" ), 'Server must reject unacknowledged rollback requests.' );
$assert( str_contains( $confirmation, 'If any code from this import has already been distributed, the rollback is blocked' ), 'Confirmation must explain protected rollback semantics.' );
$assert( str_contains( $composer, '@test:import-experience' ) && strpos( $composer, '@test:import-experience' ) < strpos( $composer, '@build' ), 'Import Experience test must run before build.' );

echo "Import experience OK: guided upload, result clarity, rollback review and security boundary verified.\n";
