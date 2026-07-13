<?php
/**
 * Import administration controller.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use RuntimeException;
use VoucherManager\Domain\Import\ImportResult;
use VoucherManager\Domain\Import\ImportService;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Infrastructure\WordPress\WpdbCodeRepository;
use VoucherManager\Infrastructure\WordPress\WpdbImportRepository;
use VoucherManager\Infrastructure\WordPress\WpdbLogRepository;
use VoucherManager\Infrastructure\WordPress\WpdbPoolRepository;
use VoucherManager\Support\CodeFileParser;
use VoucherManager\Support\ErrorBoundary;

/**
 * Handles manual code imports and rollbacks.
 */
final class ImportAdmin {

	private const MAX_FILE_SIZE = 10485760;

	private WpdbPoolRepository $pools;
	private WpdbImportRepository $imports;
	private ImportService $service;
	private OperationalLogger $logger;
	private ErrorBoundary $boundary;

	public function __construct() {
		$this->logger   = new OperationalLogger( new WpdbLogRepository() );
		$this->pools    = new WpdbPoolRepository();
		$this->imports  = new WpdbImportRepository();
		$this->service  = new ImportService(
			$this->imports,
			new WpdbCodeRepository(),
			$this->logger,
			new CodeFileParser()
		);
		$this->boundary = new ErrorBoundary( $this->logger );
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_voucher_manager_import_codes', array( $this, 'import' ) );
		add_action( 'admin_post_voucher_manager_rollback_import', array( $this, 'rollback' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			'voucher-manager',
			__( 'Import Codes', 'voucher-manager' ),
			__( 'Import', 'voucher-manager' ),
			'manage_options',
			'voucher-manager-import',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		$this->guard();

		$pools = $this->boundary->execute(
			fn(): array => $this->pools->all(),
			array(),
			array(
				'action' => 'import.render_pools',
				'source' => 'manual',
			)
		);
		$imports = $this->boundary->execute(
			fn(): array => $this->imports->recent(),
			array(),
			array(
				'action' => 'import.render_history',
				'source' => 'manual',
			)
		);

		$template = VOUCHER_MANAGER_PATH . 'templates/admin/import.php';

		if ( is_readable( $template ) ) {
			require $template;
		}
	}

	public function import(): void {
		$this->guard();
		check_admin_referer( 'voucher_manager_import_codes' );

		$pool_id = isset( $_POST['pool_id'] ) ? absint( $_POST['pool_id'] ) : 0;
		$pool    = $this->pools->find( $pool_id );

		if ( null === $pool ) {
			$this->redirect( array( 'vm_notice' => 'invalid_pool' ) );
		}

		$fallback = null;
		$result = $this->boundary->execute(
			function () use ( $pool_id ): ImportResult {
				$file = $this->validate_upload();

				return $this->service->import(
					$pool_id,
					$file['tmp_name'],
					$file['name'],
					$file['type']
				);
			},
			$fallback,
			array(
				'action'  => 'import.execute',
				'pool_id' => $pool_id,
				'source'  => 'manual',
			)
		);

		if ( ! $result instanceof ImportResult ) {
			$this->redirect( array( 'vm_notice' => 'import_error' ) );
		}

		$this->redirect(
			array(
				'vm_notice' => 'imported',
				'total'     => $result->total(),
				'imported'  => $result->imported(),
				'skipped'   => $result->skipped(),
				'invalid'   => $result->invalid(),
			)
		);
	}

	public function rollback(): void {
		$this->guard();

		$import_id = isset( $_GET['import_id'] ) ? absint( $_GET['import_id'] ) : 0;
		check_admin_referer( 'voucher_manager_rollback_import_' . $import_id );

		$deleted = $this->boundary->execute(
			fn(): int => $this->service->rollback( $import_id ),
			null,
			array(
				'action'    => 'import.rollback',
				'import_id' => $import_id,
				'source'    => 'manual',
			)
		);

		if ( ! is_int( $deleted ) ) {
			$this->logger->warning(
				OperationalEvent::IMPORT_ROLLBACK_BLOCKED,
				'Import rollback was blocked or failed.',
				array(
					'import_id' => $import_id,
					'source'    => 'manual',
				)
			);
			$this->redirect( array( 'vm_notice' => 'rollback_blocked' ) );
		}

		$this->redirect(
			array(
				'vm_notice' => 'rolled_back',
				'deleted'   => $deleted,
			)
		);
	}

	/**
	 * Validate the uploaded TXT or CSV file.
	 *
	 * @return array{name:string,tmp_name:string,type:string}
	 */
	private function validate_upload(): array {
		if ( ! isset( $_FILES['code_file'] ) || ! is_array( $_FILES['code_file'] ) ) {
			throw new RuntimeException( 'Missing file.' );
		}

		$file  = $_FILES['code_file'];
		$error = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
		$size  = isset( $file['size'] ) ? (int) $file['size'] : 0;
		$name  = isset( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : '';
		$tmp   = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';

		if (
			UPLOAD_ERR_OK !== $error
			|| 0 >= $size
			|| self::MAX_FILE_SIZE < $size
			|| ! is_uploaded_file( $tmp )
		) {
			throw new RuntimeException( 'Invalid upload.' );
		}

		$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

		if ( ! in_array( $extension, array( 'txt', 'csv' ), true ) ) {
			throw new RuntimeException( 'Unsupported file type.' );
		}

		return array(
			'name'     => $name,
			'tmp_name' => $tmp,
			'type'     => $extension,
		);
	}

	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You are not allowed to access this page.', 'voucher-manager' )
			);
		}
	}

	/**
	 * Redirect back to the import screen.
	 *
	 * @param array<string,int|string> $args Query arguments.
	 */
	private function redirect( array $args ): void {
		wp_safe_redirect(
			add_query_arg(
				array_merge(
					array( 'page' => 'voucher-manager-import' ),
					$args
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
