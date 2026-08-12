<?php
/**
 * Manual distribution experience.
 *
 * @var array<int,array{pool:\VoucherManager\Domain\Pool\Pool,total:int,available:int,assigned:int}> $pool_rows
 * @var \VoucherManager\Admin\DistributionViewModel $view
 * @var string $result_pool_name
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notice = isset( $_GET['vm_notice'] )
	? sanitize_key( wp_unslash( $_GET['vm_notice'] ) )
	: '';

$distributable_rows = array_values(
	array_filter(
		$pool_rows,
		static fn( array $row ): bool => $view->can_distribute( $row )
	)
);

$requested_pool_id = isset( $_GET['pool_id'] )
	? absint( wp_unslash( $_GET['pool_id'] ) )
	: 0;

$selected_pool_id = 0;
foreach ( $distributable_rows as $row ) {
	if ( $requested_pool_id === (int) $row['pool']->id() ) {
		$selected_pool_id = $requested_pool_id;
		break;
	}
}
?>
<div class="wrap voucher-manager">
	<h1><?php echo esc_html__( 'Distribution', 'voucher-manager' ); ?></h1>
	<p class="description"><?php echo esc_html__( 'Assign one available One-Time Code from an active pool. Each successful distribution immediately marks the code as assigned.', 'voucher-manager' ); ?></p>

	<?php if ( 'replayed' === $notice ) : ?>
		<div class="notice notice-warning inline"><p><?php echo esc_html__( 'This distribution request was already used or expired. No additional One-Time Code was distributed.', 'voucher-manager' ); ?></p></div>
	<?php endif; ?>

	<?php if ( is_array( $result ) ) : ?>
		<?php if ( ! empty( $result['success'] ) && is_string( $result['code'] ?? null ) ) : ?>
			<?php $remaining = isset( $result['remaining'] ) ? absint( $result['remaining'] ) : null; ?>
			<div class="voucher-manager__distribution-result voucher-manager__distribution-result--<?php echo esc_attr( $view->result_tone( $remaining ) ); ?>">
				<h2><?php echo esc_html__( 'Assigned One-Time Code', 'voucher-manager' ); ?></h2>
				<p class="voucher-manager__result-guidance"><?php echo esc_html__( 'Copy the One-Time Code now and deliver it through the intended channel. For privacy, the complete value is shown only in this one-time result.', 'voucher-manager' ); ?></p>
				<div class="voucher-manager__code-result" aria-label="<?php echo esc_attr__( 'Distributed One-Time Code', 'voucher-manager' ); ?>">
					<code id="vm-distributed-code"><?php echo esc_html( (string) $result['code'] ); ?></code>
					<button type="button" class="button button-secondary" id="vm-copy-distributed-code" data-copy-label="<?php echo esc_attr__( 'Copy code', 'voucher-manager' ); ?>" data-copied-label="<?php echo esc_attr__( 'Copied', 'voucher-manager' ); ?>">
						<?php echo esc_html__( 'Copy code', 'voucher-manager' ); ?>
					</button>
				</div>
				<?php if ( '' !== $result_pool_name ) : ?>
					<p class="voucher-manager__result-pool"><?php echo esc_html( $view->pool_message( $result_pool_name ) ); ?></p>
				<?php endif; ?>
				<p class="voucher-manager__result-inventory"><?php echo esc_html( $view->remaining_message( $remaining ) ); ?></p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'voucher-manager-distribution', 'pool_id' => absint( $result['pool_id'] ?? 0 ) ), admin_url( 'admin.php' ) ) ); ?>">
						<?php echo esc_html__( 'Distribute another One-Time Code', 'voucher-manager' ); ?>
					</a>
				</p>
			</div>
			<script>
			(function () {
				const button = document.getElementById('vm-copy-distributed-code');
				const code = document.getElementById('vm-distributed-code');
				if (!button || !code || !navigator.clipboard) {
					return;
				}
				const copyLabel = button.dataset.copyLabel || button.textContent || '';
				const copiedLabel = button.dataset.copiedLabel || copyLabel;

				button.addEventListener('click', function () {
					navigator.clipboard.writeText(code.textContent || '').then(function () {
						button.textContent = copiedLabel;
						window.setTimeout(function () {
							button.textContent = copyLabel;
						}, 1600);
					});
				});
			}());
			</script>
		<?php else : ?>
			<div class="notice notice-error inline"><p><strong><?php echo esc_html__( 'No One-Time Code was distributed.', 'voucher-manager' ); ?></strong> <?php echo esc_html( (string) ( $result['message'] ?? __( 'Distribution failed.', 'voucher-manager' ) ) ); ?></p></div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! $has_successful_result ) : ?>
	<div class="voucher-manager__distribution-grid">
		<div class="voucher-manager__card voucher-manager__form">
			<h2><?php echo esc_html__( 'Distribute next available One-Time Code', 'voucher-manager' ); ?></h2>
			<p><?php echo esc_html__( 'Choose a pool with available inventory. Voucher Manager automatically assigns the next available One-Time Code. Concurrent requests never receive the same code.', 'voucher-manager' ); ?></p>

			<?php if ( empty( $distributable_rows ) ) : ?>
				<div class="voucher-manager__empty-state voucher-manager__distribution-empty">
					<span class="dashicons dashicons-tickets-alt" aria-hidden="true"></span>
					<h3><?php echo esc_html__( 'No One-Time Codes are ready to distribute', 'voucher-manager' ); ?></h3>
					<p><?php echo esc_html__( 'Active pools currently have no available One-Time Codes. Import codes before trying again.', 'voucher-manager' ); ?></p>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=voucher-manager-import' ) ); ?>"><?php echo esc_html__( 'Import Codes', 'voucher-manager' ); ?></a>
				</div>
			<?php elseif ( '' === $intent_token ) : ?>
				<div class="notice notice-error inline"><p><?php echo esc_html__( 'A secure distribution request could not be prepared. Reload this page and try again.', 'voucher-manager' ); ?></p></div>
			<?php else : ?>
				<form class="voucher-manager__distribution-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="voucher_manager_distribute_code">
					<input type="hidden" name="distribution_intent" value="<?php echo esc_attr( $intent_token ); ?>">
					<?php wp_nonce_field( 'voucher_manager_distribute_code' ); ?>
					<p>
						<label for="vm-distribution-pool"><strong><?php echo esc_html_x( 'Pool', 'Distribution form field label', 'voucher-manager' ); ?></strong></label>
						<select id="vm-distribution-pool" name="pool_id" required>
							<?php foreach ( $distributable_rows as $row ) : ?>
								<option value="<?php echo esc_attr( (string) $row['pool']->id() ); ?>" <?php selected( $selected_pool_id, (int) $row['pool']->id() ); ?>><?php echo esc_html( $view->pool_option_label( $row ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p class="description"><?php echo esc_html__( 'One-Time Code assignment occurs immediately after confirmation.', 'voucher-manager' ); ?></p>
					<?php submit_button( __( 'Distribute Code', 'voucher-manager' ) ); ?>
				</form>
			<?php endif; ?>
		</div>

		<div class="voucher-manager__card">
			<h2><?php echo esc_html__( 'Distribution safety', 'voucher-manager' ); ?></h2>
			<ul class="voucher-manager__guidance-list">
				<li><?php echo esc_html__( 'Only active pools with available inventory can be selected.', 'voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'A successful distribution changes the One-Time Code from available to assigned immediately.', 'voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'The One-Time Code value is not written to operational Activity context.', 'voucher-manager' ); ?></li>
				<li><?php echo esc_html__( 'Refreshing the page does not distribute another One-Time Code. Every distribution requires a new form submission.', 'voucher-manager' ); ?></li>
			</ul>
		</div>
	</div>
	<?php endif; ?>
</div>
