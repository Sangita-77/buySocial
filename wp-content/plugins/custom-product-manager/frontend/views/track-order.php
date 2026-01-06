<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$order_number = isset($_GET['order_number']) ? sanitize_text_field($_GET['order_number']) : '';
$order = null;
$order_items = array();
$error_message = '';

if ($order_number) {
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('orders') . " WHERE order_number = %s",
        $order_number
    ));
    
    if ($order) {
        $order_items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . CPM_Database::get_table('order_items') . " WHERE order_id = %d ORDER BY id ASC",
            $order->id
        ));
    } else {
        $error_message = __('Order not found. Please check your order number and try again.', 'custom-product-manager');
    }
}
?>

<div class="cpm-track-order">
    <!-- <h1><?php //_e('Track Your Order', 'custom-product-manager'); ?></h1> -->
    
    <div class="cpm-track-order-form">
        <form method="get" action="">
            <div class="cpm-form-group">
                <label for="order_number"><?php _e('Order Number', 'custom-product-manager'); ?></label>
                <input type="text" 
                       id="order_number" 
                       name="order_number" 
                       value="<?php echo esc_attr($order_number); ?>" 
                       placeholder="<?php _e('Enter your order number (e.g., ORD-20240101-ABC12345)', 'custom-product-manager'); ?>"
                       required />
                <p class="description"><?php _e('You can find your order number in the confirmation email we sent you.', 'custom-product-manager'); ?></p>
            </div>
            <button type="submit" class="cpm-track-btn"><?php _e('Track Order', 'custom-product-manager'); ?></button>
        </form>
    </div>
    
    <?php if ($error_message) : ?>
        <div class="cpm-error-message">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($order) : ?>
        <div class="cpm-order-tracking-results">
            <div class="cpm-order-status-box">
                <h2><?php _e('Order Status', 'custom-product-manager'); ?></h2>
                <div class="cpm-status-indicator">
                    <span class="cpm-status-badge cpm-status-<?php echo esc_attr($order->order_status); ?>">
                        <?php echo esc_html(ucfirst($order->order_status)); ?>
                    </span>
                </div>
            </div>
            
            <div class="cpm-order-details-section">
                <h2><?php _e('Order Details', 'custom-product-manager'); ?></h2>
                <div class="cpm-order-info-grid">
                    <div class="cpm-info-item">
                        <strong><?php _e('Order Number:', 'custom-product-manager'); ?></strong>
                        <span><?php echo esc_html($order->order_number); ?></span>
                    </div>
                    <div class="cpm-info-item">
                        <strong><?php _e('Order Date:', 'custom-product-manager'); ?></strong>
                        <span><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->order_date))); ?></span>
                    </div>
                    <div class="cpm-info-item">
                        <strong><?php _e('Order Status:', 'custom-product-manager'); ?></strong>
                        <span><?php echo esc_html(ucfirst($order->order_status)); ?></span>
                    </div>
                    <div class="cpm-info-item">
                        <strong><?php _e('Payment Status:', 'custom-product-manager'); ?></strong>
                        <span><?php echo esc_html(ucfirst($order->payment_status)); ?></span>
                    </div>
                    <div class="cpm-info-item">
                        <strong><?php _e('Payment Method:', 'custom-product-manager'); ?></strong>
                        <span><?php echo esc_html(ucfirst(str_replace('_', ' ', $order->payment_method))); ?></span>
                    </div>
                    <div class="cpm-info-item">
                        <strong><?php _e('Total Amount:', 'custom-product-manager'); ?></strong>
                        <span class="cpm-total-amount"><?php echo cpm_format_price($order->order_total); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="cpm-order-items-section">
                <h2><?php _e('Order Items', 'custom-product-manager'); ?></h2>
                <table class="cpm-order-items-table">
                    <thead>
                        <tr>
                            <th><?php _e('Product', 'custom-product-manager'); ?></th>
                            <th><?php _e('Main Category', 'custom-product-manager'); ?></th>
                            <th><?php _e('Category', 'custom-product-manager'); ?></th>
                            <th><?php _e('Country', 'custom-product-manager'); ?></th>
                            <th><?php _e('Post URL', 'custom-product-manager'); ?></th>
                            <th><?php _e('Quantity', 'custom-product-manager'); ?></th>
                            <th><?php _e('Price', 'custom-product-manager'); ?></th>
                            <th><?php _e('Subtotal', 'custom-product-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item) : ?>
                            <?php
                            $variation_data = json_decode($item->variation_data, true);
                            
                            // Extract details from new structure
                            $product_name = isset($variation_data['product_name']) ? $variation_data['product_name'] : $item->product_name;
                            $main_category = isset($variation_data['main_category']) ? $variation_data['main_category'] : '';
                            $category = isset($variation_data['category']) ? $variation_data['category'] : '';
                            $country = isset($variation_data['country']) ? $variation_data['country'] : '';
                            $quantity_text = isset($variation_data['quantity']) ? $variation_data['quantity'] : '';
                            
                            // Get page URL from variation data
                            $page_url = '';
                            
                            // First check top level
                            if (is_array($variation_data) && isset($variation_data['page_url']) && !empty($variation_data['page_url'])) {
                                $page_url = esc_url($variation_data['page_url']);
                            }
                            
                            // If not found, check inside original_data (for orders created before fix)
                            if (empty($page_url) && is_array($variation_data) && isset($variation_data['original_data'])) {
                                $original_data = $variation_data['original_data'];
                                if (is_array($original_data) && isset($original_data['page_url']) && !empty($original_data['page_url'])) {
                                    $page_url = esc_url($original_data['page_url']);
                                }
                            }
                            
                            // If still not found, try alternative keys (for backward compatibility)
                            if (empty($page_url) && is_array($variation_data)) {
                                // Try alternative keys
                                if (isset($variation_data['post_url'])) {
                                    $page_url = esc_url($variation_data['post_url']);
                                } elseif (isset($variation_data['url'])) {
                                    $page_url = esc_url($variation_data['url']);
                                }
                                // Also check in original_data
                                elseif (isset($variation_data['original_data']) && is_array($variation_data['original_data'])) {
                                    $original_data = $variation_data['original_data'];
                                    if (isset($original_data['post_url'])) {
                                        $page_url = esc_url($original_data['post_url']);
                                    } elseif (isset($original_data['url'])) {
                                        $page_url = esc_url($original_data['url']);
                                    }
                                }
                            }
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($product_name); ?></strong></td>
                                <td><?php echo esc_html($main_category ? $main_category : '-'); ?></td>
                                <td><?php echo esc_html($category ? $category : '-'); ?></td>
                                <td><?php echo esc_html($country ? $country : '-'); ?></td>
                                <td>
                                    <?php if ($page_url) : ?>
                                        <a href="<?php echo esc_url($page_url); ?>" target="_blank" rel="noopener noreferrer" class="cpm-post-url-link" title="<?php echo esc_attr($page_url); ?>">
                                            <?php 
                                            // Truncate long URLs for display
                                            $display_url = strlen($page_url) > 40 ? substr($page_url, 0, 40) . '...' : $page_url;
                                            echo esc_html($display_url); 
                                            ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="cpm-no-url">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($quantity_text ? $quantity_text : $item->quantity); ?></td>
                                <td><?php echo cpm_format_price($item->price); ?></td>
                                <td><?php echo cpm_format_price($item->subtotal); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="cpm-total-label"><strong><?php _e('Total:', 'custom-product-manager'); ?></strong></td>
                            <td class="cpm-total-amount"><strong><?php echo cpm_format_price($order->order_total); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="cpm-billing-info-section">
                <h2><?php _e('Billing Information', 'custom-product-manager'); ?></h2>
                <div class="cpm-billing-details">
                    <p><strong><?php _e('Name:', 'custom-product-manager'); ?></strong> <?php echo esc_html($order->billing_first_name . ' ' . $order->billing_last_name); ?></p>
                    <p><strong><?php _e('Email:', 'custom-product-manager'); ?></strong> <?php echo esc_html($order->billing_email); ?></p>
                    <?php if ($order->billing_phone) : ?>
                        <p><strong><?php _e('Phone:', 'custom-product-manager'); ?></strong> <?php echo esc_html($order->billing_phone); ?></p>
                    <?php endif; ?>
                    <p><strong><?php _e('Address:', 'custom-product-manager'); ?></strong></p>
                    <p>
                        <?php echo esc_html($order->billing_address_1); ?><br>
                        <?php if ($order->billing_address_2) : ?>
                            <?php echo esc_html($order->billing_address_2); ?><br>
                        <?php endif; ?>
                        <?php echo esc_html($order->billing_city . ', ' . $order->billing_state . ' ' . $order->billing_postcode); ?><br>
                        <?php echo esc_html($order->billing_country); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

