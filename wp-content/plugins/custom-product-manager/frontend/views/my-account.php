<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user = isset($current_user) ? $current_user : wp_get_current_user();
?>

<div class="cpm-my-account-wrapper">
        <div class="cpm-my-account-container">
            <div class="cpm-my-account-header">
                <h1><?php _e('My Account', 'custom-product-manager'); ?></h1>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="cpm-logout-link"><?php _e('Logout', 'custom-product-manager'); ?></a>
            </div>
            
            <div class="cpm-my-account-content">
                <div class="cpm-account-welcome">
                    <h2><?php printf(__('Hello %s', 'custom-product-manager'), esc_html($current_user->display_name)); ?></h2>
                    <p><?php printf(__('From your account dashboard you can view your recent orders, manage your account details, and more.', 'custom-product-manager')); ?></p>
                </div>
                
                <div class="cpm-account-menu">
                    <a href="<?php echo home_url('/my-account/orders/'); ?>" class="cpm-account-menu-item">
                        <span class="cpm-menu-icon">📦</span>
                        <div>
                            <h3><?php _e('Orders', 'custom-product-manager'); ?></h3>
                            <p><?php _e('View your order history', 'custom-product-manager'); ?></p>
                        </div>
                    </a>
                    
                    <a href="<?php echo home_url('/my-account/edit-account/'); ?>" class="cpm-account-menu-item">
                        <span class="cpm-menu-icon">👤</span>
                        <div>
                            <h3><?php _e('Account Details', 'custom-product-manager'); ?></h3>
                            <p><?php _e('Edit your account information', 'custom-product-manager'); ?></p>
                        </div>
                    </a>
                </div>
                
                <div class="cpm-account-info">
                    <h3><?php _e('Account Information', 'custom-product-manager'); ?></h3>
                    <table class="cpm-account-table">
                        <tr>
                            <th><?php _e('Name:', 'custom-product-manager'); ?></th>
                            <td><?php echo esc_html($current_user->display_name); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Email:', 'custom-product-manager'); ?></th>
                            <td><?php echo esc_html($current_user->user_email); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Username:', 'custom-product-manager'); ?></th>
                            <td><?php echo esc_html($current_user->user_login); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Member Since:', 'custom-product-manager'); ?></th>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($current_user->user_registered))); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

