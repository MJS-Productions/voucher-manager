<?php
declare(strict_types=1);

namespace VoucherManager\Admin;

final class PoolAdmin {

    public function register(): void {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu(): void {
        add_submenu_page(
            'voucher-manager',
            __('Pools', 'voucher-manager'),
            __('Pools', 'voucher-manager'),
            'manage_options',
            'voucher-manager-pools',
            [$this, 'render']
        );
    }

    public function render(): void {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Pools', 'voucher-manager') . '</h1>';
        echo '<p>' . esc_html__('Pool management foundation ready.', 'voucher-manager') . '</p>';
        echo '</div>';
    }
}
