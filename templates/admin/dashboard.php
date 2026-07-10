<?php
/**
 * Voucher Manager administration dashboard.
 *
 * @package VoucherManager
 *
 * @var array<string,mixed> $data Dashboard data.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$database_label = $data['database_healthy']
	? __( 'Ready', 'voucher-manager' )
	: __( 'Needs attention', 'voucher-manager' );
?>
<div class="wrap voucher-manager">
	<header class="voucher-manager__header">
		<div>
			<h1><?php echo esc_html__( 'Voucher Manager', 'voucher-manager' ); ?></h1>
			<p><?php echo esc_html__( 'Secure management and distribution of unique codes.', 'voucher-manager' ); ?></p>
		</div>
		<span class="voucher-manager__version">
			<?php echo esc_html( sprintf( 'v%s', $data['plugin_version'] ) ); ?>
		</span>
	</header>

	<section class="voucher-manager__metrics" aria-label="<?php echo esc_attr__( 'Inventory overview', 'voucher-manager' ); ?>">
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'Pools', 'voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['pools'] ) ); ?></strong>
		</article>
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'Codes', 'voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['codes'] ) ); ?></strong>
		</article>
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'Log entries', 'voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['logs'] ) ); ?></strong>
		</article>
	</section>

	<section class="voucher-manager__card" aria-labelledby="voucher-manager-system-title">
		<h2 id="voucher-manager-system-title"><?php echo esc_html__( 'System status', 'voucher-manager' ); ?></h2>
		<dl class="voucher-manager__status-list">
			<div><dt><?php echo esc_html__( 'Plugin', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( $data['plugin_version'] ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'Database', 'voucher-manager' ); ?></dt><dd class="<?php echo $data['database_healthy'] ? 'is-good' : 'is-warning'; ?>"><?php echo esc_html( $database_label ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'Database schema', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( $data['database_version'] ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'WordPress', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( $data['wordpress_version'] ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'PHP', 'voucher-manager' ); ?></dt><dd><?php echo esc_html( $data['php_version'] ); ?></dd></div>
		</dl>
	</section>

	<footer class="voucher-manager__footer">
		<span><?php echo esc_html__( 'Designed and developed with love in Austria.', 'voucher-manager' ); ?></span>
		<em><?php echo esc_html__( 'Every great project starts with a single line.', 'voucher-manager' ); ?></em>
	</footer>
</div>
