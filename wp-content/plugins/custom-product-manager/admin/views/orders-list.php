<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Orders', 'custom-product-manager'); ?></h1>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Order #', 'custom-product-manager'); ?></th>
                <th><?php _e('Customer', 'custom-product-manager'); ?></th>
                <th><?php _e('Email', 'custom-product-manager'); ?></th>
                <th><?php _e('Total', 'custom-product-manager'); ?></th>
                <th><?php _e('Status', 'custom-product-manager'); ?></th>
                <th><?php _e('Date', 'custom-product-manager'); ?></th>
                <th><?php _e('Actions', 'custom-product-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders) : ?>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($order->order_number); ?></strong></td>
                        <td><?php echo esc_html($order->billing_first_name . ' ' . $order->billing_last_name); ?></td>
                        <td><?php echo esc_html($order->billing_email); ?></td>
                        <td><?php echo cpm_format_price($order->order_total); ?></td>
                        <td><span class="status-<?php echo esc_attr($order->order_status); ?>"><?php echo esc_html(ucfirst($order->order_status)); ?></span></td>
                        <td><?php echo esc_html($order->order_date); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=cpm-orders&action=view&order_id=' . $order->id); ?>"><?php _e('View', 'custom-product-manager'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7"><?php _e('No orders found.', 'custom-product-manager'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>




