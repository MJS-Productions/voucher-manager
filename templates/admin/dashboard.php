<?php
/**
 * Voucher Manager dashboard.
 *
 * @package VoucherManager
 */

/** @var array<string,mixed> $data */

declare(strict_types=1);

use VoucherManager\Admin\DashboardViewModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view_model    = new DashboardViewModel();
$database_label = $data['database_healthy']
	? __( 'Ready', 'voucher-manager' )
	: __( 'Needs attention', 'voucher-manager' );

$metrics = array(
	'available' => array(
		'label' => __( 'Available codes', 'voucher-manager' ),
		'hint'  => __( 'Ready for distribution', 'voucher-manager' ),
	),
	'assigned' => array(
		'label' => __( 'Distributed codes', 'voucher-manager' ),
		'hint'  => __( 'Successfully assigned', 'voucher-manager' ),
	),
	'pools' => array(
		'label' => __( 'Pools', 'voucher-manager' ),
		'hint'  => __( 'Code collections', 'voucher-manager' ),
	),
	'imports' => array(
		'label' => __( 'Imports', 'voucher-manager' ),
		'hint'  => __( 'Completed and recorded', 'voucher-manager' ),
	),
);
?>
<div class="wrap voucher-manager">
	<header class="voucher-manager__header">
		<div>
			<h1><?php echo esc_html__( 'Dashboard', 'voucher-manager' ); ?></h1>
			<p>
				<?php
				echo esc_html__(
					'Manage your code inventory and review recent activity.',
					'voucher-manager'
				);
				?>
			</p>
		</div>
		<span class="voucher-manager__version">
			<?php echo esc_html( sprintf( 'v%s', $data['plugin_version'] ) ); ?>
		</span>
	</header>

	<nav class="voucher-manager__quick-actions" aria-label="<?php echo esc_attr__( 'Quick actions', 'voucher-manager' ); ?>">
		<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=voucher-manager-pools&action=new' ) ); ?>">
			<?php echo esc_html__( 'Create Pool', 'voucher-manager' ); ?>
		</a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=voucher-manager-import' ) ); ?>">
			<?php echo esc_html__( 'Import Codes', 'voucher-manager' ); ?>
		</a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=voucher-manager-distribution' ) ); ?>">
			<?php echo esc_html__( 'Distribute Code', 'voucher-manager' ); ?>
		</a>
	</nav>

	<section class="voucher-manager__metrics voucher-manager__metrics--four" aria-label="<?php echo esc_attr__( 'Inventory overview', 'voucher-manager' ); ?>">
		<?php foreach ( $metrics as $key => $metric ) : ?>
			<article class="voucher-manager__metric">
				<span><?php echo esc_html( $metric['label'] ); ?></span>
				<strong><?php echo esc_html( number_format_i18n( $data['counts'][ $key ] ) ); ?></strong>
				<small><?php echo esc_html( $metric['hint'] ); ?></small>
			</article>
		<?php endforeach; ?>
	</section>

	<div class="voucher-manager__dashboard-grid">
		<section class="voucher-manager__card" aria-labelledby="voucher-manager-activity-title">
			<div class="voucher-manager__card-header">
				<div>
					<h2 id="voucher-manager-activity-title">
						<?php echo esc_html__( 'Recent activity', 'voucher-manager' ); ?>
					</h2>
					<p><?php echo esc_html__( 'The latest operational events, without personal data.', 'voucher-manager' ); ?></p>
				</div>
				<span class="voucher-manager__muted">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: total number of operational log entries */
							__( '%s total events', 'voucher-manager' ),
							number_format_i18n( $data['counts']['logs'] )
						)
					);
					?>
				</span>
			</div>

			<?php if ( empty( $data['activity'] ) ) : ?>
				<div class="voucher-manager__empty-state">
					<strong><?php echo esc_html__( 'No activity recorded yet.', 'voucher-manager' ); ?></strong>
					<p><?php echo esc_html__( 'Imports and distributions will appear here.', 'voucher-manager' ); ?></p>
				</div>
			<?php else : ?>
				<ol class="voucher-manager__activity-list">
					<?php foreach ( $data['activity'] as $activity ) : ?>
						<?php
						$event_type = (string) $activity['event_type'];
						$tone       = $view_model->activity_tone( $event_type );
						$detail     = $view_model->activity_detail(
							$event_type,
							is_array( $activity['context'] ) ? $activity['context'] : array()
						);
						$timestamp  = strtotime( (string) $activity['created_at'] );
						?>
						<li class="voucher-manager__activity voucher-manager__activity--<?php echo esc_attr( $tone ); ?>">
							<span class="voucher-manager__activity-marker" aria-hidden="true"></span>
							<div>
								<strong><?php echo esc_html( $view_model->activity_label( $event_type ) ); ?></strong>
								<?php if ( '' !== $detail ) : ?>
									<span><?php echo esc_html( $detail ); ?></span>
								<?php endif; ?>
							</div>
							<time datetime="<?php echo esc_attr( (string) $activity['created_at'] ); ?>">
								<?php
								echo esc_html(
									false !== $timestamp
										? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp )
										: ''
								);
								?>
							</time>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</section>

		<section class="voucher-manager__card" aria-labelledby="voucher-manager-system-title">
			<h2 id="voucher-manager-system-title"><?php echo esc_html__( 'System status', 'voucher-manager' ); ?></h2>
			<dl class="voucher-manager__status-list">
				<div>
					<dt><?php echo esc_html__( 'Database', 'voucher-manager' ); ?></dt>
					<dd class="<?php echo $data['database_healthy'] ? 'is-good' : 'is-warning'; ?>">
						<?php echo esc_html( $database_label ); ?>
					</dd>
				</div>
				<div>
					<dt><?php echo esc_html__( 'Database schema', 'voucher-manager' ); ?></dt>
					<dd><?php echo esc_html( $data['database_version'] ); ?></dd>
				</div>
				<div>
					<dt><?php echo esc_html__( 'WordPress', 'voucher-manager' ); ?></dt>
					<dd><?php echo esc_html( $data['wordpress_version'] ); ?></dd>
				</div>
				<div>
					<dt><?php echo esc_html__( 'PHP', 'voucher-manager' ); ?></dt>
					<dd><?php echo esc_html( $data['php_version'] ); ?></dd>
				</div>
			</dl>
		</section>
	</div>

	<footer class="voucher-manager__footer">
		<span><?php echo esc_html__( 'Designed and developed with love in Austria.', 'voucher-manager' ); ?></span>
		<em><?php echo esc_html__( 'Every great project starts with a single line.', 'voucher-manager' ); ?></em>
	</footer>
</div>
