<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user = isset($current_user) ? $current_user : wp_get_current_user();
?>

<div class="cpm-my-account-wrapper">
    <div class="cpm-my-account-container">
        <div class="cpm-my-account-header">
            <h1><?php _e('My Orders', 'custom-product-manager'); ?></h1>
            <a href="<?php echo home_url('/my-account/'); ?>" class="cpm-back-link"><?php _e('← Back to Account', 'custom-product-manager'); ?></a>
        </div>
        
        <div class="cpm-my-account-content">
            <?php 
            // Debug output
            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo '<!-- CPM Debug: Orders variable exists: ' . (isset($orders) ? 'yes' : 'no') . ' -->';
                echo '<!-- CPM Debug: Orders is array: ' . (is_array($orders) ? 'yes' : 'no') . ' -->';
                echo '<!-- CPM Debug: Orders count: ' . (isset($orders) && is_array($orders) ? count($orders) : 'N/A') . ' -->';
                echo '<!-- CPM Debug: User ID: ' . (isset($current_user) ? $current_user->ID : 'not set') . ' -->';
                echo '<!-- CPM Debug: User Email: ' . (isset($current_user) ? $current_user->user_email : 'not set') . ' -->';
            }
            
            if (isset($orders) && is_array($orders) && count($orders) > 0) : ?>
                <div class="cpm-orders-list">
                    <table class="cpm-orders-table">
                        <thead>
                            <tr>
                                <th><?php _e('Order #', 'custom-product-manager'); ?></th>
                                <th><?php _e('Date', 'custom-product-manager'); ?></th>
                                <th><?php _e('Status', 'custom-product-manager'); ?></th>
                                <th><?php _e('Total', 'custom-product-manager'); ?></th>
                                <th><?php _e('Actions', 'custom-product-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($order->order_number); ?></strong></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($order->order_date))); ?></td>
                                    <td>
                                        <span class="cpm-order-status cpm-status-<?php echo esc_attr($order->order_status); ?>">
                                            <?php echo esc_html(ucfirst($order->order_status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo cpm_format_price($order->order_total); ?></td>
                                    <td>
                                        <a href="<?php echo home_url('/track-order/?order_number=' . urlencode($order->order_number)); ?>" class="cpm-view-order-btn">
                                            <?php _e('View', 'custom-product-manager'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="cpm-no-orders">
                    <p><?php _e('You have not placed any orders yet.', 'custom-product-manager'); ?></p>
                    <?php if (defined('WP_DEBUG') && WP_DEBUG) : ?>
                        <p style="color: red; font-size: 12px;">
                            Debug: Orders variable <?php echo isset($orders) ? 'exists' : 'does not exist'; ?>, 
                            is array: <?php echo is_array($orders) ? 'yes' : 'no'; ?>, 
                            count: <?php echo isset($orders) && is_array($orders) ? count($orders) : 'N/A'; ?>
                        </p>
                    <?php endif; ?>
                    <a href="<?php echo home_url('?cpm_products=1'); ?>" class="cpm-shop-btn"><?php _e('Start Shopping', 'custom-product-manager'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

