<?php
/** @var array<\VoucherManager\Domain\Pool\Pool> $pools */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
$key = 'voucher_manager_distribution_' . get_current_user_id();
$result = get_transient( $key );
if ( false !== $result ) { delete_transient( $key ); }
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html__( 'Distribution', 'voucher-manager' ); ?></h1>
	<?php if ( is_array( $result ) ) : ?>
		<?php if ( ! empty( $result['success'] ) ) : ?>
		<div class="notice notice-success"><p><strong><?php echo esc_html__( 'Code distributed.', 'voucher-manager' ); ?></strong></p></div>
		<div class="voucher-manager__card">
			<h2><?php echo esc_html__( 'Distributed code', 'voucher-manager' ); ?></h2>
			<p><code style="font-size:1.35em;"><?php echo esc_html( (string) $result['code'] ); ?></code></p>
			<p><?php echo esc_html( sprintf( __( '%d codes remain available in this pool.', 'voucher-manager' ), absint( $result['remaining'] ) ) ); ?></p>
		</div>
		<?php else : ?>
		<div class="notice notice-error"><p><?php echo esc_html( (string) ( $result['message'] ?? __( 'Distribution failed.', 'voucher-manager' ) ) ); ?></p></div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="voucher-manager__card voucher-manager__form">
		<h2><?php echo esc_html__( 'Distribute next available code', 'voucher-manager' ); ?></h2>
		<p><?php echo esc_html__( 'Select an active pool. The next available code will be atomically claimed and marked as assigned.', 'voucher-manager' ); ?></p>
		<?php if ( empty( $pools ) ) : ?>
		<p><?php echo esc_html__( 'No active pools are available.', 'voucher-manager' ); ?></p>
		<?php else : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="voucher_manager_distribute_code">
			<?php wp_nonce_field( 'voucher_manager_distribute_code' ); ?>
			<p><label for="vm-distribution-pool"><strong><?php echo esc_html__( 'Pool', 'voucher-manager' ); ?></strong></label>
			<select id="vm-distribution-pool" name="pool_id" required>
			<?php foreach ( $pools as $pool ) : ?>
				<option value="<?php echo esc_attr( (string) $pool->id() ); ?>"><?php echo esc_html( $pool->name() ); ?></option>
			<?php endforeach; ?>
			</select></p>
			<?php submit_button( __( 'Distribute Code', 'voucher-manager' ) ); ?>
		</form>
		<?php endif; ?>
	</div>
</div>
