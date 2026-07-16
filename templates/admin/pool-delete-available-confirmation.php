<?php
/** @package VoucherManager */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/** @var \VoucherManager\Domain\Pool\Pool $pool */
/** @var array{total:int,available:int,assigned:int,imports:int}|null $summary */
if ( null === $summary ) { return; }
$notice = isset( $_GET['vm_notice'] ) ? sanitize_key( wp_unslash( $_GET['vm_notice'] ) ) : '';
$danger_url = add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'danger-zone', 'pool_id' => $pool->id() ), admin_url( 'admin.php' ) );
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html__( 'Confirm available-code deletion', 'voucher-manager' ); ?></h1>
	<p><a href="<?php echo esc_url( $danger_url ); ?>">&larr; <?php echo esc_html__( 'Back to Pool Danger Zone', 'voucher-manager' ); ?></a></p>
	<?php if ( 'confirmation_required' === $notice ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'You must explicitly acknowledge the permanent deletion before continuing.', 'voucher-manager' ); ?></p></div><?php endif; ?>
	<?php if ( 'delete_failed' === $notice ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'Available-code deletion failed. No additional action was performed.', 'voucher-manager' ); ?></p></div><?php endif; ?>
	<section class="voucher-manager__card voucher-manager__danger-zone">
		<h2><?php echo esc_html( $pool->name() ); ?></h2>
		<p><strong><?php echo esc_html__( 'This action permanently deletes unused codes.', 'voucher-manager' ); ?></strong></p>
		<dl>
			<dt><?php echo esc_html__( 'Available One-Time Codes to delete', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['available'] ) ); ?></dd>
			<dt><?php echo esc_html__( 'Distributed codes preserved', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['assigned'] ) ); ?></dd>
			<dt><?php echo esc_html__( 'Pool preserved', 'voucher-manager' ); ?></dt><dd><?php echo esc_html__( 'Yes', 'voucher-manager' ); ?></dd>
			<dt><?php echo esc_html__( 'Import and operational history preserved', 'voucher-manager' ); ?></dt><dd><?php echo esc_html__( 'Yes', 'voucher-manager' ); ?></dd>
		</dl>
		<?php if ( 0 < $summary['available'] ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'voucher_manager_delete_available_codes_' . $pool->id() ); ?>
				<input type="hidden" name="action" value="voucher_manager_delete_available_codes">
				<input type="hidden" name="pool_id" value="<?php echo esc_attr( (string) $pool->id() ); ?>">
				<p><label><input type="checkbox" name="confirm_delete_available" value="1" required> <?php echo esc_html( sprintf( __( 'I understand that this permanently deletes %d available One-Time Codes.', 'voucher-manager' ), $summary['available'] ) ); ?></label></p>
				<p><button class="button" type="submit"><?php echo esc_html__( 'Permanently delete available One-Time Codes', 'voucher-manager' ); ?></button></p>
			</form>
		<?php else : ?>
			<p><?php echo esc_html__( 'There are no available One-Time Codes to delete.', 'voucher-manager' ); ?></p>
		<?php endif; ?>
	</section>
</div>
