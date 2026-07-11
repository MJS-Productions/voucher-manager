<?php
/** @var array<\VoucherManager\Domain\Pool\Pool> $pools */
/** @var array<\VoucherManager\Domain\Import\ImportRecord> $imports */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
$notice = isset($_GET['vm_notice']) ? sanitize_key(wp_unslash($_GET['vm_notice'])) : '';
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html__( 'Import Codes', 'voucher-manager' ); ?></h1>
	<?php if ( 'imported' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><strong><?php echo esc_html__( 'Import completed.', 'voucher-manager' ); ?></strong> <?php echo esc_html( sprintf( __( '%1$d imported, %2$d skipped, %3$d invalid (%4$d rows processed).', 'voucher-manager' ), absint($_GET['imported']??0), absint($_GET['skipped']??0), absint($_GET['invalid']??0), absint($_GET['total']??0) ) ); ?></p></div>
	<?php elseif ( 'rolled_back' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( __( 'Import rolled back. %d available codes removed.', 'voucher-manager' ), absint($_GET['deleted']??0) ) ); ?></p></div>
	<?php elseif ( in_array($notice,array('invalid_pool','import_error','rollback_blocked'),true) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( 'rollback_blocked' === $notice ? __( 'Rollback is unavailable because codes from this import have already been assigned.', 'voucher-manager' ) : __( 'The import could not be completed. Check the pool and file, then try again.', 'voucher-manager' ) ); ?></p></div>
	<?php endif; ?>

	<div class="voucher-manager__card voucher-manager__form">
		<h2><?php echo esc_html__( 'Upload a code file', 'voucher-manager' ); ?></h2>
		<p><?php echo esc_html__( 'Use a TXT file with one code per line or a CSV file whose first column contains the code. Maximum file size: 10 MB.', 'voucher-manager' ); ?></p>
		<?php if ( empty($pools) ) : ?>
			<p><a class="button button-primary" href="<?php echo esc_url(add_query_arg(array('page'=>'voucher-manager-pools','action'=>'new'),admin_url('admin.php'))); ?>"><?php echo esc_html__( 'Create a pool first', 'voucher-manager' ); ?></a></p>
		<?php else : ?>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="voucher_manager_import_codes">
			<?php wp_nonce_field('voucher_manager_import_codes'); ?>
			<p><label for="vm-pool"><strong><?php echo esc_html__( 'Destination pool', 'voucher-manager' ); ?></strong></label>
			<select id="vm-pool" name="pool_id" required><?php foreach($pools as $pool): ?><option value="<?php echo esc_attr((string)$pool->id()); ?>"><?php echo esc_html($pool->name()); ?></option><?php endforeach; ?></select></p>
			<p><label for="vm-code-file"><strong><?php echo esc_html__( 'TXT or CSV file', 'voucher-manager' ); ?></strong></label><input id="vm-code-file" type="file" name="code_file" accept=".txt,.csv,text/plain,text/csv" required></p>
			<?php submit_button(__('Import Codes','voucher-manager')); ?>
		</form>
		<?php endif; ?>
	</div>

	<h2 class="voucher-manager__section-title"><?php echo esc_html__( 'Recent imports', 'voucher-manager' ); ?></h2>
	<div class="voucher-manager__card voucher-manager__table-card">
	<table class="widefat striped"><thead><tr><th><?php echo esc_html__('File','voucher-manager'); ?></th><th><?php echo esc_html__('Pool','voucher-manager'); ?></th><th><?php echo esc_html__('Result','voucher-manager'); ?></th><th><?php echo esc_html__('Status','voucher-manager'); ?></th><th><?php echo esc_html__('Imported','voucher-manager'); ?></th><th><?php echo esc_html__('Actions','voucher-manager'); ?></th></tr></thead><tbody>
	<?php if(empty($imports)): ?><tr><td colspan="6"><?php echo esc_html__('No imports yet.','voucher-manager'); ?></td></tr><?php endif; ?>
	<?php foreach($imports as $import): $rollback=wp_nonce_url(add_query_arg(array('action'=>'voucher_manager_rollback_import','import_id'=>$import->id()),admin_url('admin-post.php')),'voucher_manager_rollback_import_'.$import->id()); ?>
	<tr><td><strong><?php echo esc_html($import->filename()); ?></strong><br><small><?php echo esc_html(strtoupper($import->file_type())); ?></small></td><td><?php echo esc_html($import->pool_name()); ?></td><td><?php echo esc_html(sprintf(__('%1$d imported / %2$d skipped / %3$d invalid','voucher-manager'),$import->imported_rows(),$import->skipped_rows(),$import->invalid_rows())); ?></td><td><span class="voucher-manager__badge <?php echo 'completed'===$import->status()?'is-active':'is-inactive'; ?>"><?php echo esc_html(ucwords(str_replace('_',' ',$import->status()))); ?></span></td><td><?php echo esc_html(mysql2date(get_option('date_format').' '.get_option('time_format'),$import->created_at().' UTC',true)); ?></td><td><?php if('completed'===$import->status()): ?><a class="voucher-manager__delete" href="<?php echo esc_url($rollback); ?>" onclick="return confirm('<?php echo esc_js(__('Remove all still-available codes from this import?','voucher-manager')); ?>');"><?php echo esc_html__('Roll back','voucher-manager'); ?></a><?php else: ?>—<?php endif; ?></td></tr>
	<?php endforeach; ?>
	</tbody></table></div>
</div>
