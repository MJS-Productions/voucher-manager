<?php
/** Initial administration dashboard. @package VoucherManager */
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="wrap voucher-manager">
	<header class="voucher-manager__header">
		<h1><?php echo esc_html__( 'Voucher Manager', 'voucher-manager' ); ?></h1>
		<p><?php echo esc_html__( 'The project foundation is active. Pool and code management will follow in the next sprint.', 'voucher-manager' ); ?></p>
	</header>
	<section class="voucher-manager__card" aria-labelledby="voucher-manager-foundation-title">
		<h2 id="voucher-manager-foundation-title"><?php echo esc_html__( 'Foundation ready', 'voucher-manager' ); ?></h2>
		<ul>
			<li><?php echo esc_html__( 'Plugin bootstrap loaded', 'voucher-manager' ); ?></li>
			<li><?php echo esc_html__( 'Composer autoloading active', 'voucher-manager' ); ?></li>
			<li><?php echo esc_html__( 'Administration screen registered', 'voucher-manager' ); ?></li>
			<li><?php echo esc_html__( 'Translation support prepared', 'voucher-manager' ); ?></li>
		</ul>
	</section>
</div>
