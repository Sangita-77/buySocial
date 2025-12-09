<?php
/**
 * Helper functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get session ID
 */
function cpm_get_session_id() {
    if (!session_id()) {
        session_start();
    }
    if (!isset($_SESSION['cpm_session_id'])) {
        $_SESSION['cpm_session_id'] = wp_generate_password(32, false);
    }
    return $_SESSION['cpm_session_id'];
}

/**
 * Get cart count
 */
function cpm_get_cart_count() {
    global $wpdb;
    $session_id = cpm_get_session_id();
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(quantity) FROM " . CPM_Database::get_table('cart') . " WHERE session_id = %s",
        $session_id
    ));
    
    return intval($count);
}

/**
 * Get cart items
 */
function cpm_get_cart_items() {
    global $wpdb;
    $session_id = cpm_get_session_id();
    
    // Get cart items - variation_data is stored directly in cart table for new items
    $items = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('cart') . " WHERE session_id = %s ORDER BY id DESC",
        $session_id
    ));
    
    return $items ? $items : array();
}

/**
 * Get cart total
 */
function cpm_get_cart_total() {
    global $wpdb;
    $session_id = cpm_get_session_id();
    
    $total = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(price * quantity) FROM " . CPM_Database::get_table('cart') . " WHERE session_id = %s",
        $session_id
    ));
    
    return floatval($total);
}

/**
 * Format price
 */
function cpm_format_price($price) {
    return '$' . number_format($price, 2);
}

/**
 * Generate order number
 */
function cpm_generate_order_number() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(wp_generate_password(8, false));
}


