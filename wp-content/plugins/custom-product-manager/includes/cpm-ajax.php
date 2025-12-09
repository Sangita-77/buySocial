<?php
/**
 * AJAX handlers
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get main categories for product (frontend)
add_action('wp_ajax_cpm_get_main_categories', 'cpm_get_main_categories_ajax');
add_action('wp_ajax_nopriv_cpm_get_main_categories', 'cpm_get_main_categories_ajax');
function cpm_get_main_categories_ajax() {
    check_ajax_referer('cpm_frontend_nonce', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error(array('message' => __('Invalid product ID', 'custom-product-manager')));
    }
    
    $option_name = '_cpm_product_' . $product_id . '_main_categories';
    $saved_data = get_option($option_name, '');
    $main_categories = array();
    
    if ($saved_data) {
        $main_categories = json_decode($saved_data, true);
        if (!is_array($main_categories)) {
            $main_categories = array();
        }
    }
    
    wp_send_json_success(array('main_categories' => $main_categories));
}

// Add to cart with custom data structure
add_action('wp_ajax_cpm_add_to_cart_custom', 'cpm_add_to_cart_custom');
add_action('wp_ajax_nopriv_cpm_add_to_cart_custom', 'cpm_add_to_cart_custom');
function cpm_add_to_cart_custom() {
    check_ajax_referer('cpm_nonce', 'nonce');
    
    global $wpdb;
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $main_category_id = isset($_POST['main_category_id']) ? sanitize_text_field($_POST['main_category_id']) : '';
    $category_id = isset($_POST['category_id']) ? sanitize_text_field($_POST['category_id']) : '';
    $country_id = isset($_POST['country_id']) ? sanitize_text_field($_POST['country_id']) : '';
    $quantity_id = isset($_POST['quantity_id']) ? sanitize_text_field($_POST['quantity_id']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $price_id = isset($_POST['price_id']) ? sanitize_text_field($_POST['price_id']) : '';
    $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
    
    if (!$product_id || !$price || $price <= 0) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    $session_id = cpm_get_session_id();
    
    // Create variation data
    $variation_data = array(
        'product_id' => $product_id,
        'main_category_id' => $main_category_id,
        'category_id' => $category_id,
        'country_id' => $country_id,
        'quantity_id' => $quantity_id,
        'price_id' => $price_id,
        'page_url' => $page_url
    );
    
    // Check if item already in cart (without page_url for comparison)
    $variation_data_for_match = array(
        'product_id' => $product_id,
        'main_category_id' => $main_category_id,
        'category_id' => $category_id,
        'country_id' => $country_id,
        'quantity_id' => $quantity_id,
        'price_id' => $price_id
    );
    
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('cart') . " 
        WHERE session_id = %s AND variation_data LIKE %s",
        $session_id,
        '%' . $wpdb->esc_like(json_encode($variation_data_for_match)) . '%'
    ));
    
    if ($existing) {
        // Update quantity and page_url if it changed
        $existing_variation_data = json_decode($existing->variation_data, true);
        if (is_array($existing_variation_data)) {
            $existing_variation_data['page_url'] = $page_url;
        }
        $new_quantity = $existing->quantity + 1;
        $wpdb->update(
            CPM_Database::get_table('cart'),
            array(
                'quantity' => $new_quantity,
                'variation_data' => json_encode($variation_data)
            ),
            array('id' => $existing->id),
            array('%d', '%s'),
            array('%d')
        );
    } else {
        // Add new item
        $wpdb->insert(
            CPM_Database::get_table('cart'),
            array(
                'session_id' => $session_id,
                'variation_id' => 0,
                'quantity' => 1,
                'price' => $price,
                'variation_data' => json_encode($variation_data)
            ),
            array('%s', '%d', '%d', '%f', '%s')
        );
    }
    
    $cart_count = cpm_get_cart_count();
    
    wp_send_json_success(array(
        'message' => __('Added to cart', 'custom-product-manager'),
        'cart_count' => $cart_count
    ));
}

// Buy Now - Add to cart and redirect to checkout
add_action('wp_ajax_cpm_buy_now', 'cpm_buy_now');
add_action('wp_ajax_nopriv_cpm_buy_now', 'cpm_buy_now');
function cpm_buy_now() {
    check_ajax_referer('cpm_nonce', 'nonce');
    
    global $wpdb;
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $main_category_id = isset($_POST['main_category_id']) ? sanitize_text_field($_POST['main_category_id']) : '';
    $category_id = isset($_POST['category_id']) ? sanitize_text_field($_POST['category_id']) : '';
    $country_id = isset($_POST['country_id']) ? sanitize_text_field($_POST['country_id']) : '';
    $quantity_id = isset($_POST['quantity_id']) ? sanitize_text_field($_POST['quantity_id']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $price_id = isset($_POST['price_id']) ? sanitize_text_field($_POST['price_id']) : '';
    $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
    
    if (!$product_id || !$price || $price <= 0) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    $session_id = cpm_get_session_id();
    
    // Create variation data
    $variation_data = array(
        'product_id' => $product_id,
        'main_category_id' => $main_category_id,
        'category_id' => $category_id,
        'country_id' => $country_id,
        'quantity_id' => $quantity_id,
        'price_id' => $price_id,
        'page_url' => $page_url
    );
    
    // Clear existing cart items for this session (optional - you might want to keep them)
    // $wpdb->delete(CPM_Database::get_table('cart'), array('session_id' => $session_id), array('%s'));
    
    // Add item to cart
    $wpdb->insert(
        CPM_Database::get_table('cart'),
        array(
            'session_id' => $session_id,
            'variation_id' => 0,
            'quantity' => 1,
            'price' => $price,
            'variation_data' => json_encode($variation_data)
        ),
        array('%s', '%d', '%d', '%f', '%s')
    );
    
    // Get checkout URL
    $checkout_url = home_url('/checkout');
    if (function_exists('wc_get_checkout_url')) {
        $checkout_url = wc_get_checkout_url();
    }
    
    wp_send_json_success(array(
        'message' => __('Redirecting to checkout...', 'custom-product-manager'),
        'checkout_url' => $checkout_url
    ));
}

// Submit review
add_action('wp_ajax_cpm_submit_review', 'cpm_submit_review');
add_action('wp_ajax_nopriv_cpm_submit_review', 'cpm_submit_review');
function cpm_submit_review() {
    check_ajax_referer('cpm_submit_review', 'nonce');
    
    global $wpdb;
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $reviewer_name = isset($_POST['reviewer_name']) ? sanitize_text_field($_POST['reviewer_name']) : '';
    $reviewer_email = isset($_POST['reviewer_email']) ? sanitize_email($_POST['reviewer_email']) : '';
    $review_text = isset($_POST['review_text']) ? sanitize_textarea_field($_POST['review_text']) : '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    
    // Validate
    if (!$product_id || !$reviewer_name || !$reviewer_email || !$review_text || $rating < 1 || $rating > 5) {
        wp_send_json_error(array('message' => __('Please fill in all required fields correctly.', 'custom-product-manager')));
    }
    
    // Check if product exists
    $product = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('products') . " WHERE id = %d",
        $product_id
    ));
    
    if (!$product) {
        wp_send_json_error(array('message' => __('Product not found.', 'custom-product-manager')));
    }
    
    // Check if user has purchased (optional - can be enhanced)
    $verified_purchase = 0;
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $has_order = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . CPM_Database::get_table('orders') . " 
            WHERE user_id = %d AND billing_email = %s",
            $user_id, $reviewer_email
        ));
        if ($has_order > 0) {
            $verified_purchase = 1;
        }
    }
    
    // Save review with pending status
    $result = $wpdb->insert(
        CPM_Database::get_table('reviews'),
        array(
            'product_id' => $product_id,
            'reviewer_name' => $reviewer_name,
            'reviewer_email' => $reviewer_email,
            'review_text' => $review_text,
            'rating' => $rating,
            'status' => 'pending',
            'verified_purchase' => $verified_purchase
        ),
        array('%d', '%s', '%s', '%s', '%d', '%s', '%d')
    );
    
    if ($result === false) {
        error_log('CPM Review: Failed to insert review. Error: ' . $wpdb->last_error);
        wp_send_json_error(array('message' => __('Error saving review. Please try again.', 'custom-product-manager')));
    } else {
        error_log('CPM Review: Review saved successfully. ID: ' . $wpdb->insert_id);
        wp_send_json_success(array('message' => __('Review submitted successfully. It will be reviewed before being published.', 'custom-product-manager')));
    }
}

// Save category
add_action('wp_ajax_cpm_save_category', 'cpm_save_category');
function cpm_save_category() {
    check_ajax_referer('cpm_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'custom-product-manager')));
    }
    
    global $wpdb;
    
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';
    $display_order = isset($_POST['display_order']) ? intval($_POST['display_order']) : 0;
    
    if (!$product_id || !$name) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    $data = array(
        'product_id' => $product_id,
        'name' => $name,
        'description' => $description,
        'display_order' => $display_order,
        'status' => 'active'
    );
    
    if ($category_id) {
        $wpdb->update(
            CPM_Database::get_table('categories'),
            $data,
            array('id' => $category_id),
            array('%d', '%s', '%s', '%d', '%s'),
            array('%d')
        );
        $id = $category_id;
    } else {
        $wpdb->insert(
            CPM_Database::get_table('categories'),
            $data,
            array('%d', '%s', '%s', '%d', '%s')
        );
        $id = $wpdb->insert_id;
    }
    
    wp_send_json_success(array('category_id' => $id));
}

// Save subcategory
add_action('wp_ajax_cpm_save_subcategory', 'cpm_save_subcategory');
function cpm_save_subcategory() {
    check_ajax_referer('cpm_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'custom-product-manager')));
    }
    
    global $wpdb;
    
    $subcat_id = isset($_POST['subcat_id']) ? intval($_POST['subcat_id']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $field_type = isset($_POST['field_type']) ? sanitize_text_field($_POST['field_type']) : 'select';
    $options = isset($_POST['options']) ? array_map('sanitize_text_field', $_POST['options']) : array();
    
    if (!$category_id || !$name) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    $data = array(
        'category_id' => $category_id,
        'name' => $name,
        'field_type' => $field_type,
        'status' => 'active'
    );
    
    if ($subcat_id) {
        $wpdb->update(
            CPM_Database::get_table('subcategories'),
            $data,
            array('id' => $subcat_id),
            array('%d', '%s', '%s', '%s'),
            array('%d')
        );
        $id = $subcat_id;
        
        // Delete old options
        $wpdb->delete(
            CPM_Database::get_table('subcategory_options'),
            array('subcategory_id' => $subcat_id),
            array('%d')
        );
    } else {
        $wpdb->insert(
            CPM_Database::get_table('subcategories'),
            $data,
            array('%d', '%s', '%s', '%s')
        );
        $id = $wpdb->insert_id;
    }
    
    // Insert options
    if ($field_type === 'select' && !empty($options)) {
        foreach ($options as $index => $option) {
            if (trim($option)) {
                $wpdb->insert(
                    CPM_Database::get_table('subcategory_options'),
                    array(
                        'subcategory_id' => $id,
                        'option_value' => trim($option),
                        'display_order' => $index
                    ),
                    array('%d', '%s', '%d')
                );
            }
        }
    }
    
    wp_send_json_success(array('subcat_id' => $id));
}

// Save variation
add_action('wp_ajax_cpm_save_variation', 'cpm_save_variation');
function cpm_save_variation() {
    check_ajax_referer('cpm_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'custom-product-manager')));
    }
    
    global $wpdb;
    
    $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $variation_data = isset($_POST['variation_data']) ? $_POST['variation_data'] : array();
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    
    if (!$category_id || $price <= 0) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    $data = array(
        'category_id' => $category_id,
        'variation_data' => json_encode($variation_data),
        'price' => $price,
        'status' => 'active'
    );
    
    if ($variation_id) {
        $wpdb->update(
            CPM_Database::get_table('variations'),
            $data,
            array('id' => $variation_id),
            array('%d', '%s', '%f', '%s'),
            array('%d')
        );
        $id = $variation_id;
    } else {
        $wpdb->insert(
            CPM_Database::get_table('variations'),
            $data,
            array('%d', '%s', '%f', '%s')
        );
        $id = $wpdb->insert_id;
    }
    
    wp_send_json_success(array('variation_id' => $id));
}

// Get variation price (frontend)
add_action('wp_ajax_cpm_get_variation_price', 'cpm_get_variation_price');
add_action('wp_ajax_nopriv_cpm_get_variation_price', 'cpm_get_variation_price');
function cpm_get_variation_price() {
    check_ajax_referer('cpm_nonce', 'nonce');
    
    global $wpdb;
    
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $selected_values = isset($_POST['selected_values']) ? $_POST['selected_values'] : array();
    
    if (!$category_id || empty($selected_values)) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    // Get all variations for this category
    $variations = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('variations') . " 
        WHERE category_id = %d AND status = 'active'",
        $category_id
    ));
    
    // Find matching variation
    foreach ($variations as $variation) {
        $variation_data = json_decode($variation->variation_data, true);
        $match = true;
        
        foreach ($selected_values as $subcat_id => $value) {
            if (!isset($variation_data[$subcat_id]) || $variation_data[$subcat_id] != $value) {
                $match = false;
                break;
            }
        }
        
        if ($match) {
            wp_send_json_success(array(
                'price' => floatval($variation->price),
                'variation_id' => $variation->id
            ));
        }
    }
    
    wp_send_json_error(array('message' => __('No matching variation found', 'custom-product-manager')));
}

// Add to cart
add_action('wp_ajax_cpm_add_to_cart', 'cpm_add_to_cart');
add_action('wp_ajax_nopriv_cpm_add_to_cart', 'cpm_add_to_cart');
function cpm_add_to_cart() {
    check_ajax_referer('cpm_nonce', 'nonce');
    
    global $wpdb;
    
    $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if (!$variation_id || $quantity <= 0) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    // Get variation details
    $variation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('variations') . " WHERE id = %d",
        $variation_id
    ));
    
    if (!$variation) {
        wp_send_json_error(array('message' => __('Variation not found', 'custom-product-manager')));
    }
    
    $session_id = cpm_get_session_id();
    
    // Check if item already in cart
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('cart') . " 
        WHERE session_id = %s AND variation_id = %d",
        $session_id, $variation_id
    ));
    
    if ($existing) {
        // Update quantity
        $new_quantity = $existing->quantity + $quantity;
        $wpdb->update(
            CPM_Database::get_table('cart'),
            array('quantity' => $new_quantity),
            array('id' => $existing->id),
            array('%d'),
            array('%d')
        );
    } else {
        // Add new item
        $wpdb->insert(
            CPM_Database::get_table('cart'),
            array(
                'session_id' => $session_id,
                'variation_id' => $variation_id,
                'quantity' => $quantity,
                'price' => $variation->price,
                'variation_data' => $variation->variation_data
            ),
            array('%s', '%d', '%d', '%f', '%s')
        );
    }
    
    $cart_count = cpm_get_cart_count();
    
    wp_send_json_success(array(
        'message' => __('Added to cart', 'custom-product-manager'),
        'cart_count' => $cart_count
    ));
}

// Get cart count
add_action('wp_ajax_cpm_get_cart_count', 'cpm_get_cart_count_ajax');
add_action('wp_ajax_nopriv_cpm_get_cart_count', 'cpm_get_cart_count_ajax');
function cpm_get_cart_count_ajax() {
    check_ajax_referer('cpm_nonce', 'nonce');
    wp_send_json_success(array('count' => cpm_get_cart_count()));
}

// Session and cart functions are defined in cpm-functions.php

// Update cart item quantity
add_action('wp_ajax_cpm_update_cart_item', 'cpm_update_cart_item');
add_action('wp_ajax_nopriv_cpm_update_cart_item', 'cpm_update_cart_item');
function cpm_update_cart_item() {
    check_ajax_referer('cpm_nonce', 'nonce');
    
    global $wpdb;
    
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if (!$item_id || $quantity <= 0) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    $session_id = cpm_get_session_id();
    
    // Verify item belongs to current session
    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('cart') . " 
        WHERE id = %d AND session_id = %s",
        $item_id, $session_id
    ));
    
    if (!$item) {
        wp_send_json_error(array('message' => __('Item not found', 'custom-product-manager')));
    }
    
    // Update quantity
    $wpdb->update(
        CPM_Database::get_table('cart'),
        array('quantity' => $quantity),
        array('id' => $item_id),
        array('%d'),
        array('%d')
    );
    
    $subtotal = $item->price * $quantity;
    $cart_total = cpm_get_cart_total();
    
    wp_send_json_success(array(
        'subtotal' => cpm_format_price($subtotal),
        'cart_total' => cpm_format_price($cart_total)
    ));
}

// Remove cart item
add_action('wp_ajax_cpm_remove_cart_item', 'cpm_remove_cart_item');
add_action('wp_ajax_nopriv_cpm_remove_cart_item', 'cpm_remove_cart_item');
function cpm_remove_cart_item() {
    check_ajax_referer('cpm_nonce', 'nonce');
    
    global $wpdb;
    
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if (!$item_id) {
        wp_send_json_error(array('message' => __('Invalid data', 'custom-product-manager')));
    }
    
    $session_id = cpm_get_session_id();
    
    // Verify and delete item
    $deleted = $wpdb->delete(
        CPM_Database::get_table('cart'),
        array(
            'id' => $item_id,
            'session_id' => $session_id
        ),
        array('%d', '%s')
    );
    
    if ($deleted) {
        $cart_total = cpm_get_cart_total();
        wp_send_json_success(array(
            'cart_total' => cpm_format_price($cart_total),
            'cart_count' => cpm_get_cart_count()
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to remove item', 'custom-product-manager')));
    }
}

// Validate URL - Check if post exists
add_action('wp_ajax_cpm_validate_url', 'cpm_validate_url');
add_action('wp_ajax_nopriv_cpm_validate_url', 'cpm_validate_url');
function cpm_validate_url() {
    check_ajax_referer('cpm_nonce', 'nonce');
    
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    $selected_platform = isset($_POST['platform']) ? strtolower(sanitize_text_field($_POST['platform'])) : '';
    
    if (empty($url)) {
        wp_send_json_error(array('message' => __('Please enter a URL', 'custom-product-manager')));
    }
    
    // Ensure URL has protocol
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }
    
    // Parse URL to get domain and path
    $parsed_url = parse_url($url);
    if (!$parsed_url || !isset($parsed_url['host'])) {
        wp_send_json_error(array('message' => __('Invalid URL format', 'custom-product-manager')));
    }
    
    $host = strtolower($parsed_url['host']);
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? $parsed_url['query'] : '';
    
    // Detect platform from URL
    $url_platform = '';
    if (strpos($host, 'facebook.com') !== false || strpos($host, 'fb.com') !== false || strpos($host, 'm.facebook.com') !== false) {
        $url_platform = 'facebook';
    } elseif (strpos($host, 'instagram.com') !== false) {
        $url_platform = 'instagram';
    } elseif (strpos($host, 'twitter.com') !== false || strpos($host, 'x.com') !== false) {
        $url_platform = 'twitter';
    } elseif (strpos($host, 'youtube.com') !== false || strpos($host, 'youtu.be') !== false) {
        $url_platform = 'youtube';
    } elseif (strpos($host, 'tiktok.com') !== false) {
        $url_platform = 'tiktok';
    }
    
    // Check if URL platform matches selected platform
    if (!empty($selected_platform) && !empty($url_platform) && $selected_platform !== $url_platform) {
        $platform_names = array(
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'twitter' => 'Twitter/X',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok'
        );
        $selected_name = isset($platform_names[$selected_platform]) ? $platform_names[$selected_platform] : ucfirst($selected_platform);
        $url_name = isset($platform_names[$url_platform]) ? $platform_names[$url_platform] : ucfirst($url_platform);
        
        wp_send_json_error(array(
            'message' => sprintf(__('URL mismatch: You selected %s but the URL is for %s. Please enter a %s URL.', 'custom-product-manager'), $selected_name, $url_name, $selected_name)
        ));
    }
    
    // Check if it's a Facebook URL
    $is_facebook = ($url_platform === 'facebook');
    $is_instagram = ($url_platform === 'instagram');
    $is_twitter = ($url_platform === 'twitter');
    $is_youtube = ($url_platform === 'youtube');
    $is_tiktok = ($url_platform === 'tiktok');
    
    $is_post_url = false;
    
    if ($is_facebook) {
        // Check if it's a Facebook post URL pattern
        // Pattern 1: /username/posts/123456789
        if (preg_match('/\/[^\/]+\/(posts|photos|videos)\/\d+/', $path)) {
            $is_post_url = true;
        }
        // Pattern 2: /reel/123456789 or /reels/123456789 (Facebook Reels)
        elseif (preg_match('/\/(reel|reels)\/\d+/', $path)) {
            $is_post_url = true;
        }
        // Pattern 3: /permalink.php?story_fbid=123456789
        elseif (strpos($path, '/permalink.php') !== false && strpos($query, 'story_fbid=') !== false) {
            $is_post_url = true;
        }
        // Pattern 4: /story.php?story_fbid=123456789 (mobile)
        elseif (strpos($path, '/story.php') !== false && strpos($query, 'story_fbid=') !== false) {
            $is_post_url = true;
        }
        // Pattern 5: /photo.php?fbid=123456789
        elseif (strpos($path, '/photo.php') !== false && strpos($query, 'fbid=') !== false) {
            $is_post_url = true;
        }
        // Pattern 6: /groups/groupname/posts/123456789
        elseif (preg_match('/\/groups\/[^\/]+\/posts\/\d+/', $path)) {
            $is_post_url = true;
        }
        // Pattern 7: /watch/?v=123456789 (videos)
        elseif (strpos($path, '/watch') !== false && strpos($query, 'v=') !== false) {
            $is_post_url = true;
        }
        // Pattern 8: /videos/123456789 (video posts)
        elseif (preg_match('/\/videos\/\d+/', $path)) {
            $is_post_url = true;
        }
        // Check if it's just the homepage
        elseif ($path === '/' || $path === '' || $path === '/home.php' || $path === '/home/') {
            wp_send_json_error(array(
                'message' => __('This is the Facebook homepage, not a post URL. Please enter a specific post URL.', 'custom-product-manager')
            ));
        }
        
        if (!$is_post_url) {
            wp_send_json_error(array(
                'message' => __('This does not appear to be a valid Facebook post URL. Please enter a specific post URL (e.g., https://www.facebook.com/username/posts/123456789 or https://www.facebook.com/reel/123456789)', 'custom-product-manager')
            ));
        }
    } elseif ($is_instagram) {
        // Check if it's an Instagram post URL pattern
        // Pattern 1: /p/ABC123/ (regular posts)
        if (preg_match('/\/p\/[A-Za-z0-9_-]+\/?$/', $path)) {
            $is_post_url = true;
        }
        // Pattern 2: /reel/ABC123/ (Instagram Reels)
        elseif (preg_match('/\/reel\/[A-Za-z0-9_-]+\/?$/', $path)) {
            $is_post_url = true;
        }
        // Pattern 3: /tv/ABC123/ (IGTV)
        elseif (preg_match('/\/tv\/[A-Za-z0-9_-]+\/?$/', $path)) {
            $is_post_url = true;
        }
        // Pattern 4: /stories/username/123456789/ (Stories)
        elseif (preg_match('/\/stories\/[^\/]+\/\d+\/?$/', $path)) {
            $is_post_url = true;
        }
        // Check if it's just the homepage or profile
        elseif ($path === '/' || $path === '' || preg_match('/^\/[^\/]+\/?$/', $path)) {
            wp_send_json_error(array(
                'message' => __('This appears to be an Instagram profile or homepage, not a specific post. Please enter a post URL (e.g., https://www.instagram.com/p/ABC123/)', 'custom-product-manager')
            ));
        }
        
        if (!$is_post_url) {
            wp_send_json_error(array(
                'message' => __('This does not appear to be a valid Instagram post URL. Please enter a specific post URL (e.g., https://www.instagram.com/p/ABC123/ or https://www.instagram.com/reel/ABC123/)', 'custom-product-manager')
            ));
        }
    } elseif ($is_twitter) {
        // Check if it's a Twitter/X post URL pattern
        // Pattern: /username/status/123456789
        if (preg_match('/\/[^\/]+\/status\/\d+/', $path)) {
            $is_post_url = true;
        } elseif ($path === '/' || $path === '' || preg_match('/^\/[^\/]+\/?$/', $path)) {
            wp_send_json_error(array(
                'message' => __('This appears to be a Twitter/X profile or homepage, not a specific post. Please enter a post URL (e.g., https://twitter.com/username/status/123456789)', 'custom-product-manager')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('This does not appear to be a valid Twitter/X post URL. Please enter a specific post URL (e.g., https://twitter.com/username/status/123456789)', 'custom-product-manager')
            ));
        }
    } elseif ($is_youtube) {
        // Check if it's a YouTube video URL pattern
        // Pattern 1: /watch?v=ABC123
        if (strpos($path, '/watch') !== false && strpos($query, 'v=') !== false) {
            $is_post_url = true;
        }
        // Pattern 2: /v/ABC123 (short format)
        elseif (preg_match('/\/v\/[A-Za-z0-9_-]+/', $path)) {
            $is_post_url = true;
        }
        // Pattern 3: youtu.be/ABC123 (short URL)
        elseif (strpos($host, 'youtu.be') !== false && !empty($path) && $path !== '/') {
            $is_post_url = true;
        } else {
            wp_send_json_error(array(
                'message' => __('This does not appear to be a valid YouTube video URL. Please enter a specific video URL (e.g., https://www.youtube.com/watch?v=ABC123)', 'custom-product-manager')
            ));
        }
    } elseif ($is_tiktok) {
        // Check if it's a TikTok video URL pattern
        // Pattern: /@username/video/123456789
        if (preg_match('/\/@[^\/]+\/video\/\d+/', $path)) {
            $is_post_url = true;
        } elseif ($path === '/' || $path === '' || preg_match('/^\/@[^\/]+\/?$/', $path)) {
            wp_send_json_error(array(
                'message' => __('This appears to be a TikTok profile or homepage, not a specific video. Please enter a video URL (e.g., https://www.tiktok.com/@username/video/123456789)', 'custom-product-manager')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('This does not appear to be a valid TikTok video URL. Please enter a specific video URL (e.g., https://www.tiktok.com/@username/video/123456789)', 'custom-product-manager')
            ));
        }
    } elseif (!empty($selected_platform)) {
        // Platform selected but URL doesn't match any known platform
        wp_send_json_error(array(
            'message' => sprintf(__('The URL does not match the selected platform (%s). Please enter a valid URL for the selected platform.', 'custom-product-manager'), ucfirst($selected_platform))
        ));
    }
    
    // For Facebook URLs with valid format, we'll be more lenient
    // Facebook often blocks automated requests even for public content
    // So if the URL format is correct, we'll accept it even if we can't verify via HTTP
    
    // Use WordPress HTTP API to check if URL is accessible
    // Use a more realistic browser user-agent to avoid Facebook blocking
    $response = wp_remote_get($url, array(
        'timeout' => 10,
        'sslverify' => false,
        'redirection' => 5,
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'headers' => array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Connection' => 'keep-alive'
        )
    ));
    
    // If it's a Facebook URL with valid format, be lenient with validation
    if ($is_facebook && $is_post_url) {
        if (is_wp_error($response)) {
            // Facebook often blocks automated requests, but if URL format is correct, accept it
            wp_send_json_success(array(
                'message' => __('URL format is valid. Note: Facebook may block automated verification, but the URL appears to be a valid post URL.', 'custom-product-manager'),
                'warning' => true
            ));
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        // For Facebook, accept 200-299 and also 403 (blocked but format is correct)
        if ($response_code >= 200 && $response_code < 300) {
            $body = wp_remote_retrieve_body($response);
            
            // Check if body is not empty
            if (empty($body) || strlen($body) < 100) {
                // Still accept if format is correct
                wp_send_json_success(array(
                    'message' => __('URL format is valid. Unable to verify content, but the URL appears to be a valid post URL.', 'custom-product-manager'),
                    'warning' => true
                ));
            }
            
            // Check if page contains post-specific indicators
            $is_post_page = false;
            
            // Check for post-specific content indicators (including reels)
            if (
                strpos($body, 'story_fbid') !== false ||
                strpos($body, 'post_id') !== false ||
                strpos($body, 'permalink') !== false ||
                strpos($body, '/reel/') !== false ||
                strpos($body, 'video_id') !== false ||
                preg_match('/data-ft="[^"]*"top_level_post_id[^"]*"/', $body) ||
                preg_match('/"post_id":\s*"\d+"/', $body) ||
                preg_match('/story_fbid[=:]\s*["\']?\d+/', $body) ||
                preg_match('/"video_id":\s*"\d+"/', $body)
            ) {
                $is_post_page = true;
            }
            
            // Check if it's redirected to login or homepage
            if (
                (strpos($body, 'login') !== false || strpos($body, 'Log In') !== false) && 
                (strpos($body, 'Sign Up') !== false || strpos($body, 'Connect with friends') !== false)
            ) {
                wp_send_json_error(array(
                    'message' => __('This post requires login to view. Please ensure the post is public and accessible without logging in.', 'custom-product-manager')
                ));
            }
            
            // Check if Facebook is showing an error page
            if (
                strpos($body, 'This content isn\'t available') !== false ||
                strpos($body, 'Content isn\'t available') !== false ||
                strpos($body, 'Page Not Found') !== false ||
                strpos($body, 'Sorry, this content isn\'t available') !== false
            ) {
                wp_send_json_error(array(
                    'message' => __('This post is not available. It may have been deleted, made private, or the URL is incorrect.', 'custom-product-manager')
                ));
            }
            
            // If we confirmed it's a post page, great!
            if ($is_post_page) {
                wp_send_json_success(array(
                    'message' => __('Post URL is valid and accessible', 'custom-product-manager'),
                    'status_code' => $response_code
                ));
            } else {
                // Check if it looks like homepage
                if (
                    strpos($body, 'Connect with friends') !== false ||
                    strpos($body, 'Create a Page') !== false ||
                    (strpos($body, 'home.php') !== false && strpos($body, 'feed') === false)
                ) {
                    wp_send_json_error(array(
                        'message' => __('This appears to be the Facebook homepage, not a specific post. Please enter a post URL.', 'custom-product-manager')
                    ));
                } else {
                    // Format is correct, accept it
                    wp_send_json_success(array(
                        'message' => __('URL format is valid. The post appears to be accessible.', 'custom-product-manager'),
                        'status_code' => $response_code
                    ));
                }
            }
        } elseif ($response_code == 403) {
            // Facebook blocked the request, but URL format is correct, so accept it
            wp_send_json_success(array(
                'message' => __('URL format is valid. Facebook may be blocking automated verification, but the URL appears to be a valid post URL.', 'custom-product-manager'),
                'warning' => true
            ));
        } elseif ($response_code == 404) {
            wp_send_json_error(array(
                'message' => __('Post not found. The URL may be incorrect or the post may have been deleted.', 'custom-product-manager'),
                'status_code' => $response_code
            ));
        } else {
            // Other errors, but format is correct, so accept with warning
            wp_send_json_success(array(
                'message' => __('URL format is valid. Unable to fully verify due to server response, but the URL appears to be a valid post URL.', 'custom-product-manager'),
                'warning' => true
            ));
        }
    } else {
        // For non-Facebook URLs or invalid format, use strict validation
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            
            // Provide more specific error messages
            if (strpos($error_message, 'timeout') !== false || strpos($error_message, 'timed out') !== false) {
                wp_send_json_error(array(
                    'message' => __('Request timed out. The URL may be slow to respond. Please try again or verify the URL is correct.', 'custom-product-manager')
                ));
            } elseif (strpos($error_message, 'resolve') !== false || strpos($error_message, 'DNS') !== false) {
                wp_send_json_error(array(
                    'message' => __('Unable to reach the URL. Please check your internet connection and verify the URL is correct.', 'custom-product-manager')
                ));
            } else {
                wp_send_json_error(array(
                    'message' => __('Unable to access the URL. Please verify the URL is correct and accessible.', 'custom-product-manager'),
                    'error' => $error_message
                ));
            }
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Handle different HTTP status codes
        if ($response_code >= 200 && $response_code < 300) {
            $body = wp_remote_retrieve_body($response);
            
            // Check if body is not empty
            if (empty($body) || strlen($body) < 100) {
                wp_send_json_error(array(
                    'message' => __('The page appears to be empty. The post may not exist or may have been removed.', 'custom-product-manager')
                ));
            }
            
            wp_send_json_success(array(
                'message' => __('Post URL is valid and accessible', 'custom-product-manager'),
                'status_code' => $response_code
            ));
        } elseif ($response_code == 403) {
            wp_send_json_error(array(
                'message' => __('Access denied. The URL may require authentication or may be blocking automated requests.', 'custom-product-manager'),
                'status_code' => $response_code
            ));
        } elseif ($response_code == 404) {
            wp_send_json_error(array(
                'message' => __('Post not found. The URL may be incorrect or the post may have been deleted.', 'custom-product-manager'),
                'status_code' => $response_code
            ));
        } elseif ($response_code >= 500) {
            wp_send_json_error(array(
                'message' => __('Server error. The server may be experiencing issues. Please try again later.', 'custom-product-manager'),
                'status_code' => $response_code
            ));
        } else {
            wp_send_json_error(array(
                'message' => sprintf(__('URL returned error (HTTP %d). The post may not be accessible or the URL may be incorrect.', 'custom-product-manager'), $response_code),
                'status_code' => $response_code
            ));
        }
    }
}

