<?php
/**
 * Direct protected Distribution result fallback.
 *
 * Used only when server-side one-time result persistence fails after a claim.
 *
 * @var array{success:bool,code:?string,message:string,remaining:?int,pool_id:int} $result
 * @var \VoucherManager\Admin\DistributionViewModel $view
 * @var string $result_pool_name
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$remaining = isset( $result['remaining'] ) ? absint( $result['remaining'] ) : null;

wp_enqueue_style(
	'voucher-manager-admin',
	VOUCHER_MANAGER_URL . 'assets/css/admin.css',
	array(),
	VOUCHER_MANAGER_VERSION
);
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html__( 'Distribution result', 'voucher-manager' ); ?></title>
	<?php
	wp_admin_css( 'common' );
	wp_print_styles( 'voucher-manager-admin' );
	?>
</head>
<body class="wp-admin wp-core-ui">
	<div class="wrap voucher-manager voucher-manager__direct-result">
		<h1><?php echo esc_html__( 'Distribution', 'voucher-manager' ); ?></h1>

		<?php if ( ! empty( $result['success'] ) && is_string( $result['code'] ?? null ) ) : ?>
			<div class="notice notice-warning inline"><p><?php echo esc_html__( 'The One-Time Code was assigned successfully, but the normal one-time result could not be stored. Copy it before leaving this page.', 'voucher-manager' ); ?></p></div>
			<div class="voucher-manager__distribution-result voucher-manager__distribution-result--<?php echo esc_attr( $view->result_tone( $remaining ) ); ?>">
				<h2><?php echo esc_html__( 'Assigned One-Time Code', 'voucher-manager' ); ?></h2>
				<div class="voucher-manager__code-result" aria-label="<?php echo esc_attr__( 'Distributed One-Time Code', 'voucher-manager' ); ?>">
					<code id="vm-distributed-code"><?php echo esc_html( (string) $result['code'] ); ?></code>
					<button type="button" class="button button-secondary" id="vm-copy-distributed-code"><?php echo esc_html__( 'Copy code', 'voucher-manager' ); ?></button>
				</div>
				<?php if ( '' !== $result_pool_name ) : ?>
					<p class="voucher-manager__result-pool"><?php echo esc_html( $view->pool_message( $result_pool_name ) ); ?></p>
				<?php endif; ?>
				<p class="voucher-manager__result-inventory"><?php echo esc_html( $view->remaining_message( $remaining ) ); ?></p>
			</div>
		<?php else : ?>
			<div class="notice notice-error inline"><p><strong><?php echo esc_html__( 'No One-Time Code was distributed.', 'voucher-manager' ); ?></strong> <?php echo esc_html( (string) ( $result['message'] ?? __( 'Distribution failed.', 'voucher-manager' ) ) ); ?></p></div>
		<?php endif; ?>

		<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=voucher-manager-distribution' ) ); ?>"><?php echo esc_html__( 'Return to Distribution', 'voucher-manager' ); ?></a></p>
	</div>
	<script>
	(function () {
		const button = document.getElementById('vm-copy-distributed-code');
		const code = document.getElementById('vm-distributed-code');
		if (!button || !code || !navigator.clipboard) {
			return;
		}
		button.addEventListener('click', function () {
			navigator.clipboard.writeText(code.textContent || '');
		});
	}());
	</script>
</body>
</html>
