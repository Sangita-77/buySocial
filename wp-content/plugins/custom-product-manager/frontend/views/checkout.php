<?php
if (!defined('ABSPATH')) {
    exit;
}

$cart_items = cpm_get_cart_items();
$cart_total = cpm_get_cart_total();
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order_status = isset($_GET['order_status']) ? sanitize_text_field($_GET['order_status']) : '';

// Show success message
if ($order_status === 'success' && $order_id) {
    global $wpdb;
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('orders') . " WHERE id = %d",
        $order_id
    ));
    
    if ($order) {
        // Check if email was sent
        if (!session_id()) {
            session_start();
        }
        $email_sent = isset($_SESSION['cpm_order_email_sent_' . $order_id]) ? $_SESSION['cpm_order_email_sent_' . $order_id] : false;
        
        // Get last email for local testing
        $last_email = get_option('cpm_last_email_' . $order_id, false);
        
        // Get track order page URL
        $track_order_page_id = get_option('cpm_track_order_page_id', 0);
        $track_order_url = '';
        if ($track_order_page_id) {
            $track_order_url = add_query_arg('order_number', $order->order_number, get_permalink($track_order_page_id));
        } else {
            // Fallback: use track-order URL directly
            $track_order_url = home_url('/track-order/?order_number=' . urlencode($order->order_number));
        }
        
        echo '<div class="cpm-order-success">';
        echo '<h2>' . __('Order Placed Successfully!', 'custom-product-manager') . '</h2>';
        echo '<p>' . sprintf(__('Your order number is: %s', 'custom-product-manager'), '<strong>' . esc_html($order->order_number) . '</strong>') . '</p>';
        
        if ($email_sent) {
            echo '<p>' . __('A confirmation email has been sent to your email address.', 'custom-product-manager') . '</p>';
        } else {
            echo '<p style="color: #d63638;"><strong>' . __('Note:', 'custom-product-manager') . '</strong> ' . __('Email could not be sent. This is common on local development environments.', 'custom-product-manager') . '</p>';
        }
        
        // Add track order link
        echo '<div style="margin-top: 20px; padding: 15px; background: #e8f4f8; border: 1px solid #0073aa; border-radius: 5px;">';
        echo '<p><strong>' . __('Track Your Order:', 'custom-product-manager') . '</strong></p>';
        echo '<p>' . __('You can track your order status anytime using your order number.', 'custom-product-manager') . '</p>';
        echo '<a href="' . esc_url($track_order_url) . '" class="button button-primary" style="margin-top: 10px;">' . __('Track Order Now', 'custom-product-manager') . '</a>';
        echo '</div>';
        
        // Show email preview for local development
        if ($last_email && (defined('WP_DEBUG') && WP_DEBUG)) {
            echo '<div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 5px;">';
            echo '<h3>' . __('Email Preview (Local Development)', 'custom-product-manager') . '</h3>';
            echo '<p><strong>' . __('To:', 'custom-product-manager') . '</strong> ' . esc_html($last_email['to']) . '</p>';
            echo '<p><strong>' . __('Subject:', 'custom-product-manager') . '</strong> ' . esc_html($last_email['subject']) . '</p>';
            echo '<div style="margin-top: 15px; padding: 15px; background: #fff; border: 1px solid #ccc; max-height: 500px; overflow-y: auto;">';
            echo $last_email['message'];
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        return;
    }
}
?>

<?php
// Get billing information for logged-in users
$billing_data = array(
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'address_1' => '',
    'address_2' => '',
    'city' => '',
    'state' => '',
    'postcode' => '',
    'country' => ''
);

