<?php
/**
 * Operational activity history.
 *
 * @var array<string,mixed> $data
 * @var \VoucherManager\Admin\OperationalActivityViewModel $view
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$families = array( 'all', 'import', 'distribution', 'pool', 'settings', 'admin' );
$tones    = array(
	'all'     => __( 'All outcomes', 'mjs-productions-voucher-manager' ),
	'success' => __( 'Success', 'mjs-productions-voucher-manager' ),
	'warning' => __( 'Attention', 'mjs-productions-voucher-manager' ),
	'error'   => __( 'Errors', 'mjs-productions-voucher-manager' ),
);
?>
<div class="wrap voucher-manager">
	<header class="voucher-manager__header">
		<div>
			<h1><?php echo esc_html__( 'Operational Activity', 'mjs-productions-voucher-manager' ); ?></h1>
			<p><?php echo esc_html__( 'Review operational events and identify actions that need attention.', 'mjs-productions-voucher-manager' ); ?></p>
		</div>
	</header>

	<section class="voucher-manager__metrics voucher-manager__metrics--three" aria-label="<?php echo esc_attr__( 'Operational event overview', 'mjs-productions-voucher-manager' ); ?>">
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'All events', 'mjs-productions-voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['all'] ) ); ?></strong>
			<small><?php echo esc_html__( 'Recorded', 'mjs-productions-voucher-manager' ); ?></small>
		</article>
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'Warnings and errors', 'mjs-productions-voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['attention'] ) ); ?></strong>
			<small><?php echo esc_html__( 'Need attention', 'mjs-productions-voucher-manager' ); ?></small>
		</article>
		<article class="voucher-manager__metric">
			<span><?php echo esc_html__( 'Errors', 'mjs-productions-voucher-manager' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $data['counts']['error'] ) ); ?></strong>
			<small><?php echo esc_html__( 'Failed operations', 'mjs-productions-voucher-manager' ); ?></small>
		</article>
	</section>

	<div class="voucher-manager__card voucher-manager__activity-filters">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
			<input type="hidden" name="page" value="voucher-manager-activity">
			<div>
				<label for="vm-activity-family"><strong><?php echo esc_html_x( 'Area', 'Activity filter label', 'mjs-productions-voucher-manager' ); ?></strong></label>
				<select id="vm-activity-family" name="family">
					<?php foreach ( $families as $family ) : ?>
						<option value="<?php echo esc_attr( $family ); ?>" <?php selected( $data['filters']['family'], $family ); ?>>
							<?php echo esc_html( $view->family_label( $family ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label for="vm-activity-tone"><strong><?php echo esc_html_x( 'Outcome', 'Activity filter label', 'mjs-productions-voucher-manager' ); ?></strong></label>
				<select id="vm-activity-tone" name="tone">
					<?php foreach ( $tones as $tone => $label ) : ?>
						<option value="<?php echo esc_attr( $tone ); ?>" <?php selected( $data['filters']['tone'], $tone ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php submit_button( __( 'Filter activity', 'mjs-productions-voucher-manager' ), 'secondary', '', false ); ?>
			<?php if ( $view->has_active_filters( $data['filters']['family'], $data['filters']['tone'] ) ) : ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=voucher-manager-activity' ) ); ?>"><?php echo esc_html__( 'Reset filters', 'mjs-productions-voucher-manager' ); ?></a>
			<?php endif; ?>
		</form>
	</div>

	<section class="voucher-manager__card" aria-labelledby="voucher-manager-activity-history-title">
		<div class="voucher-manager__card-header">
			<div>
				<h2 id="voucher-manager-activity-history-title"><?php echo esc_html__( 'Activity history', 'mjs-productions-voucher-manager' ); ?></h2>
				<p><?php echo esc_html__( 'One-Time Code values are not stored in Activity history.', 'mjs-productions-voucher-manager' ); ?></p>
			</div>
			<span class="voucher-manager__muted">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: filtered event count */
						__( '%s matching events', 'mjs-productions-voucher-manager' ),
						number_format_i18n( $data['total'] )
					)
				);
				?>
			</span>
		</div>

		<?php if ( empty( $data['events'] ) ) : ?>
			<div class="voucher-manager__empty-state">
				<?php if ( 0 === (int) $data['counts']['all'] ) : ?>
					<strong><?php echo esc_html__( 'No activity recorded yet.', 'mjs-productions-voucher-manager' ); ?></strong>
					<p><?php echo esc_html__( 'New activity will appear here automatically.', 'mjs-productions-voucher-manager' ); ?></p>
				<?php else : ?>
					<strong><?php echo esc_html__( 'No matching activity found.', 'mjs-productions-voucher-manager' ); ?></strong>
					<p><?php echo esc_html__( 'Adjust the filters to display more activity.', 'mjs-productions-voucher-manager' ); ?></p>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<ol class="voucher-manager__activity-history">
				<?php foreach ( $data['events'] as $event ) : ?>
					<?php
					$event_type = (string) $event['event_type'];
					$context    = is_array( $event['context'] ) ? $event['context'] : array();
					$tone       = $view->tone( $event_type );
					$detail     = $view->detail( $event_type, $context );
					$guidance   = $view->guidance( $event_type );
					$timestamp  = strtotime( (string) $event['created_at'] );
					?>
					<li class="voucher-manager__activity-entry voucher-manager__activity-entry--<?php echo esc_attr( $tone ); ?>">
						<div class="voucher-manager__activity-entry-main">
							<div class="voucher-manager__activity-entry-heading">
								<strong><?php echo esc_html( $view->label( $event_type, $context ) ); ?></strong>
								<span class="voucher-manager__activity-badge voucher-manager__activity-badge--<?php echo esc_attr( $tone ); ?>">
									<?php echo esc_html( $view->severity_label( $event_type ) ); ?>
								</span>
							</div>
							<?php if ( '' !== $detail ) : ?>
								<p><?php echo wp_kses_post( nl2br( esc_html( $detail ) ) ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $guidance ) : ?>
								<p class="voucher-manager__activity-guidance"><?php echo esc_html( $guidance ); ?></p>
							<?php endif; ?>
						</div>
						<time datetime="<?php echo esc_attr( (string) $event['created_at'] ); ?>">
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

			<?php if ( 1 < $data['pages'] ) : ?>
				<div class="voucher-manager__pagination">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => add_query_arg(
									array(
										'page'   => 'voucher-manager-activity',
										'family' => $data['filters']['family'],
										'tone'   => $data['filters']['tone'],
										'paged'  => '%#%',
									),
									admin_url( 'admin.php' )
								),
								'format'    => '',
								'current'   => $data['page'],
								'total'     => $data['pages'],
								'prev_text' => __( 'Previous', 'mjs-productions-voucher-manager' ),
								'next_text' => __( 'Next', 'mjs-productions-voucher-manager' ),
							)
						)
					);
					?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</section>
</div>
