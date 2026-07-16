<?php
/** @var \VoucherManager\Domain\Import\ImportRecord $import */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
$back_url = add_query_arg( array( 'page' => 'voucher-manager-import' ), admin_url( 'admin.php' ) );
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html__( 'Undo import', 'voucher-manager' ); ?></h1>
	<div class="voucher-manager__card voucher-manager__form voucher-manager__danger-review">
		<h2><?php echo esc_html__( 'Undo this import?', 'voucher-manager' ); ?></h2>
		<dl class="voucher-manager__status-list">
			<div><dt><?php echo esc_html_x( 'File', 'Import source file label', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( $import->filename() ); ?></dd></div>
			<div><dt><?php echo esc_html_x( 'Pool', 'Import destination Pool label', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( $import->pool_name() ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'Originally added', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $import->imported_rows() ) ); ?></dd></div>
		</dl>
		<div class="notice notice-warning inline"><p><strong><?php echo esc_html__( 'This action cannot be undone.', 'voucher-manager' ); ?></strong> <?php echo esc_html__( 'All One-Time Codes added by this import will be permanently removed. If any of them has already been distributed, the rollback is blocked and no codes are removed.', 'voucher-manager' ); ?></p></div>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="voucher_manager_rollback_import">
			<input type="hidden" name="import_id" value="<?php echo esc_attr( (string) $import->id() ); ?>">
			<?php wp_nonce_field( 'voucher_manager_rollback_import_' . $import->id() ); ?>
			<?php $maximum_removal = $import->imported_rows(); ?>
			<p><label><input type="checkbox" name="confirm_rollback" value="1" required>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: total number of One-Time Codes added by this import */
						_n(
							'I understand that this permanently removes all %d One-Time Code added by this import.',
							'I understand that this permanently removes all %d One-Time Codes added by this import.',
							$maximum_removal,
							'voucher-manager'
						),
						$maximum_removal
					)
				);
				?>
			</label></p>
			<p><button type="submit" class="button button-secondary voucher-manager__delete"><?php echo esc_html__( 'Roll back import', 'voucher-manager' ); ?></button> <a class="button" href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html_x( 'Cancel', 'Cancel import rollback action', 'voucher-manager' ); ?></a></p>
		</form>
	</div>
</div>
