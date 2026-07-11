<?php
/**
 * Import administration controller.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use RuntimeException;
use Throwable;
use VoucherManager\Domain\Import\ImportService;
use VoucherManager\Infrastructure\WordPress\WpdbCodeRepository;
use VoucherManager\Infrastructure\WordPress\WpdbImportRepository;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;
use VoucherManager\Support\CodeFileParser;

final class ImportAdmin {
	private const MAX_FILE_SIZE = 10485760;
	private WpdbPoolRepository $pools;
	private WpdbImportRepository $imports;
	private ImportService $service;

	public function __construct() {
		$this->pools = new WpdbPoolRepository();
		$this->imports = new WpdbImportRepository();
		$this->service = new ImportService( $this->imports, new WpdbCodeRepository(), new WpdbLogRepository(), new CodeFileParser() );
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_voucher_manager_import_codes', array( $this, 'import' ) );
		add_action( 'admin_post_voucher_manager_rollback_import', array( $this, 'rollback' ) );
	}

	public function register_menu(): void {
		add_submenu_page( 'voucher-manager', __( 'Import Codes', 'voucher-manager' ), __( 'Import', 'voucher-manager' ), 'manage_options', 'voucher-manager-import', array( $this, 'render' ) );
	}

	public function render(): void {
		$this->guard();
		$pools = $this->pools->all();
		$imports = $this->imports->recent();
		$template = VOUCHER_MANAGER_PATH . 'templates/admin/import.php';
		if ( is_readable( $template ) ) { require $template; }
	}

	public function import(): void {
		$this->guard();
		check_admin_referer( 'voucher_manager_import_codes' );
		$pool_id = isset( $_POST['pool_id'] ) ? absint( $_POST['pool_id'] ) : 0;
		$pool = $this->pools->find( $pool_id );
		if ( null === $pool ) { $this->redirect( array( 'vm_notice'=>'invalid_pool' ) ); }

		try {
			$file = $this->validate_upload();
			$result = $this->service->import( $pool_id, $file['tmp_name'], $file['name'], $file['type'] );
			$this->redirect( array( 'vm_notice'=>'imported', 'total'=>$result->total(), 'imported'=>$result->imported(), 'skipped'=>$result->skipped(), 'invalid'=>$result->invalid() ) );
		} catch ( Throwable $exception ) {
			$this->redirect( array( 'vm_notice'=>'import_error' ) );
		}
	}

	public function rollback(): void {
		$this->guard();
		$import_id = isset( $_GET['import_id'] ) ? absint( $_GET['import_id'] ) : 0;
		check_admin_referer( 'voucher_manager_rollback_import_' . $import_id );
		try {
			$deleted = $this->service->rollback( $import_id );
			$this->redirect( array( 'vm_notice'=>'rolled_back', 'deleted'=>$deleted ) );
		} catch ( Throwable $exception ) {
			$this->redirect( array( 'vm_notice'=>'rollback_blocked' ) );
		}
	}

	/** @return array{name:string,tmp_name:string,type:string} */
	private function validate_upload(): array {
		if ( ! isset( $_FILES['code_file'] ) || ! is_array( $_FILES['code_file'] ) ) { throw new RuntimeException('Missing file.'); }
		$file = $_FILES['code_file'];
		$error = isset($file['error']) ? (int)$file['error'] : UPLOAD_ERR_NO_FILE;
		$size = isset($file['size']) ? (int)$file['size'] : 0;
		$name = isset($file['name']) ? sanitize_file_name((string)$file['name']) : '';
		$tmp = isset($file['tmp_name']) ? (string)$file['tmp_name'] : '';
		if ( UPLOAD_ERR_OK !== $error || 0 >= $size || self::MAX_FILE_SIZE < $size || ! is_uploaded_file( $tmp ) ) { throw new RuntimeException('Invalid upload.'); }
		$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( ! in_array( $extension, array('txt','csv'), true ) ) { throw new RuntimeException('Unsupported file type.'); }
		return array('name'=>$name,'tmp_name'=>$tmp,'type'=>$extension);
	}

	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You are not allowed to access this page.', 'voucher-manager' ) ); }
	}

	/** @param array<string,int|string> $args */
	private function redirect( array $args ): void {
		wp_safe_redirect( add_query_arg( array_merge( array('page'=>'voucher-manager-import'), $args ), admin_url('admin.php') ) );
		exit;
	}
}
