<?php
/** @var array<\VoucherManager\Domain\Pool\Pool> $pools */
/** @var array<int,array{pool:\VoucherManager\Domain\Pool\Pool,total:int,available:int,assigned:int}> $pool_rows */
/** @var array<\VoucherManager\Domain\Import\ImportRecord> $imports */
/** @var \VoucherManager\Admin\ImportViewModel $view_model */
/** @var int $selected_pool_id */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
$notice = isset( $_GET['vm_notice'] ) ? sanitize_key( wp_unslash( $_GET['vm_notice'] ) ) : '';
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html__( 'Import Codes', 'voucher-manager' ); ?></h1>

	<?php if ( 'imported' === $notice ) :
		$total = absint( $_GET['total'] ?? 0 );
		$imported = absint( $_GET['imported'] ?? 0 );
		$skipped = absint( $_GET['skipped'] ?? 0 );
		$invalid = absint( $_GET['invalid'] ?? 0 );
	?>
		<div class="notice <?php echo 0 < $imported ? 'notice-success' : 'notice-warning'; ?> is-dismissible">
			<p><strong><?php echo esc_html( 0 < $imported ? __( 'Import completed.', 'voucher-manager' ) : __( 'Import completed without adding codes.', 'voucher-manager' ) ); ?></strong></p>
			<p><?php echo esc_html( sprintf( __( '%1$d codes added, %2$d skipped, %3$d invalid — %4$d rows processed.', 'voucher-manager' ), $imported, $skipped, $invalid, $total ) ); ?></p>
			<?php if ( 0 === $imported ) : ?><p><?php echo esc_html__( 'Check whether the file only contained duplicates, empty rows, or invalid values.', 'voucher-manager' ); ?></p><?php endif; ?>
		</div>
	<?php elseif ( 'rolled_back' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( __( 'Import rolled back. %d available codes removed.', 'voucher-manager' ), absint( $_GET['deleted'] ?? 0 ) ) ); ?></p></div>
	<?php elseif ( in_array( $notice, array( 'invalid_pool', 'import_error', 'rollback_blocked', 'rollback_confirmation_required', 'rollback_unavailable' ), true ) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php
			$message = match ( $notice ) {
				'rollback_blocked' => __( 'Rollback is unavailable because codes from this import have already been assigned. No codes were removed.', 'voucher-manager' ),
				'rollback_confirmation_required' => __( 'Confirm the rollback acknowledgement before continuing.', 'voucher-manager' ),
				'rollback_unavailable' => __( 'This import is not available for rollback.', 'voucher-manager' ),
				default => __( 'The import could not be completed. Check the pool and file, then try again.', 'voucher-manager' ),
			};
			echo esc_html( $message );
		?></p></div>
	<?php endif; ?>

	<div class="voucher-manager__import-grid">
		<section class="voucher-manager__card voucher-manager__form" aria-labelledby="voucher-manager-upload-title">
			<h2 id="voucher-manager-upload-title"><?php echo esc_html__( 'Upload a code file', 'voucher-manager' ); ?></h2>
			<?php if ( empty( $pools ) ) : ?>
				<p><?php echo esc_html__( 'Create a pool before importing codes.', 'voucher-manager' ); ?></p>
				<p><a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'new' ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html__( 'Create a pool first', 'voucher-manager' ); ?></a></p>
			<?php else : ?>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="voucher_manager_import_codes">
				<?php wp_nonce_field( 'voucher_manager_import_codes' ); ?>
				<p><label for="vm-pool"><strong><?php echo esc_html__( 'Destination pool', 'voucher-manager' ); ?></strong></label>
				<select id="vm-pool" name="pool_id" required>
					<?php foreach ( $pool_rows as $row ) : $pool = $row['pool']; ?>
					<option value="<?php echo esc_attr( (string) $pool->id() ); ?>" <?php selected( $selected_pool_id, (int) $pool->id() ); ?>><?php echo esc_html( sprintf( __( '%1$s — %2$d available, %3$d total', 'voucher-manager' ), $pool->name(), $row['available'], $row['total'] ) ); ?></option>
					<?php endforeach; ?>
				</select></p>
				<p><label for="vm-code-file"><strong><?php echo esc_html__( 'TXT or CSV file', 'voucher-manager' ); ?></strong></label><input id="vm-code-file" type="file" name="code_file" accept=".txt,.csv,text/plain,text/csv" required></p>
				<?php submit_button( __( 'Import Codes', 'voucher-manager' ) ); ?>
			</form>
			<?php endif; ?>
		</section>

		<aside class="voucher-manager__card" aria-labelledby="voucher-manager-import-rules-title">
			<h2 id="voucher-manager-import-rules-title"><?php echo esc_html__( 'What happens during import', 'voucher-manager' ); ?></h2>
			<ul class="voucher-manager__guidance-list">
				<li><?php echo esc_html__( 'TXT: place one code on each line.', 'voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'CSV: codes are read from the first column; a common header is ignored.', 'voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'Duplicate codes are skipped instead of being added twice.', 'voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'Empty or invalid rows are counted but not imported.', 'voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'Maximum file size: 10 MB.', 'voucher-manager' ); ?></li>
			</ul>
		</aside>
	</div>

	<h2 class="voucher-manager__section-title"><?php echo esc_html__( 'Recent imports', 'voucher-manager' ); ?></h2>
	<div class="voucher-manager__card voucher-manager__table-card">
	<table class="widefat striped"><thead><tr><th><?php echo esc_html__( 'File', 'voucher-manager' ); ?></th><th><?php echo esc_html__( 'Pool', 'voucher-manager' ); ?></th><th><?php echo esc_html__( 'Result', 'voucher-manager' ); ?></th><th><?php echo esc_html__( 'Status', 'voucher-manager' ); ?></th><th><?php echo esc_html__( 'Imported', 'voucher-manager' ); ?></th><th><?php echo esc_html__( 'Actions', 'voucher-manager' ); ?></th></tr></thead><tbody>
	<?php if ( empty( $imports ) ) : ?><tr><td colspan="6"><?php echo esc_html__( 'No imports yet.', 'voucher-manager' ); ?></td></tr><?php endif; ?>
	<?php foreach ( $imports as $import ) :
		$review_url = add_query_arg( array( 'page' => 'voucher-manager-import', 'action' => 'confirm-rollback', 'import_id' => $import->id() ), admin_url( 'admin.php' ) );
		$tone = $view_model->status_tone( $import );
	?>
	<tr>
		<td><strong><?php echo esc_html( $import->filename() ); ?></strong><br><small><?php echo esc_html( strtoupper( $import->file_type() ) ); ?></small></td>
		<td><?php echo esc_html( '' !== $import->pool_name() ? $import->pool_name() : __( 'Deleted pool', 'voucher-manager' ) ); ?></td>
		<td><?php echo esc_html( $view_model->result_summary( $import ) ); ?></td>
		<td><span class="voucher-manager__badge voucher-manager__badge--<?php echo esc_attr( $tone ); ?>"><?php echo esc_html( $view_model->status_label( $import ) ); ?></span></td>
		<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $import->created_at() . ' UTC', true ) ); ?></td>
		<td><?php if ( $view_model->can_review_rollback( $import ) ) : ?><a class="voucher-manager__delete" href="<?php echo esc_url( $review_url ); ?>"><?php echo esc_html__( 'Review rollback', 'voucher-manager' ); ?></a><?php else : ?>—<?php endif; ?></td>
	</tr>
	<?php endforeach; ?>
	</tbody></table></div>
</div>
