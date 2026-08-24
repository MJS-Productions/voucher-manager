<?php
/** @var array<\VoucherManager\Domain\Pool\Pool> $pools */
/** @var array<int,array{pool:\VoucherManager\Domain\Pool\Pool,total:int,available:int,assigned:int}> $pool_rows */
/** @var array<\VoucherManager\Domain\Import\ImportRecord> $imports */
/** @var \VoucherManager\Admin\ImportViewModel $view_model */
/** @var int $selected_pool_id */
/** @var array<int,int> $assigned_counts */
declare(strict_types=1);

use VoucherManager\Admin\Capabilities;

if ( ! defined( 'ABSPATH' ) ) { exit; }
$notice = isset( $_GET['vm_notice'] ) ? sanitize_key( wp_unslash( $_GET['vm_notice'] ) ) : '';
$can_manage_pools = current_user_can( Capabilities::MANAGE_POOLS );
$can_rollback     = current_user_can( Capabilities::ROLLBACK_IMPORTS );
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html__( 'Import Codes', 'mjs-productions-voucher-manager' ); ?></h1>

	<?php if ( 'imported' === $notice ) :
		$total = absint( $_GET['total'] ?? 0 );
		$imported = absint( $_GET['imported'] ?? 0 );
		$skipped = absint( $_GET['skipped'] ?? 0 );
		$invalid = absint( $_GET['invalid'] ?? 0 );
	?>
		<?php
		$summary_parts = array(
			sprintf(
				/* translators: %d: number of One-Time Codes added by the import */
				_n( '%d One-Time Code added', '%d One-Time Codes added', $imported, 'mjs-productions-voucher-manager' ),
				$imported
			),
			sprintf(
				/* translators: %d: number of import rows skipped as duplicates */
				_n( '%d row skipped', '%d rows skipped', $skipped, 'mjs-productions-voucher-manager' ),
				$skipped
			),
			sprintf(
				/* translators: %d: number of invalid import rows */
				_n( '%d invalid row', '%d invalid rows', $invalid, 'mjs-productions-voucher-manager' ),
				$invalid
			),
			sprintf(
				/* translators: %d: total number of import rows processed */
				_n( '%d row processed', '%d rows processed', $total, 'mjs-productions-voucher-manager' ),
				$total
			),
		);
		?>
		<div class="notice <?php echo 0 < $imported ? 'notice-success' : 'notice-warning'; ?> is-dismissible">
			<p><strong><?php echo esc_html( 0 < $imported ? __( 'Import completed.', 'mjs-productions-voucher-manager' ) : __( 'Import completed without adding codes.', 'mjs-productions-voucher-manager' ) ); ?></strong></p>
			<p><?php echo esc_html( implode( ', ', array_slice( $summary_parts, 0, 3 ) ) . ' — ' . $summary_parts[3] . '.' ); ?></p>
			<?php if ( 0 === $imported ) : ?><p><?php echo esc_html__( 'Check whether the file only contained duplicates, empty rows, or invalid values.', 'mjs-productions-voucher-manager' ); ?></p><?php endif; ?>
		</div>
	<?php elseif ( 'rolled_back' === $notice ) : ?>
		<?php $deleted = absint( $_GET['deleted'] ?? 0 ); ?>
		<div class="notice notice-success is-dismissible"><p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: number of available One-Time Codes removed by rollback */
					_n(
						'Import rolled back. %d available One-Time Code removed.',
						'Import rolled back. %d available One-Time Codes removed.',
						$deleted,
						'mjs-productions-voucher-manager'
					),
					$deleted
				)
			);
			?>
		</p></div>
	<?php elseif ( in_array( $notice, array( 'invalid_pool', 'import_error', 'rollback_blocked', 'rollback_confirmation_required', 'rollback_unavailable' ), true ) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php
			$message = match ( $notice ) {
				'rollback_blocked' => __( 'Rollback is unavailable because codes from this import have already been assigned. No codes were removed.', 'mjs-productions-voucher-manager' ),
				'rollback_confirmation_required' => __( 'Confirm the rollback acknowledgement before continuing.', 'mjs-productions-voucher-manager' ),
				'rollback_unavailable' => __( 'This import is not available for rollback.', 'mjs-productions-voucher-manager' ),
				default => __( 'The import could not be completed. Check the pool and file, then try again.', 'mjs-productions-voucher-manager' ),
			};
			echo esc_html( $message );
		?></p></div>
	<?php endif; ?>

	<div class="voucher-manager__import-grid">
		<section class="voucher-manager__card voucher-manager__form" aria-labelledby="voucher-manager-upload-title">
			<h2 id="voucher-manager-upload-title"><?php echo esc_html__( 'Upload a code file', 'mjs-productions-voucher-manager' ); ?></h2>
			<?php if ( empty( $pools ) ) : ?>
				<p><?php echo esc_html__( 'Create a pool before importing codes.', 'mjs-productions-voucher-manager' ); ?></p>
				<?php if ( $can_manage_pools ) : ?>
					<p><a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'new' ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html__( 'Create a pool first', 'mjs-productions-voucher-manager' ); ?></a></p>
				<?php endif; ?>
			<?php else : ?>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="voucher_manager_import_codes">
				<?php wp_nonce_field( 'voucher_manager_import_codes' ); ?>
				<p><label for="vm-pool"><strong><?php echo esc_html__( 'Destination pool', 'mjs-productions-voucher-manager' ); ?></strong></label>
				<select id="vm-pool" name="pool_id" required>
					<?php foreach ( $pool_rows as $row ) : $pool = $row['pool']; ?>
					<option value="<?php echo esc_attr( (string) $pool->id() ); ?>" <?php selected( $selected_pool_id, (int) $pool->id() ); ?>>
						<?php
							echo esc_html(
								sprintf(
									/* translators: 1: Pool name, 2: available One-Time Code count, 3: total One-Time Code count */
									__( '%1$s — %2$d available, %3$d total', 'mjs-productions-voucher-manager' ),
									$pool->name(),
									$row['available'],
									$row['total']
								)
							);
						?>
					</option>
					<?php endforeach; ?>
				</select></p>
				<p><label for="vm-code-file"><strong><?php echo esc_html__( 'TXT or CSV file', 'mjs-productions-voucher-manager' ); ?></strong></label><input id="vm-code-file" type="file" name="code_file" accept=".txt,.csv,text/plain,text/csv" required></p>
				<?php submit_button( __( 'Import Codes', 'mjs-productions-voucher-manager' ) ); ?>
			</form>
			<?php endif; ?>
		</section>

		<aside class="voucher-manager__card" aria-labelledby="voucher-manager-import-rules-title">
			<h2 id="voucher-manager-import-rules-title"><?php echo esc_html__( 'What happens during import', 'mjs-productions-voucher-manager' ); ?></h2>
			<ul class="voucher-manager__guidance-list">
				<li><?php echo esc_html__( 'TXT: place one code on each line.', 'mjs-productions-voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'CSV: codes are read from the first column; a common header is ignored.', 'mjs-productions-voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'Duplicate codes are skipped instead of being added twice.', 'mjs-productions-voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'Invalid rows are counted but not imported.', 'mjs-productions-voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'Maximum file size: 10 MB.', 'mjs-productions-voucher-manager' ); ?></li>
			</ul>
		</aside>
	</div>

	<h2 class="voucher-manager__section-title"><?php echo esc_html__( 'Recent imports', 'mjs-productions-voucher-manager' ); ?></h2>
	<div class="voucher-manager__card voucher-manager__table-card">
	<table class="widefat striped"><thead><tr><th><?php echo esc_html_x( 'File', 'Recent imports table column', 'mjs-productions-voucher-manager' ); ?></th><th><?php echo esc_html_x( 'Pool', 'Recent imports table column', 'mjs-productions-voucher-manager' ); ?></th><th><?php echo esc_html_x( 'Result', 'Recent imports table column', 'mjs-productions-voucher-manager' ); ?></th><th><?php echo esc_html_x( 'Status', 'Recent imports table column', 'mjs-productions-voucher-manager' ); ?></th><th><?php echo esc_html__( 'Imported', 'mjs-productions-voucher-manager' ); ?></th><th><?php echo esc_html_x( 'Actions', 'Recent imports table column', 'mjs-productions-voucher-manager' ); ?></th></tr></thead><tbody>
	<?php if ( empty( $imports ) ) : ?><tr><td colspan="6"><?php echo esc_html__( 'No imports yet.', 'mjs-productions-voucher-manager' ); ?></td></tr><?php endif; ?>
	<?php foreach ( $imports as $import ) :
		$review_url = add_query_arg( array( 'page' => 'voucher-manager-import', 'action' => 'confirm-rollback', 'import_id' => $import->id() ), admin_url( 'admin.php' ) );
		$tone = $view_model->status_tone( $import );
	?>
	<tr>
		<td><strong><?php echo esc_html( $import->filename() ); ?></strong><br><small><?php echo esc_html( strtoupper( $import->file_type() ) ); ?></small></td>
		<td><?php echo esc_html( '' !== $import->pool_name() ? $import->pool_name() : __( 'Deleted pool', 'mjs-productions-voucher-manager' ) ); ?></td>
		<td><?php echo esc_html( $view_model->result_summary( $import ) ); ?></td>
		<td><span class="voucher-manager__badge voucher-manager__badge--<?php echo esc_attr( $tone ); ?>"><?php echo esc_html( $view_model->status_label( $import ) ); ?></span></td>
		<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $import->created_at() . ' UTC', true ) ); ?></td>
		<td><?php if ( $can_rollback && $view_model->can_review_rollback( $import, $assigned_counts[ $import->id() ] ?? 0 ) ) : ?><a class="voucher-manager__delete" href="<?php echo esc_url( $review_url ); ?>"><?php echo esc_html__( 'Undo import', 'mjs-productions-voucher-manager' ); ?></a><?php else : ?>—<?php endif; ?></td>
	</tr>
	<?php endforeach; ?>
	</tbody></table></div>
</div>
