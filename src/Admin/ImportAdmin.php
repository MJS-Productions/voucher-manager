<?php
/**
 * Import administration controller.
 *
 * @package VoucherManager
 */

declare(strict_types=1);

namespace VoucherManager\Admin;

use RuntimeException;
use VoucherManager\Domain\Import\ImportRecord;
use VoucherManager\Domain\Import\ImportResult;
use VoucherManager\Domain\Import\ImportService;
use VoucherManager\Domain\Log\OperationalEvent;
use VoucherManager\Domain\Log\OperationalLogger;
use VoucherManager\Extension\InventoryChangedEvent;
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
	private WpdbCodeRepository $codes;
	private ImportService $service;
	private OperationalLogger $logger;
	private ErrorBoundary $boundary;
	private ImportViewModel $view_model;

	public function __construct() {
		$this->logger   = new OperationalLogger( new WpdbLogRepository() );
		$this->pools    = new WpdbPoolRepository();
		$this->imports  = new WpdbImportRepository();
		$this->codes    = new WpdbCodeRepository();
		$this->service  = new ImportService(
			$this->imports,
			$this->codes,
			$this->logger,
			new CodeFileParser()
		);
		$this->boundary   = new ErrorBoundary( $this->logger );
		$this->view_model = new ImportViewModel();
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_voucher_manager_import_codes', array( $this, 'import' ) );
		add_action( 'admin_post_voucher_manager_rollback_import', array( $this, 'rollback' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			'voucher-manager',
			__( 'Import Codes', 'mjs-productions-voucher-manager' ),
			_x( 'Import', 'admin menu label', 'mjs-productions-voucher-manager' ),
			Capabilities::IMPORT_CODES,
			'voucher-manager-import',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'confirm-rollback' === $action ) {
			$this->guard( Capabilities::ROLLBACK_IMPORTS );
			$this->render_rollback_confirmation();
			return;
		}

		$this->guard( Capabilities::IMPORT_CODES );

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

		$pool_rows = $this->boundary->execute(
			fn(): array => ( new PoolOverviewData() )->rows( $pools ),
			array(),
			array( 'action' => 'import.render_pool_inventory', 'source' => 'manual' )
		);
		$import_ids = array_map(
			static fn( $import ): int => $import->id(),
			$imports
		);
		$assigned_counts = $this->boundary->execute(
			fn(): array => $this->codes->assigned_counts_by_import( $import_ids ),
			array(),
			array( 'action' => 'import.render_rollback_availability', 'source' => 'manual' )
		);

		$view_model        = $this->view_model;
		$requested_pool_id = isset( $_GET['pool_id'] ) ? absint( $_GET['pool_id'] ) : 0;
		$selected_pool_id  = $view_model->selected_pool_id( $requested_pool_id, $pool_rows );

		$template = VOUCHER_MANAGER_PATH . 'templates/admin/import.php';

		if ( is_readable( $template ) ) {
			require $template;
		}
	}

	public function import(): void {
		$this->guard( Capabilities::IMPORT_CODES );
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

		if ( 0 < $result->imported() ) {
			InventoryChangedEvent::dispatch(
				$pool_id,
				InventoryChangedEvent::REASON_IMPORT
			);
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
		$this->guard( Capabilities::ROLLBACK_IMPORTS );

		$import_id = isset( $_POST['import_id'] ) ? absint( $_POST['import_id'] ) : 0;
		check_admin_referer( 'voucher_manager_rollback_import_' . $import_id );

		$acknowledged = isset( $_POST['confirm_rollback'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['confirm_rollback'] ) );
		if ( ! $acknowledged ) {
			$this->redirect( array( 'vm_notice' => 'rollback_confirmation_required' ) );
		}

		$rollback_import = $this->boundary->execute(
			fn() => $this->imports->find( $import_id ),
			null,
			array(
				'action'    => 'import.rollback_inventory_context',
				'import_id' => $import_id,
				'source'    => 'manual',
			)
		);

		$deleted = $this->boundary->execute(
			fn(): int|false => $this->service->rollback( $import_id ),
			null,
			array(
				'action'    => 'import.rollback',
				'import_id' => $import_id,
				'source'    => 'manual',
			)
		);

		if ( false === $deleted ) {
			$this->redirect( array( 'vm_notice' => 'rollback_blocked' ) );
		}

		if ( null === $deleted ) {
			$this->redirect( array( 'vm_notice' => 'import_error' ) );
		}

		if ( 0 < $deleted && $rollback_import instanceof ImportRecord ) {
			InventoryChangedEvent::dispatch(
				$rollback_import->pool_id(),
				InventoryChangedEvent::REASON_ROLLBACK
			);
		}

		$this->redirect( array( 'vm_notice' => 'rolled_back', 'deleted' => $deleted ) );
	}

	private function render_rollback_confirmation(): void {
		$import_id = isset( $_GET['import_id'] ) ? absint( $_GET['import_id'] ) : 0;
		$import = $this->boundary->execute(
			fn() => $this->imports->find( $import_id ),
			null,
			array( 'action' => 'import.render_rollback_confirmation', 'import_id' => $import_id, 'source' => 'manual' )
		);

		if ( ! $import instanceof ImportRecord || ! $this->view_model->can_review_rollback( $import ) ) {
			$this->redirect( array( 'vm_notice' => 'rollback_unavailable' ) );
		}

		$template = VOUCHER_MANAGER_PATH . 'templates/admin/import-rollback-confirmation.php';
		if ( is_readable( $template ) ) {
			require $template;
		}
	}

	/**
	 * Validate the uploaded TXT or CSV file.
	 *
	 * The import action verifies the admin nonce before this private helper is
	 * reached. Upload metadata cannot be sanitized as a whole because the
	 * temporary path must remain unchanged for is_uploaded_file(); each field
	 * is therefore validated or sanitized according to its type below.
	 *
	 * @return array{name:string,tmp_name:string,type:string}
	 */
	private function validate_upload(): array {
		// Nonce verification happens in import() before this helper is called.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_FILES['code_file'] ) || ! is_array( $_FILES['code_file'] ) ) {
			throw new RuntimeException( 'Missing file.' );
		}

		// See method documentation: individual upload fields are validated below.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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

	private function guard( string $capability ): void {
		if ( ! current_user_can( $capability ) ) {
			wp_die(
				esc_html__( 'You are not allowed to access this page.', 'mjs-productions-voucher-manager' )
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
