<?php
/**
 * Checkout class
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPM_Checkout {
    
    public function __construct() {
        add_shortcode('cpm_checkout', array($this, 'checkout_shortcode'));
        add_action('wp', array($this, 'process_checkout'));
    }
    
    /**
     * Checkout shortcode
     */
    public function checkout_shortcode() {
        $cart_items = cpm_get_cart_items();
        
        if (empty($cart_items)) {
            $cart_page_id = get_option('cpm_cart_page_id');
            if ($cart_page_id) {
                wp_redirect(get_permalink($cart_page_id));
                exit;
            }
            return __('Your cart is empty.', 'custom-product-manager');
        }
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/checkout.php';
        return ob_get_clean();
    }
    
    /**
     * Process checkout
     */
    public function process_checkout() {
        if (!isset($_POST['cpm_checkout']) || !wp_verify_nonce($_POST['cpm_checkout_nonce'], 'cpm_checkout')) {
            return;
        }
        
        global $wpdb;
        
        $cart_items = cpm_get_cart_items();
        if (empty($cart_items)) {
            wp_redirect(home_url());
            exit;
        }
        
        // Get billing information
        $billing_data = array(
            'first_name' => sanitize_text_field($_POST['billing_first_name']),
            'last_name' => sanitize_text_field($_POST['billing_last_name']),
            'email' => sanitize_email($_POST['billing_email']),
            'phone' => sanitize_text_field($_POST['billing_phone']),
            'address_1' => sanitize_text_field($_POST['billing_address_1']),
            'address_2' => sanitize_text_field($_POST['billing_address_2']),
            'city' => sanitize_text_field($_POST['billing_city']),
            'state' => sanitize_text_field($_POST['billing_state']),
            'postcode' => sanitize_text_field($_POST['billing_postcode']),
            'country' => sanitize_text_field($_POST['billing_country']),
            'payment_method' => sanitize_text_field($_POST['payment_method'])
        );
        
        // Validate required fields
        $required = array('first_name', 'last_name', 'email', 'address_1', 'city', 'state', 'postcode', 'country');
        foreach ($required as $field) {
            if (empty($billing_data[$field])) {
                wp_die(__('Please fill in all required fields.', 'custom-product-manager'));
            }
        }
        
        // Calculate total
        $total = cpm_get_cart_total();
        
        // Create order
        $order_number = cpm_generate_order_number();
        
        $order_data = array(
            'order_number' => $order_number,
            'user_id' => get_current_user_id() ? get_current_user_id() : 0,
            'billing_first_name' => $billing_data['first_name'],
            'billing_last_name' => $billing_data['last_name'],
            'billing_email' => $billing_data['email'],
            'billing_phone' => $billing_data['phone'],
            'billing_address_1' => $billing_data['address_1'],
            'billing_address_2' => $billing_data['address_2'],
            'billing_city' => $billing_data['city'],
            'billing_state' => $billing_data['state'],
            'billing_postcode' => $billing_data['postcode'],
            'billing_country' => $billing_data['country'],
            'payment_method' => $billing_data['payment_method'],
            'payment_status' => 'pending',
            'order_total' => $total,
            'order_status' => 'pending'
        );
        
        $wpdb->insert(
            CPM_Database::get_table('orders'),
            $order_data,
            array('%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s')
        );
        
        $order_id = $wpdb->insert_id;
        
        // Get product details for order items (using new main category structure)
        foreach ($cart_items as $item) {
            // Get variation data from cart item (new structure)
            $variation_data = !empty($item->variation_data) ? json_decode($item->variation_data, true) : null;
            
            $product_name = '';
            $main_category_name = '';
            $category_name = '';
            $country_name = '';
            $quantity_text = '';
            
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
                                
                                // Get quantity text
                                if (isset($variation_data['quantity_id']) && !empty($variation_data['quantity_id']) && isset($main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['countries'][$variation_data['country_id']]['quantities'][$variation_data['quantity_id']])) {
                                    $quantity_data = $main_categories[$variation_data['main_category_id']]['categories'][$variation_data['category_id']]['countries'][$variation_data['country_id']]['quantities'][$variation_data['quantity_id']];
                                    $quantity_text = isset($quantity_data['quantity']) ? $quantity_data['quantity'] : '';
                                }
                            }
                        }
                    }
                }
            }
            
            // Build full product description
            $product_description = $product_name;
            $variation_details = array();
            if ($main_category_name) {
                $variation_details[] = __('Main Category:', 'custom-product-manager') . ' ' . $main_category_name;
            }
            if ($category_name) {
                $variation_details[] = __('Category:', 'custom-product-manager') . ' ' . $category_name;
            }
            if ($country_name) {
                $variation_details[] = __('Country:', 'custom-product-manager') . ' ' . $country_name;
            }
            if ($quantity_text) {
                $variation_details[] = __('Quantity:', 'custom-product-manager') . ' ' . $quantity_text;
            }
            
            // Get page URL from variation data
            $page_url = '';
            if (is_array($variation_data) && isset($variation_data['page_url']) && !empty($variation_data['page_url'])) {
                $page_url = $variation_data['page_url'];
            }
            
            // Store full variation data for email
            $full_variation_data = array(
                'product_name' => $product_name,
                'main_category' => $main_category_name,
                'category' => $category_name,
                'country' => $country_name,
                'quantity' => $quantity_text,
                'page_url' => $page_url,
                'original_data' => $variation_data
            );
            
            $wpdb->insert(
                CPM_Database::get_table('order_items'),
                array(
                    'order_id' => $order_id,
                    'variation_id' => $item->variation_id ? $item->variation_id : 0,
                    'product_name' => $product_name ? $product_name : __('Product', 'custom-product-manager'),
                    'variation_data' => json_encode($full_variation_data),
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity
                ),
                array('%d', '%d', '%s', '%s', '%d', '%f', '%f')
            );
        }
        
        // Clear cart
        $session_id = cpm_get_session_id();
        $wpdb->delete(
            CPM_Database::get_table('cart'),
            array('session_id' => $session_id),
            array('%s')
        );
        
        // Send email
        $email = new CPM_Email();
        $email_sent = $email->send_order_confirmation($order_id);
        
        // Store email status in session for display
        if (!session_id()) {
            session_start();
        }
        $_SESSION['cpm_order_email_sent_' . $order_id] = $email_sent;
        
        // Redirect to thank you page or show success
        wp_redirect(add_query_arg(array('order_id' => $order_id, 'order_status' => 'success'), get_permalink(get_option('cpm_checkout_page_id'))));
        exit;
    }
}


