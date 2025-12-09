<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Order Details', 'custom-product-manager'); ?></h1>
    
    <div class="cpm-order-details">
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><?php _e('Order Information', 'custom-product-manager'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Order Number', 'custom-product-manager'); ?></th>
                        <td><strong><?php echo esc_html($order->order_number); ?></strong></td>
                    </tr>
                    <tr>
                        <th><?php _e('Order Date', 'custom-product-manager'); ?></th>
                        <td><?php echo esc_html($order->order_date); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Order Status', 'custom-product-manager'); ?></th>
                        <td>
                            <form method="post" action="" style="display: inline;">
                                <?php wp_nonce_field('update_order_status_' . $order->id); ?>
                                <select name="order_status">
                                    <option value="pending" <?php selected($order->order_status, 'pending'); ?>><?php _e('Pending', 'custom-product-manager'); ?></option>
                                    <option value="processing" <?php selected($order->order_status, 'processing'); ?>><?php _e('Processing', 'custom-product-manager'); ?></option>
                                    <option value="completed" <?php selected($order->order_status, 'completed'); ?>><?php _e('Completed', 'custom-product-manager'); ?></option>
                                    <option value="cancelled" <?php selected($order->order_status, 'cancelled'); ?>><?php _e('Cancelled', 'custom-product-manager'); ?></option>
                                </select>
                                <button type="submit" class="button"><?php _e('Update', 'custom-product-manager'); ?></button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Payment Status', 'custom-product-manager'); ?></th>
                        <td><?php echo esc_html(ucfirst($order->payment_status)); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Payment Method', 'custom-product-manager'); ?></th>
                        <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $order->payment_method))); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Order Total', 'custom-product-manager'); ?></th>
                        <td><strong><?php echo cpm_format_price($order->order_total); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="postbox">
            <h2 class="hndle"><?php _e('Billing Information', 'custom-product-manager'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Name', 'custom-product-manager'); ?></th>
                        <td><?php echo esc_html($order->billing_first_name . ' ' . $order->billing_last_name); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Email', 'custom-product-manager'); ?></th>
                        <td><?php echo esc_html($order->billing_email); ?></td>
                    </tr>
                    <?php if ($order->billing_phone) : ?>
                    <tr>
                        <th><?php _e('Phone', 'custom-product-manager'); ?></th>
                        <td><?php echo esc_html($order->billing_phone); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th><?php _e('Address', 'custom-product-manager'); ?></th>
                        <td>
                            <?php echo esc_html($order->billing_address_1); ?><br>
                            <?php if ($order->billing_address_2) : ?>
                                <?php echo esc_html($order->billing_address_2); ?><br>
                            <?php endif; ?>
                            <?php echo esc_html($order->billing_city . ', ' . $order->billing_state . ' ' . $order->billing_postcode); ?><br>
                            <?php echo esc_html($order->billing_country); ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="postbox">
            <h2 class="hndle"><?php _e('Order Items', 'custom-product-manager'); ?></h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Product', 'custom-product-manager'); ?></th>
                            <th><?php _e('Variation', 'custom-product-manager'); ?></th>
                            <th><?php _e('Post URL', 'custom-product-manager'); ?></th>
                            <th><?php _e('Quantity', 'custom-product-manager'); ?></th>
                            <th><?php _e('Price', 'custom-product-manager'); ?></th>
                            <th><?php _e('Subtotal', 'custom-product-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item) : ?>
                            <?php
                            global $wpdb;
                            $variation_data = json_decode($item->variation_data, true);
                            $variation_text = '';
                            $page_url = '';
                            
                            // Get page URL from variation data
                            if (is_array($variation_data) && isset($variation_data['page_url'])) {
                                $page_url = esc_url($variation_data['page_url']);
                            }
                            
                            // Build readable variation text from the data
                            if (is_array($variation_data) && !empty($variation_data)) {
                                $variation_parts = array();
                                
                                // Get product to access main categories
                                if (isset($variation_data['product_id'])) {
                                    $product = $wpdb->get_row($wpdb->prepare(
                                        "SELECT * FROM " . CPM_Database::get_table('products') . " WHERE id = %d",
                                        intval($variation_data['product_id'])
                                    ));
                                    
                                    if ($product) {
                                        // Get main categories data
                                        $option_name = '_cpm_product_' . intval($variation_data['product_id']) . '_main_categories';
                                        $saved_data = get_option($option_name, '');
                                        $main_categories = array();
                                        
                                        if ($saved_data) {
                                            $main_categories = json_decode($saved_data, true);
                                            if (!is_array($main_categories)) {
                                                $main_categories = array();
                                            }
                                        }
                                        
                                        // Get main category name
                                        if (isset($variation_data['main_category_id']) && !empty($variation_data['main_category_id']) && isset($main_categories[$variation_data['main_category_id']])) {
                                            $variation_parts[] = $main_categories[$variation_data['main_category_id']]['name'];
                                            
                                            // Get category name
                                            if (isset($variation_data['category_id']) && !empty($variation_data['category_id']) && isset($main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']])) {
                                                $variation_parts[] = $main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['name'];
                                                
                                                // Get country name
                                                if (isset($variation_data['country_id']) && !empty($variation_data['country_id']) && isset($main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['countries'][$variation_data['country_id']])) {
                                                    $variation_parts[] = $main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['countries'][$variation_data['country_id']]['name'];
                                                    
                                                    // Get quantity text
                                                    if (isset($variation_data['quantity_id']) && !empty($variation_data['quantity_id']) && isset($main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['countries'][$variation_data['country_id']]['quantities'][$variation_data['quantity_id']])) {
                                                        $quantity_data = $main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['countries'][$variation_data['country_id']]['quantities'][$variation_data['quantity_id']];
                                                        if (isset($quantity_data['quantity'])) {
                                                            $variation_parts[] = $quantity_data['quantity'];
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                // If we couldn't build from main categories, show raw data (fallback)
                                if (empty($variation_parts)) {
                                    // Filter out numeric IDs and only show meaningful values
                                    foreach ($variation_data as $key => $value) {
                                        if (!in_array($key, array('product_id', 'main_category_id', 'category_id', 'country_id', 'quantity_id', 'price_id')) && !empty($value)) {
                                            // Handle array values
                                            if (is_array($value)) {
                                                $value = implode(', ', array_filter($value, function($v) {
                                                    return !is_array($v) && !empty($v);
                                                }));
                                            }
                                            // Only add if value is not empty and is a string/number
                                            if (!is_array($value) && !empty($value)) {
                                                $variation_parts[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                                            }
                                        }
                                    }
                                }
                                
                                $variation_text = !empty($variation_parts) ? implode(', ', $variation_parts) : __('No variation details', 'custom-product-manager');
                            } else {
                                $variation_text = __('No variation data', 'custom-product-manager');
                            }
                            ?>
                            <tr>
                                <td><?php echo esc_html($item->product_name); ?></td>
                                <td><?php echo esc_html($variation_text); ?></td>
                                <td>
                                    <?php if ($page_url) : ?>
                                        <a href="<?php echo esc_url($page_url); ?>" target="_blank" rel="noopener noreferrer" class="cpm-post-url-link">
                                            <?php echo esc_html($page_url); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="cpm-no-url">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($item->quantity); ?></td>
                                <td><?php echo cpm_format_price($item->price); ?></td>
                                <td><?php echo cpm_format_price($item->subtotal); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="5" style="text-align: right;"><strong><?php _e('Total:', 'custom-product-manager'); ?></strong></td>
                            <td><strong><?php echo cpm_format_price($order->order_total); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <p>
            <a href="<?php echo admin_url('admin.php?page=cpm-orders'); ?>" class="button"><?php _e('Back to Orders', 'custom-product-manager'); ?></a>
        </p>
    </div>
</div>



