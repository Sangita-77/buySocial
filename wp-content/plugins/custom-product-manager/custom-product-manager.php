<?php
/**
 * Plugin Name: Custom Product Manager
 * Plugin URI: https://example.com/custom-product-manager
 * Description: A custom WooCommerce-like system with hierarchical product variations for selling social media services
 * Version: 1.0.0
 * Author: Sangita Singh
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: custom-product-manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CPM_VERSION', '1.0.0');
define('CPM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CPM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CPM_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class Custom_Product_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once CPM_PLUGIN_DIR . 'includes/class-cpm-database.php';
        require_once CPM_PLUGIN_DIR . 'includes/class-cpm-admin.php';
        require_once CPM_PLUGIN_DIR . 'includes/class-cpm-frontend.php';
        require_once CPM_PLUGIN_DIR . 'includes/class-cpm-cart.php';
        require_once CPM_PLUGIN_DIR . 'includes/class-cpm-checkout.php';
        require_once CPM_PLUGIN_DIR . 'includes/class-cpm-email.php';
        require_once CPM_PLUGIN_DIR . 'includes/cpm-ajax.php';
        require_once CPM_PLUGIN_DIR . 'includes/cpm-functions.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array('CPM_Database', 'activate'));
register_activation_hook(__FILE__, function() {
    // Flush rewrite rules on activation
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
});
        register_deactivation_hook(__FILE__, array('CPM_Database', 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Initialize admin
        if (is_admin()) {
            new CPM_Admin();
        }
        
        // Initialize frontend
        if (!is_admin()) {
            new CPM_Frontend();
        }
        
        // Initialize cart
        new CPM_Cart();
        
        // Initialize checkout
        new CPM_Checkout();
        
        // Initialize email
        new CPM_Email();
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('custom-product-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('cpm-frontend-style', CPM_PLUGIN_URL . 'assets/css/frontend.css', array(), CPM_VERSION);
        wp_enqueue_script('cpm-frontend-script', CPM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), CPM_VERSION, true);
        
        wp_localize_script('cpm-frontend-script', 'cpmAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cpm_nonce')
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Check if we're on any of our plugin pages by checking page parameter
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        
        if (strpos($page, 'cpm') === false && strpos($hook, 'cpm') === false) {
            return;
        }
        
        // Ensure WordPress media scripts are available for image upload fields
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }
        
        wp_enqueue_style('cpm-admin-style', CPM_PLUGIN_URL . 'assets/css/admin.css', array(), CPM_VERSION);
        wp_enqueue_script('cpm-admin-script', CPM_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CPM_VERSION, true);
        
        wp_localize_script('cpm-admin-script', 'cpmAdminAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cpm_admin_nonce')
        ));
    }
}

// Initialize the plugin
function CPM() {
    return Custom_Product_Manager::get_instance();
}

// Start the plugin
CPM();