if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    
    // Get from user meta or user object
    $billing_data['first_name'] = get_user_meta($current_user->ID, 'first_name', true);
    $billing_data['last_name'] = get_user_meta($current_user->ID, 'last_name', true);
    $billing_data['email'] = $current_user->user_email;
    $billing_data['phone'] = get_user_meta($current_user->ID, 'billing_phone', true);
    $billing_data['address_1'] = get_user_meta($current_user->ID, 'billing_address_1', true);
    $billing_data['address_2'] = get_user_meta($current_user->ID, 'billing_address_2', true);
    $billing_data['city'] = get_user_meta($current_user->ID, 'billing_city', true);
    $billing_data['state'] = get_user_meta($current_user->ID, 'billing_state', true);
    $billing_data['postcode'] = get_user_meta($current_user->ID, 'billing_postcode', true);
    $billing_data['country'] = get_user_meta($current_user->ID, 'billing_country', true);
    
    // If no user meta, try to get from most recent order
    if (empty($billing_data['first_name']) || empty($billing_data['address_1'])) {
        global $wpdb;
        $latest_order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . CPM_Database::get_table('orders') . " 
            WHERE (user_id = %d OR billing_email = %s)
            ORDER BY order_date DESC
            LIMIT 1",
            $current_user->ID,
            $current_user->user_email
        ));
        
        if ($latest_order) {
            if (empty($billing_data['first_name'])) {
                $billing_data['first_name'] = $latest_order->billing_first_name;
            }
            if (empty($billing_data['last_name'])) {
                $billing_data['last_name'] = $latest_order->billing_last_name;
            }
            if (empty($billing_data['phone'])) {
                $billing_data['phone'] = $latest_order->billing_phone;
            }
            if (empty($billing_data['address_1'])) {
                $billing_data['address_1'] = $latest_order->billing_address_1;
            }
            if (empty($billing_data['address_2'])) {
                $billing_data['address_2'] = $latest_order->billing_address_2;
            }
            if (empty($billing_data['city'])) {
                $billing_data['city'] = $latest_order->billing_city;
            }
            if (empty($billing_data['state'])) {
                $billing_data['state'] = $latest_order->billing_state;
            }
            if (empty($billing_data['postcode'])) {
                $billing_data['postcode'] = $latest_order->billing_postcode;
            }
            if (empty($billing_data['country'])) {
                $billing_data['country'] = $latest_order->billing_country;
            }
        }
    }
}
?>
<div class="cpm-checkout">
    <h1><?php _e('Checkout', 'custom-product-manager'); ?></h1>
    
    <div class="cpm-checkout-wrapper">
        <div class="cpm-checkout-form">
            <form method="post" action="">
                <?php wp_nonce_field('cpm_checkout', 'cpm_checkout_nonce'); ?>
                
                <h2><?php _e('Billing Information', 'custom-product-manager'); ?></h2>
                
                <div class="cpm-form-row">
                    <div class="cpm-form-group">
                        <label><?php _e('First Name', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="text" name="billing_first_name" value="<?php echo esc_attr($billing_data['first_name']); ?>" required />
                    </div>
                    <div class="cpm-form-group">
                        <label><?php _e('Last Name', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="text" name="billing_last_name" value="<?php echo esc_attr($billing_data['last_name']); ?>" required />
                    </div>
                </div>
                
                <div class="cpm-form-row">
                    <div class="cpm-form-group">
                        <label><?php _e('Email Address', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="email" name="billing_email" value="<?php echo esc_attr($billing_data['email']); ?>" required />
                    </div>
                    <div class="cpm-form-group">
                        <label><?php _e('Phone Number', 'custom-product-manager'); ?></label>
                        <input type="tel" name="billing_phone" value="<?php echo esc_attr($billing_data['phone']); ?>" />
                    </div>
                </div>
                
                <div class="cpm-form-group">
                    <label><?php _e('Address Line 1', 'custom-product-manager'); ?> <span class="required">*</span></label>
                    <input type="text" name="billing_address_1" value="<?php echo esc_attr($billing_data['address_1']); ?>" required />
                </div>
                
                <div class="cpm-form-group">
                    <label><?php _e('Address Line 2', 'custom-product-manager'); ?></label>
                    <input type="text" name="billing_address_2" value="<?php echo esc_attr($billing_data['address_2']); ?>" />
                </div>
                
                <div class="cpm-form-row">
                    <div class="cpm-form-group">
                        <label><?php _e('City', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="text" name="billing_city" value="<?php echo esc_attr($billing_data['city']); ?>" required />
                    </div>
                    <div class="cpm-form-group">
                        <label><?php _e('State/Province', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="text" name="billing_state" value="<?php echo esc_attr($billing_data['state']); ?>" required />
                    </div>
                </div>
                
                <div class="cpm-form-row">
                    <div class="cpm-form-group">
                        <label><?php _e('Postal Code', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="text" name="billing_postcode" value="<?php echo esc_attr($billing_data['postcode']); ?>" required />
                    </div>
                    <div class="cpm-form-group">
                        <label><?php _e('Country', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="text" name="billing_country" value="<?php echo esc_attr($billing_data['country']); ?>" required />
                    </div>
                </div>
                
                <h2><?php _e('Payment Method', 'custom-product-manager'); ?></h2>
                
                <div class="cpm-form-group">
                    <select name="payment_method" required>
                        <option value=""><?php _e('Select Payment Method', 'custom-product-manager'); ?></option>
                        <option value="credit_card"><?php _e('Credit Card', 'custom-product-manager'); ?></option>
                        <option value="paypal"><?php _e('PayPal', 'custom-product-manager'); ?></option>
                        <option value="bank_transfer"><?php _e('Bank Transfer', 'custom-product-manager'); ?></option>
                        <option value="cash_on_delivery"><?php _e('Cash on Delivery', 'custom-product-manager'); ?></option>
                    </select>
                    <p class="description"><?php _e('Note: This is a demo. Payment integration can be added later.', 'custom-product-manager'); ?></p>
                </div>
                
                <button type="submit" name="cpm_checkout" class="button button-primary button-large">
                    <?php _e('Place Order', 'custom-product-manager'); ?>
                </button>
            </form>
        </div>
        
        <div class="cpm-order-summary">
            <h2><?php _e('Order Summary', 'custom-product-manager'); ?></h2>
            
            <div class="cpm-order-items">
                <?php foreach ($cart_items as $item) : ?>
                    <?php
                    global $wpdb;
                    
                    // Get variation data (new structure with main categories)
                    $variation_data = !empty($item->variation_data) ? json_decode($item->variation_data, true) : null;
                    $product_name = '';
                    $main_category_name = '';
                    $category_name = '';
                    $country_name = '';
                    $page_url = '';
                    
                    if ($variation_data && is_array($variation_data) && isset($variation_data['product_id'])) {
                        // Get product
                        $product = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM " . CPM_Database::get_table('products') . " WHERE id = %d",
                            intval($variation_data['product_id'])
                        ));
                        
                        if ($product) {
                            $product_name = $product->name;
                            
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
                                $main_category_name = $main_categories[$variation_data['main_category_id']]['name'];
                                
                                // Get category name
                                if (isset($variation_data['category_id']) && !empty($variation_data['category_id']) && isset($main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']])) {
                                    $category_name = $main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['name'];
                                    
                                    // Get country name
                                    if (isset($variation_data['country_id']) && !empty($variation_data['country_id']) && isset($main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['countries'][$variation_data['country_id']])) {
                                        $country_name = $main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['countries'][$variation_data['country_id']]['name'];
                                    }
                                }
                            }
                            
                            // Get page URL
                            $page_url = isset($variation_data['page_url']) ? esc_url($variation_data['page_url']) : '';
                        }
                    }
                    
                    $item_display_name = $product_name;
                    if ($main_category_name) {
                        $item_display_name .= ($item_display_name ? ' - ' : '') . $main_category_name;
                    }
                    if ($category_name) {
                        $item_display_name .= ($item_display_name ? ' - ' : '') . $category_name;
                    }
                    ?>
                    <div class="cpm-order-item">
                        <div class="item-name">
                            <?php echo esc_html($item_display_name ? $item_display_name : __('Product #' . $item->id, 'custom-product-manager')); ?>
                        </div>
                        <?php if ($page_url) : ?>
                            <div class="item-url">
                                <strong><?php _e('Post URL:', 'custom-product-manager'); ?></strong>
                                <a href="<?php echo esc_url($page_url); ?>" target="_blank" rel="noopener noreferrer" class="cpm-post-url-link">
                                    <?php echo esc_html($page_url); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="item-meta">
                            <span class="item-qty"><?php echo esc_html($item->quantity); ?>x</span>
                            <span class="item-price"><?php echo cpm_format_price($item->price); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cpm-order-total">
                <strong><?php _e('Total:', 'custom-product-manager'); ?> <?php echo cpm_format_price($cart_total); ?></strong>
            </div>
        </div>
    </div>
</div>


