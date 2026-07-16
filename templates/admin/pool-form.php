<?php
/** @var \VoucherManager\Domain\Pool\Pool|null $pool */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
$is_edit = null !== $pool;
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html( $is_edit ? __( 'Edit Pool', 'voucher-manager' ) : __( 'Add New Pool', 'voucher-manager' ) ); ?></h1>
	<form class="voucher-manager__card voucher-manager__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="voucher_manager_save_pool">
		<input type="hidden" name="pool_id" value="<?php echo esc_attr( $is_edit ? (string) $pool->id() : '0' ); ?>">
		<?php wp_nonce_field( 'voucher_manager_save_pool' ); ?>
		<p><label for="vm-name"><strong><?php echo esc_html__( 'Name', 'voucher-manager' ); ?></strong></label><input id="vm-name" name="name" type="text" class="regular-text" maxlength="190" required value="<?php echo esc_attr( $is_edit ? $pool->name() : '' ); ?>"></p>
		<p><label for="vm-description"><strong><?php echo esc_html__( 'Description', 'voucher-manager' ); ?></strong></label><textarea id="vm-description" name="description" class="large-text" rows="5"><?php echo esc_textarea( $is_edit ? $pool->description() : '' ); ?></textarea></p>
		<p><label for="vm-threshold"><strong><?php echo esc_html__( 'Low-stock warning threshold', 'voucher-manager' ); ?></strong></label><input id="vm-threshold" name="warning_threshold" type="number" min="0" step="1" value="<?php echo esc_attr( $is_edit ? (string) $pool->warning_threshold() : '10' ); ?>"><span class="description"><?php echo esc_html__( 'Notifications will use this value in a future Pro extension.', 'voucher-manager' ); ?></span></p>
		<p><label><input name="active" type="checkbox" value="1" <?php checked( ! $is_edit || $pool->is_active() ); ?>> <?php echo esc_html__( 'Pool is active', 'voucher-manager' ); ?></label></p>
		<?php submit_button( $is_edit ? __( 'Update Pool', 'voucher-manager' ) : __( 'Create Pool', 'voucher-manager' ) ); ?>
		<a class="button" href="<?php echo esc_url( add_query_arg( 'page', 'voucher-manager-pools', admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html_x( 'Cancel', 'Cancel Pool editing action', 'voucher-manager' ); ?></a>
	</form>
</div>
