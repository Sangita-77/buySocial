<?php
/**
 * Cart class
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPM_Cart {
    
    public function __construct() {
        add_shortcode('cpm_cart', array($this, 'cart_shortcode'));
    }
    
    /**
     * Cart shortcode
     */
    public function cart_shortcode() {
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/cart.php';
        return ob_get_clean();
    }
}





