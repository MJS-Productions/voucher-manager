<?php
/**
 * Pool overview.
 *
 * @package VoucherManager
 */

/** @var array<int,array{pool:\VoucherManager\Domain\Pool\Pool,total:int,available:int,assigned:int}> $pool_rows */

declare(strict_types=1);

use VoucherManager\Admin\PoolViewModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notice = isset( $_GET['vm_notice'] )
	? sanitize_key( wp_unslash( $_GET['vm_notice'] ) )
	: '';

$messages = array(
	'created'        => __( 'Your first voucher pool has been created.', 'voucher-manager' ),
	'updated'        => __( 'Pool updated.', 'voucher-manager' ),
	'deleted'        => __( 'Pool deleted.', 'voucher-manager' ),
	'status'         => __( 'Pool status updated.', 'voucher-manager' ),
	'invalid'        => __( 'Please enter a pool name.', 'voucher-manager' ),
	'error'          => __( 'The operation could not be completed.', 'voucher-manager' ),
	'delete_blocked' => __( 'This pool cannot be deleted while it contains codes.', 'voucher-manager' ),
);

$view_model = new PoolViewModel();
?>
<div class="wrap voucher-manager">
	<header class="voucher-manager__header">
		<div>
			<h1><?php echo esc_html__( 'Pools', 'voucher-manager' ); ?></h1>
			<p><?php echo esc_html__( 'Organize voucher codes into inventories for distribution.', 'voucher-manager' ); ?></p>
		</div>
		<a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'new' ), admin_url( 'admin.php' ) ) ); ?>">
			<?php echo esc_html__( 'Create Pool', 'voucher-manager' ); ?>
		</a>
	</header>

	<?php if ( isset( $messages[ $notice ] ) ) : ?>
		<div class="notice <?php echo in_array( $notice, array( 'invalid', 'error', 'delete_blocked' ), true ) ? 'notice-error' : 'notice-success'; ?> is-dismissible">
			<p><?php echo esc_html( $messages[ $notice ] ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( empty( $pool_rows ) ) : ?>
		<section class="voucher-manager__card voucher-manager__pool-empty">
			<span class="dashicons dashicons-tickets-alt" aria-hidden="true"></span>
			<h2><?php echo esc_html__( 'Create your first pool', 'voucher-manager' ); ?></h2>
			<p><?php echo esc_html__( 'A pool groups voucher codes that belong to the same campaign, product or distribution workflow.', 'voucher-manager' ); ?></p>
			<a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-pools', 'action' => 'new' ), admin_url( 'admin.php' ) ) ); ?>">
				<?php echo esc_html__( 'Create Pool', 'voucher-manager' ); ?>
			</a>
		</section>
	<?php else : ?>
		<section class="voucher-manager__pool-grid" aria-label="<?php echo esc_attr__( 'Pool overview', 'voucher-manager' ); ?>">
			<?php foreach ( $pool_rows as $row ) : ?>
				<?php
				$pool       = $row['pool'];
				$pool_id    = (int) $pool->id();
				$state      = $view_model->inventory_state( $pool, $row['available'] );
				$edit_url   = add_query_arg(
					array(
						'page'    => 'voucher-manager-pools',
						'action'  => 'edit',
						'pool_id' => $pool_id,
					),
					admin_url( 'admin.php' )
				);
				$import_url = add_query_arg(
					array(
						'page'    => 'voucher-manager-import',
						'pool_id' => $pool_id,
					),
					admin_url( 'admin.php' )
				);
				$distribution_url = add_query_arg(
					array(
						'page'    => 'voucher-manager-distribution',
						'pool_id' => $pool_id,
					),
					admin_url( 'admin.php' )
				);
				$toggle_url = wp_nonce_url(
					add_query_arg(
						array(
							'action'  => 'voucher_manager_toggle_pool',
							'pool_id' => $pool_id,
						),
						admin_url( 'admin-post.php' )
					),
					'voucher_manager_toggle_pool_' . $pool_id
				);
				$delete_url = wp_nonce_url(
					add_query_arg(
						array(
							'action'  => 'voucher_manager_delete_pool',
							'pool_id' => $pool_id,
						),
						admin_url( 'admin-post.php' )
					),
					'voucher_manager_delete_pool_' . $pool_id
				);
				?>
				<article class="voucher-manager__pool-card voucher-manager__pool-card--<?php echo esc_attr( $state ); ?>">
					<div class="voucher-manager__pool-card-header">
						<div>
							<h2><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $pool->name() ); ?></a></h2>
							<?php if ( '' !== $pool->description() ) : ?>
								<p><?php echo esc_html( $pool->description() ); ?></p>
							<?php else : ?>
								<p class="voucher-manager__muted"><?php echo esc_html__( 'No description added.', 'voucher-manager' ); ?></p>
							<?php endif; ?>
						</div>
						<span class="voucher-manager__inventory-badge voucher-manager__inventory-badge--<?php echo esc_attr( $state ); ?>">
							<?php echo esc_html( $view_model->inventory_label( $pool, $row['available'] ) ); ?>
						</span>
					</div>

					<div class="voucher-manager__pool-inventory">
						<div>
							<strong><?php echo esc_html( number_format_i18n( $row['available'] ) ); ?></strong>
							<span><?php echo esc_html__( 'Available', 'voucher-manager' ); ?></span>
						</div>
						<div>
							<strong><?php echo esc_html( number_format_i18n( $row['assigned'] ) ); ?></strong>
							<span><?php echo esc_html__( 'Distributed', 'voucher-manager' ); ?></span>
						</div>
						<div>
							<strong><?php echo esc_html( number_format_i18n( $row['total'] ) ); ?></strong>
							<span><?php echo esc_html__( 'Total codes', 'voucher-manager' ); ?></span>
						</div>
					</div>

					<p class="voucher-manager__pool-hint">
						<?php echo esc_html( $view_model->inventory_hint( $pool, $row['available'] ) ); ?>
					</p>

					<div class="voucher-manager__pool-actions">
						<?php if ( $pool->is_active() && 0 < $row['available'] ) : ?>
							<a class="button button-primary" href="<?php echo esc_url( $distribution_url ); ?>">
								<?php echo esc_html__( 'Distribute Code', 'voucher-manager' ); ?>
							</a>
						<?php else : ?>
							<a class="button button-primary" href="<?php echo esc_url( $import_url ); ?>">
								<?php echo esc_html__( 'Import Codes', 'voucher-manager' ); ?>
							</a>
						<?php endif; ?>
						<a class="button" href="<?php echo esc_url( $edit_url ); ?>">
							<?php echo esc_html__( 'Edit', 'voucher-manager' ); ?>
						</a>
						<div class="voucher-manager__pool-secondary-actions">
							<a href="<?php echo esc_url( $toggle_url ); ?>">
								<?php echo esc_html( $pool->is_active() ? __( 'Deactivate', 'voucher-manager' ) : __( 'Activate', 'voucher-manager' ) ); ?>
							</a>
							<?php if ( 0 === $row['total'] ) : ?>
								<span aria-hidden="true">·</span>
								<a class="voucher-manager__delete" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this empty pool?', 'voucher-manager' ) ); ?>');">
									<?php echo esc_html__( 'Delete', 'voucher-manager' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>
</div>
