<?php
/** @package VoucherManager */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/** @var \VoucherManager\Domain\Pool\Pool $pool */
/** @var array{total:int,available:int,assigned:int,imports:int}|null $summary */
if ( null === $summary ) { return; }
$notice = isset( $_GET['vm_notice'] ) ? sanitize_key( wp_unslash( $_GET['vm_notice'] ) ) : '';
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html__( 'Pool Danger Zone', 'voucher-manager' ); ?></h1>
	<p><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-pools' ), admin_url( 'admin.php' ) ) ); ?>">&larr; <?php echo esc_html__( 'Back to Pools', 'voucher-manager' ); ?></a></p>
	<?php if ( 'confirmation_failed' === $notice ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'The pool name did not match. Nothing was deleted.', 'voucher-manager' ); ?></p></div><?php endif; ?>
	<?php if ( 'delete_failed' === $notice ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'Pool deletion failed. The destructive operation was rolled back.', 'voucher-manager' ); ?></p></div><?php endif; ?>
	<section class="voucher-manager__card"><h2><?php echo esc_html( $pool->name() ); ?></h2>
		<dl><dt><?php echo esc_html__( 'Total codes', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['total'] ) ); ?></dd><dt><?php echo esc_html__( 'Available', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['available'] ) ); ?></dd><dt><?php echo esc_html__( 'Distributed', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['assigned'] ) ); ?></dd><dt><?php echo esc_html__( 'Imports', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['imports'] ) ); ?></dd></dl>
	</section>
	<section class="voucher-manager__card"><h2><?php echo esc_html__( 'Delete available codes', 'voucher-manager' ); ?></h2><p><?php echo esc_html( sprintf( __( 'Permanently delete %d unused codes. Distributed codes, the pool and operational history are preserved.', 'voucher-manager' ), $summary['available'] ) ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'voucher_manager_delete_available_codes_' . $pool->id() ); ?><input type="hidden" name="action" value="voucher_manager_delete_available_codes"><input type="hidden" name="pool_id" value="<?php echo esc_attr( (string) $pool->id() ); ?>"><button class="button" type="submit"><?php echo esc_html__( 'Delete available codes', 'voucher-manager' ); ?></button></form>
	</section>
	<section class="voucher-manager__card voucher-manager__danger-zone"><h2><?php echo esc_html__( 'Delete pool and all associated data', 'voucher-manager' ); ?></h2><p><strong><?php echo esc_html__( 'Permanent deletion cannot be undone.', 'voucher-manager' ); ?></strong> <?php echo esc_html( sprintf( __( 'This permanently deletes the pool, all %d codes and %d import records.', 'voucher-manager' ), $summary['total'], $summary['imports'] ) ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'voucher_manager_delete_pool_' . $pool->id() ); ?><input type="hidden" name="action" value="voucher_manager_delete_pool"><input type="hidden" name="pool_id" value="<?php echo esc_attr( (string) $pool->id() ); ?>"><label for="pool-name-confirmation"><strong><?php echo esc_html( sprintf( __( 'Type “%s” to confirm:', 'voucher-manager' ), $pool->name() ) ); ?></strong></label><input id="pool-name-confirmation" class="regular-text" type="text" name="pool_name_confirmation" autocomplete="off" required><p><button class="button" type="submit"><?php echo esc_html__( 'Delete pool and all associated data', 'voucher-manager' ); ?></button></p></form>
	</section>
</div>
