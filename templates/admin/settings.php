<?php
/**
 * Voucher Manager Settings screen.
 *
 * @var \VoucherManager\Domain\Settings\Settings $settings
 * @var \VoucherManager\Admin\SettingsViewModel $view
 * @var string $notice
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap voucher-manager">
	<header class="voucher-manager__header">
		<div>
			<h1><?php echo esc_html__( 'Voucher Manager Settings', 'voucher-manager' ); ?></h1>
			<p><?php echo esc_html__( 'Control operational data retention and the explicit uninstall boundary.', 'voucher-manager' ); ?></p>
		</div>
	</header>

	<?php if ( 'settings_saved' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved.', 'voucher-manager' ); ?></p></div>
	<?php elseif ( 'uninstall_confirmation_required' === $notice ) : ?>
		<div class="notice notice-error"><p><?php echo esc_html__( 'Confirm that you understand the permanent uninstall deletion before enabling it.', 'voucher-manager' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'voucher_manager_save_settings' ); ?>
		<input type="hidden" name="action" value="voucher_manager_save_settings">

		<section class="voucher-manager__card voucher-manager__settings-section">
			<h2><?php echo esc_html__( 'Operational Activity retention', 'voucher-manager' ); ?></h2>
			<p><?php echo esc_html__( 'Activity is operational history, not a legal or financial audit log.', 'voucher-manager' ); ?></p>

			<label for="voucher-manager-activity-retention"><strong><?php echo esc_html__( 'Keep Activity for', 'voucher-manager' ); ?></strong></label>
			<select id="voucher-manager-activity-retention" name="activity_retention_days">
				<?php foreach ( $view->retention_options() as $days => $label ) : ?>
					<option value="<?php echo esc_attr( (string) $days ); ?>" <?php selected( $settings->activity_retention_days(), $days ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php echo esc_html( $view->retention_description( $settings->activity_retention_days() ) ); ?></p>
			<p class="voucher-manager__muted"><?php echo esc_html__( 'Scheduled cleanup will be introduced in the next hardening part. Saving this preference does not delete Activity immediately.', 'voucher-manager' ); ?></p>
		</section>

		<section class="voucher-manager__card voucher-manager__settings-section voucher-manager__danger-zone">
			<h2><?php echo esc_html__( 'Uninstall data boundary', 'voucher-manager' ); ?></h2>
			<p><strong><?php echo esc_html__( 'Default: preserve all Voucher Manager business data.', 'voucher-manager' ); ?></strong></p>
			<p><?php echo esc_html( $view->uninstall_warning() ); ?></p>

			<label class="voucher-manager__settings-checkbox">
				<input type="checkbox" name="delete_data_on_uninstall" value="1" <?php checked( $settings->delete_data_on_uninstall() ); ?>>
				<span><?php echo esc_html__( 'Delete all Voucher Manager data when the plugin is uninstalled', 'voucher-manager' ); ?></span>
			</label>

			<label class="voucher-manager__settings-checkbox voucher-manager__settings-confirmation">
				<input type="checkbox" name="confirm_delete_data_on_uninstall" value="1">
				<span><?php echo esc_html__( 'I understand that uninstalling the plugin will permanently delete all Pools, Imports, Codes and Activity.', 'voucher-manager' ); ?></span>
			</label>

			<p class="description"><?php echo esc_html__( 'Deactivating Voucher Manager never deletes data. This setting affects uninstall only.', 'voucher-manager' ); ?></p>
		</section>

		<?php submit_button( __( 'Save settings', 'voucher-manager' ) ); ?>
	</form>
</div>
