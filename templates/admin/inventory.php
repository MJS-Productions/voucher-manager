<?php
/**
 * Pool inventory screen.
 *
 * @var \VoucherManager\Domain\Pool\Pool $pool
 * @var array<string,mixed> $data
 * @var \VoucherManager\Admin\InventoryViewModel $view
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap voucher-manager">
	<header class="voucher-manager__header">
		<div>
			<h1><?php echo esc_html( sprintf( __( '%s Inventory', 'voucher-manager' ), $pool->name() ) ); ?></h1>
			<p><?php echo esc_html__( 'Review pool-scoped inventory without exposing complete voucher values.', 'voucher-manager' ); ?></p>
		</div>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=voucher-manager-pools' ) ); ?>"><?php echo esc_html__( 'Back to Pools', 'voucher-manager' ); ?></a>
	</header>

	<h2 class="voucher-manager__section-title voucher-manager__inventory-totals-title"><?php echo esc_html__( 'Pool totals', 'voucher-manager' ); ?></h2>
	<section class="voucher-manager__metrics voucher-manager__metrics--three" aria-label="<?php echo esc_attr__( 'Pool inventory summary', 'voucher-manager' ); ?>">
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'Total codes', 'voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['total'] ) ); ?></strong>
			<small><?php echo esc_html__( 'All code records in this pool', 'voucher-manager' ); ?></small>
		</article>
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'Available', 'voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['available'] ) ); ?></strong>
			<small><?php echo esc_html__( 'Ready for distribution', 'voucher-manager' ); ?></small>
		</article>
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'Assigned', 'voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['assigned'] ) ); ?></strong>
			<small><?php echo esc_html__( 'Already distributed', 'voucher-manager' ); ?></small>
		</article>
	</section>

	<div class="voucher-manager__card voucher-manager__inventory-filters">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
			<input type="hidden" name="page" value="voucher-manager-inventory">
			<input type="hidden" name="pool_id" value="<?php echo esc_attr( (string) $pool->id() ); ?>">

			<div>
				<label for="vm-inventory-state"><strong><?php echo esc_html__( 'State', 'voucher-manager' ); ?></strong></label>
				<select id="vm-inventory-state" name="state">
					<option value="all" <?php selected( $data['filters']['state'], 'all' ); ?>><?php echo esc_html__( 'All states', 'voucher-manager' ); ?></option>
					<option value="available" <?php selected( $data['filters']['state'], 'available' ); ?>><?php echo esc_html__( 'Available', 'voucher-manager' ); ?></option>
					<option value="assigned" <?php selected( $data['filters']['state'], 'assigned' ); ?>><?php echo esc_html__( 'Assigned', 'voucher-manager' ); ?></option>
				</select>
			</div>

			<div>
				<label for="vm-inventory-import"><strong><?php echo esc_html__( 'Import', 'voucher-manager' ); ?></strong></label>
				<select id="vm-inventory-import" name="import_id">
					<option value="0"><?php echo esc_html__( 'All imports', 'voucher-manager' ); ?></option>
					<?php foreach ( $data['import_options'] as $option ) : ?>
						<option value="<?php echo esc_attr( (string) $option['id'] ); ?>" <?php selected( $data['filters']['import_id'], $option['id'] ); ?>>
							<?php echo esc_html( sprintf( __( 'Import #%1$d — %2$s', 'voucher-manager' ), $option['id'], $option['filename'] ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<?php submit_button( __( 'Filter inventory', 'voucher-manager' ), 'secondary', '', false ); ?>
			<?php if ( $view->has_active_filters( $data['filters']['state'], $data['filters']['import_id'] ) ) : ?>
				<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-inventory', 'pool_id' => $pool->id() ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html__( 'Reset filters', 'voucher-manager' ); ?></a>
			<?php endif; ?>
		</form>
	</div>

	<section class="voucher-manager__card voucher-manager__table-card">
		<div class="voucher-manager__inventory-table-header">
			<div>
				<h2><?php echo esc_html__( 'Code inventory', 'voucher-manager' ); ?></h2>
				<p><?php echo esc_html__( 'References are masked. Complete voucher values are only shown in the one-time Distribution result.', 'voucher-manager' ); ?></p>
			</div>
			<div class="voucher-manager__inventory-result-meta">
				<?php $filter_summary = $view->active_filter_summary( $data['filters']['state'], $data['filters']['import_id'], $data['import_options'] ); ?>
				<?php if ( '' !== $filter_summary ) : ?>
					<strong><?php echo esc_html( $filter_summary ); ?></strong>
				<?php endif; ?>
				<span class="voucher-manager__muted"><?php echo esc_html( $view->result_range( $data['page'], $data['per_page'], $data['total'] ) ); ?></span>
			</div>
		</div>

		<?php if ( empty( $data['records'] ) ) : ?>
			<div class="voucher-manager__empty-state">
				<strong><?php echo esc_html( $view->empty_state_title( $data['pool_empty'], $data['filters']['state'], $data['filters']['import_id'] ) ); ?></strong>
				<p><?php echo esc_html( $view->empty_state_message( $data['pool_empty'], $data['filters']['state'], $data['filters']['import_id'] ) ); ?></p>
				<?php if ( $view->has_active_filters( $data['filters']['state'], $data['filters']['import_id'] ) ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-inventory', 'pool_id' => $pool->id() ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html__( 'Reset filters', 'voucher-manager' ); ?></a>
				<?php endif; ?>
				<?php if ( $data['pool_empty'] || 0 === $data['counts']['available'] ) : ?>
					<a class="button<?php echo $view->has_active_filters( $data['filters']['state'], $data['filters']['import_id'] ) ? '' : ' button-primary'; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-import', 'pool_id' => $pool->id() ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html__( 'Import Codes', 'voucher-manager' ); ?></a>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="voucher-manager__table-scroll">
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html__( 'Reference', 'voucher-manager' ); ?></th>
						<th><?php echo esc_html__( 'State', 'voucher-manager' ); ?></th>
						<th><?php echo esc_html__( 'Import', 'voucher-manager' ); ?></th>
						<th><?php echo esc_html__( 'Imported', 'voucher-manager' ); ?></th>
						<th><?php echo esc_html__( 'Assigned', 'voucher-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $data['records'] as $record ) : ?>
						<tr>
							<td><code class="voucher-manager__masked-reference"><?php echo esc_html( $view->reference( $record ) ); ?></code><br><small><?php echo esc_html( sprintf( __( 'Code #%d', 'voucher-manager' ), $record->id() ) ); ?></small></td>
							<td><span class="voucher-manager__badge voucher-manager__badge--<?php echo esc_attr( $view->status_tone( $record->status() ) ); ?>"><?php echo esc_html( $view->status_label( $record->status() ) ); ?></span></td>
							<td><?php echo null === $record->import_id() ? '—' : esc_html( sprintf( __( 'Import #%d', 'voucher-manager' ), $record->import_id() ) ); ?></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $record->imported_at() . ' UTC', true ) ); ?></td>
							<td><?php echo null === $record->assigned_at() ? '—' : esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $record->assigned_at() . ' UTC', true ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>

			<?php if ( 1 < $data['pages'] ) : ?>
				<div class="voucher-manager__pagination voucher-manager__inventory-pagination">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => add_query_arg(
									array(
										'page'      => 'voucher-manager-inventory',
										'pool_id'   => $pool->id(),
										'state'     => $data['filters']['state'],
										'import_id' => $data['filters']['import_id'] ?? 0,
										'paged'     => '%#%',
									),
									admin_url( 'admin.php' )
								),
								'format'    => '',
								'current'   => $data['page'],
								'total'     => $data['pages'],
								'prev_text' => __( 'Previous', 'voucher-manager' ),
								'next_text' => __( 'Next', 'voucher-manager' ),
							)
						)
					);
					?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</section>
</div>
