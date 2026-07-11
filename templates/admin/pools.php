<?php
/** @var array<\VoucherManager\Domain\Pool\Pool> $pools */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
$notice = isset( $_GET['vm_notice'] ) ? sanitize_key( wp_unslash( $_GET['vm_notice'] ) ) : '';
$messages = array(
	'created' => __( 'Your first voucher pool has been created.', 'voucher-manager' ),
	'updated' => __( 'Pool updated.', 'voucher-manager' ),
	'deleted' => __( 'Pool deleted.', 'voucher-manager' ),
	'status' => __( 'Pool status updated.', 'voucher-manager' ),
	'invalid' => __( 'Please enter a pool name.', 'voucher-manager' ),
	'error' => __( 'The operation could not be completed.', 'voucher-manager' ),
	'delete_blocked' => __( 'This pool cannot be deleted while it contains codes.', 'voucher-manager' ),
);
?>
<div class="wrap voucher-manager">
	<h1 class="wp-heading-inline"><?php echo esc_html__( 'Pools', 'voucher-manager' ); ?></h1>
	<a class="page-title-action" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'new' ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html__( 'Add New Pool', 'voucher-manager' ); ?></a>
	<hr class="wp-header-end">
	<?php if ( isset( $messages[ $notice ] ) ) : ?>
		<div class="notice <?php echo in_array( $notice, array( 'invalid', 'error', 'delete_blocked' ), true ) ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $messages[ $notice ] ); ?></p></div>
	<?php endif; ?>
	<div class="voucher-manager__card voucher-manager__table-card">
		<table class="widefat striped">
			<thead><tr><th><?php echo esc_html__( 'Name', 'voucher-manager' ); ?></th><th><?php echo esc_html__( 'Warning threshold', 'voucher-manager' ); ?></th><th><?php echo esc_html__( 'Status', 'voucher-manager' ); ?></th><th><?php echo esc_html__( 'Actions', 'voucher-manager' ); ?></th></tr></thead>
			<tbody>
			<?php if ( empty( $pools ) ) : ?><tr><td colspan="4"><?php echo esc_html__( 'No pools yet. Create your first pool to get started.', 'voucher-manager' ); ?></td></tr><?php endif; ?>
			<?php foreach ( $pools as $pool ) :
				$edit_url = add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'edit', 'pool_id' => $pool->id() ), admin_url( 'admin.php' ) );
				$toggle_url = wp_nonce_url( add_query_arg( array( 'action' => 'voucher_manager_toggle_pool', 'pool_id' => $pool->id() ), admin_url( 'admin-post.php' ) ), 'voucher_manager_toggle_pool_' . $pool->id() );
				$delete_url = wp_nonce_url( add_query_arg( array( 'action' => 'voucher_manager_delete_pool', 'pool_id' => $pool->id() ), admin_url( 'admin-post.php' ) ), 'voucher_manager_delete_pool_' . $pool->id() );
			?>
			<tr><td><strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $pool->name() ); ?></a></strong><br><small><?php echo esc_html( $pool->description() ); ?></small></td><td><?php echo esc_html( number_format_i18n( $pool->warning_threshold() ) ); ?></td><td><span class="voucher-manager__badge <?php echo $pool->is_active() ? 'is-active' : 'is-inactive'; ?>"><?php echo esc_html( $pool->is_active() ? __( 'Active', 'voucher-manager' ) : __( 'Inactive', 'voucher-manager' ) ); ?></span></td><td><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html__( 'Edit', 'voucher-manager' ); ?></a> · <a href="<?php echo esc_url( $toggle_url ); ?>"><?php echo esc_html( $pool->is_active() ? __( 'Deactivate', 'voucher-manager' ) : __( 'Activate', 'voucher-manager' ) ); ?></a> · <a class="voucher-manager__delete" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this pool?', 'voucher-manager' ) ); ?>');"><?php echo esc_html__( 'Delete', 'voucher-manager' ); ?></a></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
