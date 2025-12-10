<?php
/**
 * Database handler class
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPM_Database {
    
    /**
     * Create database tables on activation
     */
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Main products table
        $table_products = $wpdb->prefix . 'cpm_products';
        $sql_products = "CREATE TABLE IF NOT EXISTS $table_products (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Categories table
        $table_categories = $wpdb->prefix . 'cpm_categories';
        $sql_categories = "CREATE TABLE IF NOT EXISTS $table_categories (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            name varchar(255) NOT NULL,
            description text,
            display_order int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id)
        ) $charset_collate;";
        
        // Sub-categories table
        $table_subcategories = $wpdb->prefix . 'cpm_subcategories';
        $sql_subcategories = "CREATE TABLE IF NOT EXISTS $table_subcategories (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            category_id bigint(20) UNSIGNED NOT NULL,
            name varchar(255) NOT NULL,
            field_type varchar(50) DEFAULT 'select',
            display_order int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id)
        ) $charset_collate;";
        
        // Sub-category options table (for select/dropdown fields)
        $table_subcat_options = $wpdb->prefix . 'cpm_subcategory_options';
        $sql_subcat_options = "CREATE TABLE IF NOT EXISTS $table_subcat_options (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            subcategory_id bigint(20) UNSIGNED NOT NULL,
            option_value varchar(255) NOT NULL,
            display_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY subcategory_id (subcategory_id)
        ) $charset_collate;";
        
        // Product variations/prices table
        $table_variations = $wpdb->prefix . 'cpm_variations';
        $sql_variations = "CREATE TABLE IF NOT EXISTS $table_variations (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            category_id bigint(20) UNSIGNED NOT NULL,
            variation_data longtext NOT NULL,
            price decimal(10,2) NOT NULL,
            stock_status varchar(20) DEFAULT 'in_stock',
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id)
        ) $charset_collate;";
        
        // Cart table
        $table_cart = $wpdb->prefix . 'cpm_cart';
        $sql_cart = "CREATE TABLE IF NOT EXISTS $table_cart (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            variation_id bigint(20) UNSIGNED NOT NULL,
            quantity int(11) DEFAULT 1,
            price decimal(10,2) NOT NULL,
            variation_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY variation_id (variation_id)
        ) $charset_collate;";
        
        // Orders table
        $table_orders = $wpdb->prefix . 'cpm_orders';
        $sql_orders = "CREATE TABLE IF NOT EXISTS $table_orders (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_number varchar(50) NOT NULL,
            user_id bigint(20) UNSIGNED,
            billing_first_name varchar(255),
            billing_last_name varchar(255),
            billing_email varchar(255),
            billing_phone varchar(50),
            billing_address_1 varchar(255),
            billing_address_2 varchar(255),
            billing_city varchar(100),
            billing_state varchar(100),
            billing_postcode varchar(20),
            billing_country varchar(100),
            payment_method varchar(100),
            payment_status varchar(50) DEFAULT 'pending',
            order_total decimal(10,2) NOT NULL,
            order_status varchar(50) DEFAULT 'pending',
            order_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_number (order_number),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        // Order items table
        $table_order_items = $wpdb->prefix . 'cpm_order_items';
        $sql_order_items = "CREATE TABLE IF NOT EXISTS $table_order_items (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id bigint(20) UNSIGNED NOT NULL,
            variation_id bigint(20) UNSIGNED NOT NULL,
            product_name varchar(255),
            variation_data longtext,
            quantity int(11) DEFAULT 1,
            price decimal(10,2) NOT NULL,
            subtotal decimal(10,2) NOT NULL,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY variation_id (variation_id)
        ) $charset_collate;";
        
        // Reviews table
        $table_reviews = $wpdb->prefix . 'cpm_reviews';
        $sql_reviews = "CREATE TABLE IF NOT EXISTS $table_reviews (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            order_id bigint(20) UNSIGNED DEFAULT NULL,
            reviewer_name varchar(255) NOT NULL,
            reviewer_email varchar(255) NOT NULL,
            rating int(1) NOT NULL DEFAULT 5,
            review_text text NOT NULL,
            status varchar(20) DEFAULT 'pending',
            verified_purchase tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY status (status),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_products);
        dbDelta($sql_categories);
        dbDelta($sql_subcategories);
        dbDelta($sql_subcat_options);
        dbDelta($sql_variations);
        dbDelta($sql_cart);
        dbDelta($sql_orders);
        dbDelta($sql_order_items);
        dbDelta($sql_reviews);
        
        // Create pages
        self::create_pages();
    }
    
    /**
     * Create necessary pages
     */
    private static function create_pages() {
        // Products page
        $products_page = get_page_by_path('products');
        if (!$products_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Products',
                'post_content' => '[cpm_products]',
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
            update_option('cpm_products_page_id', $page_id);
        }
        
        // Cart page
        $cart_page = get_page_by_path('cart');
        if (!$cart_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Cart',
                'post_content' => '[cpm_cart]',
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
            update_option('cpm_cart_page_id', $page_id);
        }
        
        // Checkout page
        $checkout_page = get_page_by_path('checkout');
        if (!$checkout_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Checkout',
                'post_content' => '[cpm_checkout]',
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
            update_option('cpm_checkout_page_id', $page_id);
        }
        
        // Track Order page - create if doesn't exist
        $track_order_page = get_page_by_path('track-order');
        if (!$track_order_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Track Order',
                'post_content' => '<!-- Track Order Page - Content handled by plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'track-order'
            ));
            update_option('cpm_track_order_page_id', $page_id);
        } else {
            // Update existing page ID
            update_option('cpm_track_order_page_id', $track_order_page->ID);
        }
        
        // Login page
        $login_page = get_page_by_path('login');
        if (!$login_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Login',
                'post_content' => '<!-- Login Page - Content handled by plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'login'
            ));
            update_option('cpm_login_page_id', $page_id);
        }
        
        // Login page
        $login_page = get_page_by_path('login');
        if (!$login_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Login',
                'post_content' => '<!-- Login Page - Content handled by plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'login'
            ));
            update_option('cpm_login_page_id', $page_id);
        } else {
            update_option('cpm_login_page_id', $login_page->ID);
        }
        
        // Register page
        $register_page = get_page_by_path('register');
        if (!$register_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Register',
                'post_content' => '<!-- Register Page - Content handled by plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'register'
            ));
            update_option('cpm_register_page_id', $page_id);
        } else {
            update_option('cpm_register_page_id', $register_page->ID);
        }
        
        // My Account page
        $my_account_page = get_page_by_path('my-account');
        if (!$my_account_page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'My Account',
                'post_content' => '<!-- My Account Page - Content handled by plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'my-account'
            ));
            update_option('cpm_my_account_page_id', $page_id);
        } else {
            update_option('cpm_my_account_page_id', $my_account_page->ID);
        }
        
        // Set plugin installation date for statistics
        if (!get_option('cpm_install_date')) {
            update_option('cpm_install_date', current_time('mysql'));
        }
        
        // Flush rewrite rules to register new endpoints
        flush_rewrite_rules();
    }
    
    /**
     * Drop database tables on deactivation (optional - commented out for safety)
     */
    public static function deactivate() {
        // Uncomment below if you want to drop tables on deactivation
        /*
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'cpm_products',
            $wpdb->prefix . 'cpm_categories',
            $wpdb->prefix . 'cpm_subcategories',
            $wpdb->prefix . 'cpm_subcategory_options',
            $wpdb->prefix . 'cpm_variations',
            $wpdb->prefix . 'cpm_cart',
            $wpdb->prefix . 'cpm_orders',
            $wpdb->prefix . 'cpm_order_items'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        */
    }
    
    /**
     * Get database table name
     */
    public static function get_table($table_name) {
        global $wpdb;
        return $wpdb->prefix . 'cpm_' . $table_name;
    }
}


