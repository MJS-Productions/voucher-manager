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
		<dl><dt><?php echo esc_html__( 'Total One-Time Codes', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['total'] ) ); ?></dd><dt><?php echo esc_html_x( 'Available', 'One-Time Code status', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['available'] ) ); ?></dd><dt><?php echo esc_html__( 'Distributed', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['assigned'] ) ); ?></dd><dt><?php echo esc_html__( 'Imports', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $summary['imports'] ) ); ?></dd></dl>
	</section>
	<section class="voucher-manager__card"><h2><?php echo esc_html__( 'Delete available One-Time Codes', 'voucher-manager' ); ?></h2><p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %d: number of unused One-Time Codes that would be deleted */
				_n(
					'Permanently delete %d unused One-Time Code. Assigned One-Time Codes, the pool and operational history are preserved.',
					'Permanently delete %d unused One-Time Codes. Assigned One-Time Codes, the pool and operational history are preserved.',
					$summary['available'],
					'voucher-manager'
				),
				$summary['available']
			)
		);
		?>
	</p>
		<?php if ( 0 < $summary['available'] ) : ?>
			<p><a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'confirm-delete-available', 'pool_id' => $pool->id() ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html__( 'Review available-code deletion', 'voucher-manager' ); ?></a></p>
		<?php else : ?>
			<p><?php echo esc_html__( 'This pool has no available One-Time Codes to delete.', 'voucher-manager' ); ?></p>
		<?php endif; ?>
	</section>
	<?php
	$code_count = sprintf(
		/* translators: %d: total number of One-Time Codes in the Pool */
		_n( '%d One-Time Code', '%d One-Time Codes', $summary['total'], 'voucher-manager' ),
		$summary['total']
	);
	$import_count = sprintf(
		/* translators: %d: total number of import records associated with the Pool */
		_n( '%d import record', '%d import records', $summary['imports'], 'voucher-manager' ),
		$summary['imports']
	);
	?>
	<section class="voucher-manager__card voucher-manager__danger-zone"><h2><?php echo esc_html__( 'Delete pool and all associated data', 'voucher-manager' ); ?></h2><p><strong><?php echo esc_html__( 'Permanent deletion cannot be undone.', 'voucher-manager' ); ?></strong>
		<?php
		echo esc_html(
			sprintf(
				/* translators: 1: localized One-Time Code count, 2: localized import record count */
				__( 'This permanently deletes the pool, %1$s and %2$s.', 'voucher-manager' ),
				$code_count,
				$import_count
			)
		);
		?>
	</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'voucher_manager_delete_pool_' . $pool->id() ); ?><input type="hidden" name="action" value="voucher_manager_delete_pool"><input type="hidden" name="pool_id" value="<?php echo esc_attr( (string) $pool->id() ); ?>"><label for="pool-name-confirmation"><strong>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: exact Pool name required for destructive confirmation */
					__( 'Type “%s” to confirm:', 'voucher-manager' ),
					$pool->name()
				)
			);
			?>
		</strong></label><input id="pool-name-confirmation" class="regular-text" type="text" name="pool_name_confirmation" autocomplete="off" required><p><button class="button" type="submit"><?php echo esc_html__( 'Delete pool and all associated data', 'voucher-manager' ); ?></button></p></form>
	</section>
</div>
