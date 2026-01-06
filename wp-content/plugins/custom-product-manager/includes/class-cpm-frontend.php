<?php
/**
 * Frontend class
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPM_Frontend {
    
    public function __construct() {
        add_shortcode('cpm_products', array($this, 'products_shortcode'));
        add_shortcode('cpm_product', array($this, 'product_shortcode'));
        add_shortcode('cpm_main_category_details', array($this, 'main_category_details_shortcode'));
        add_shortcode('cpm_track_order', array($this, 'track_order_shortcode'));
        add_shortcode('cpm_statistics', array($this, 'statistics_shortcode'));
        // Automatically display statistics on home page
        add_filter('the_content', array($this, 'auto_display_statistics'), 20);
        add_action('wp', array($this, 'init_session'));
        add_action('wp', array($this, 'handle_my_account_subpages_wp'), 5);
        add_action('template_redirect', array($this, 'handle_track_order_page'), 5);
        add_action('template_redirect', array($this, 'handle_main_category_page'), 5);
        add_action('template_redirect', array($this, 'handle_login_register_pages'), 10);
        add_action('wp_head', array($this, 'add_product_dropdown_to_header'));
        add_action('wp_footer', array($this, 'add_dropdown_script'));
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_filter('show_admin_bar', array($this, 'hide_admin_bar'));
        add_filter('wp_nav_menu_objects', array($this, 'hide_login_register_menu_items'), 10, 2);
        add_action('wp_head', array($this, 'hide_login_register_css'));
        
        // Flush rewrite rules if needed (one-time on version update)
        add_action('admin_init', array($this, 'maybe_flush_rewrite_rules'));
        
        // Intercept requests for my-account subpages before 404
        add_filter('request', array($this, 'intercept_my_account_requests'));
        
        // Prevent 404 for my-account subpages
        add_action('parse_request', array($this, 'handle_my_account_subpages_early'), 1);
        add_filter('status_header', array($this, 'prevent_404_for_my_account'), 10, 4);
    }
    
    /**
     * Hide WordPress admin bar for all users (or only non-admins)
     */
    public function hide_admin_bar($show) {
        // Hide admin bar for all users
        return false;
        
        // Or hide only for non-admin users (uncomment to use):
        // if (!current_user_can('administrator')) {
        //     return false;
        // }
        // return $show;
    }
    
    /**
     * Hide Login and Register menu items when user is logged in
     */
    public function hide_login_register_menu_items($items, $args) {
        if (!is_user_logged_in()) {
            return $items;
        }
        
        // Remove Login and Register menu items for logged-in users
        foreach ($items as $key => $item) {
            $should_remove = false;
            
            // Check by page slug
            if (isset($item->object) && $item->object === 'page' && isset($item->object_id)) {
                $page = get_post($item->object_id);
                if ($page && in_array($page->post_name, array('login', 'register'))) {
                    $should_remove = true;
                }
            }
            
            // Check by URL
            if (isset($item->url)) {
                $url_lower = strtolower($item->url);
                if (strpos($url_lower, '/login') !== false || strpos($url_lower, '/register') !== false) {
                    $should_remove = true;
                }
            }
            
            // Check by title
            if (isset($item->title)) {
                $title_lower = strtolower(strip_tags($item->title));
                if (in_array(trim($title_lower), array('login', 'register', 'sign up', 'signup'))) {
                    $should_remove = true;
                }
            }
            
            // Check by post name in database
            if (isset($item->object_id)) {
                $post_name = get_post_field('post_name', $item->object_id);
                if (in_array($post_name, array('login', 'register'))) {
                    $should_remove = true;
                }
            }
            
            if ($should_remove) {
                unset($items[$key]);
            }
        }
        
        return $items;
    }
    
    /**
     * Add CSS to hide login/register menu items and Products navigation
     */
    public function hide_login_register_css() {
        ?>
        <style type="text/css">
            /* Hide Products link in WordPress block navigation */
            .wp-block-pages-list__item__link.wp-block-navigation-item__content,
            a.wp-block-pages-list__item__link.wp-block-navigation-item__content,
            .wp-block-navigation-item__content.wp-block-pages-list__item__link,
            a.wp-block-navigation-item__content.wp-block-pages-list__item__link,
            /* Hide Products link by text content */
            .wp-block-pages-list__item__link:contains("Products"),
            a.wp-block-pages-list__item__link:contains("Products"),
            /* Hide parent list item containing Products link */
            li:has(.wp-block-pages-list__item__link.wp-block-navigation-item__content[href*="/products"]),
            li:has(a.wp-block-pages-list__item__link.wp-block-navigation-item__content[href*="/products"]),
            li:has(a.wp-block-pages-list__item__link[href*="/products"]) {
                display: none !important;
            }
            
            <?php if (is_user_logged_in()) : ?>
            /* Hide Login and Register menu items - Multiple selectors for compatibility */
            .menu-item a[href*="/login"],
            .menu-item a[href*="/register"],
            .menu-item a[href*="login"],
            .menu-item a[href*="register"],
            nav a[href*="/login"],
            nav a[href*="/register"],
            nav a[href*="login"],
            nav a[href*="register"],
            ul.menu a[href*="/login"],
            ul.menu a[href*="/register"],
            ul.menu a[href*="login"],
            ul.menu a[href*="register"],
            #menu a[href*="/login"],
            #menu a[href*="/register"],
            #menu a[href*="login"],
            #menu a[href*="register"],
            header a[href*="/login"],
            header a[href*="/register"],
            header a[href*="login"],
            header a[href*="register"] {
                display: none !important;
            }
            
            /* Hide parent list items containing login/register links */
            li.menu-item:has(a[href*="/login"]),
            li.menu-item:has(a[href*="/register"]),
            li:has(a[href*="/login"]),
            li:has(a[href*="/register"]) {
                display: none !important;
            }
            <?php endif; ?>
        </style>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Hide Products link in WordPress block navigation
            $('.wp-block-pages-list__item__link.wp-block-navigation-item__content').each(function() {
                var $link = $(this);
                var href = $link.attr('href') || '';
                var text = $link.text().trim().toLowerCase();
                // Hide if it's a Products link
                if (href.indexOf('/products') !== -1 || href.indexOf('products') !== -1 || 
                    text === 'products' || text.indexOf('product') !== -1) {
                    $link.hide();
                    $link.closest('li').hide();
                }
            });
            
            // Also hide by href containing products
            $('a.wp-block-pages-list__item__link[href*="/products"], a.wp-block-pages-list__item__link[href*="products"]').hide().closest('li').hide();
            $('a.wp-block-navigation-item__content[href*="/products"], a.wp-block-navigation-item__content[href*="products"]').hide().closest('li').hide();
            
            <?php if (is_user_logged_in()) : ?>
            // Hide login/register menu items using JavaScript as fallback
            if ($('body').hasClass('logged-in')) {
                $('a[href*="/login"], a[href*="/register"], a[href*="login"], a[href*="register"]').each(function() {
                    var $link = $(this);
                    var href = $link.attr('href') || '';
                    if (href.indexOf('/login') !== -1 || href.indexOf('/register') !== -1 || 
                        href.indexOf('login') !== -1 || href.indexOf('register') !== -1) {
                        $link.closest('li').hide();
                    }
                });
            }
            <?php endif; ?>
        });
        </script>
        <?php
    }
    
    /**
     * Initialize session
     */
    public function init_session() {
        if (!session_id()) {
            session_start();
        }
        
        // Handle main category details page - replace entire page content
        if (isset($_GET['cpm_main_category']) && $_GET['cpm_main_category'] == '1') {
            add_filter('the_content', array($this, 'display_main_category_details'), 999);
            add_action('wp_head', array($this, 'hide_page_title_for_main_category'));
        }
    }
    
    /**
     * Handle track order page via template redirect
     */
    public function handle_track_order_page() {
        // Check if accessing track-order URL
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        // Check for /track-order in URL or cpm_track_order parameter
        if (strpos($request_uri, '/track-order') !== false || isset($_GET['cpm_track_order'])) {
            // Replace content on any page
            add_filter('the_content', array($this, 'display_track_order'), 999);
            add_action('wp_head', array($this, 'hide_page_title_for_track_order'));
        }
    }
    
    /**
     * Handle my-account subpages via wp action (runs after parse_request)
     */
    public function handle_my_account_subpages_wp() {
        global $wp_query, $wp;
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        // Check for endpoint values (from add_rewrite_endpoint)
        $orders_endpoint = isset($wp->query_vars['orders']) ? $wp->query_vars['orders'] : false;
        $edit_account_endpoint = isset($wp->query_vars['edit-account']) ? $wp->query_vars['edit-account'] : false;
        
        // Check if it's a my-account subpage via URL or endpoint
        if ($orders_endpoint !== false || strpos($request_uri, '/my-account/orders') !== false) {
            // Ensure my-account page exists
            $this->ensure_my_account_page();
            
            // Get the my-account page
            $page = get_page_by_path('my-account');
            if ($page) {
                // Set up the query to use the my-account page
                $wp_query->is_404 = false;
                $wp_query->is_page = true;
                $wp_query->is_singular = true;
                $wp_query->queried_object = $page;
                $wp_query->queried_object_id = $page->ID;
                $wp_query->posts = array($page);
                $wp_query->post_count = 1;
                $wp_query->found_posts = 1;
                $wp_query->max_num_pages = 1;
                
                // Set query vars
                $wp->query_vars['cpm_page'] = 'my_account';
                $wp->query_vars['cpm_account_page'] = 'orders';
                
                // Set global post
                $GLOBALS['post'] = $page;
                wp_reset_postdata();
            }
        } elseif ($edit_account_endpoint !== false || strpos($request_uri, '/my-account/edit-account') !== false) {
            // Ensure my-account page exists
            $this->ensure_my_account_page();
            
            // Get the my-account page
            $page = get_page_by_path('my-account');
            if ($page) {
                // Set up the query to use the my-account page
                $wp_query->is_404 = false;
                $wp_query->is_page = true;
                $wp_query->is_singular = true;
                $wp_query->queried_object = $page;
                $wp_query->queried_object_id = $page->ID;
                $wp_query->posts = array($page);
                $wp_query->post_count = 1;
                $wp_query->found_posts = 1;
                $wp_query->max_num_pages = 1;
                
                // Set query vars
                $wp->query_vars['cpm_page'] = 'my_account';
                $wp->query_vars['cpm_account_page'] = 'edit_account';
                
                // Set global post
                $GLOBALS['post'] = $page;
                wp_reset_postdata();
            }
        }
    }
    
    /**
     * Intercept requests for my-account subpages to prevent 404
     */
    public function intercept_my_account_requests($query_vars) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        // Check if it's a my-account subpage
        if (strpos($request_uri, '/my-account/orders') !== false) {
            $query_vars['cpm_page'] = 'my_account';
            $query_vars['cpm_account_page'] = 'orders';
            // Set pagename to my-account to prevent 404
            $query_vars['pagename'] = 'my-account';
        } elseif (strpos($request_uri, '/my-account/edit-account') !== false) {
            $query_vars['cpm_page'] = 'my_account';
            $query_vars['cpm_account_page'] = 'edit_account';
            // Set pagename to my-account to prevent 404
            $query_vars['pagename'] = 'my-account';
        }
        
        return $query_vars;
    }
    
    /**
     * Handle my-account subpages early in parse_request
     */
    public function handle_my_account_subpages_early($wp) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        // Check if it's a my-account subpage
        if (strpos($request_uri, '/my-account/orders') !== false) {
            $wp->query_vars['cpm_page'] = 'my_account';
            $wp->query_vars['cpm_account_page'] = 'orders';
            $wp->query_vars['pagename'] = 'my-account';
            unset($wp->query_vars['error']);
        } elseif (strpos($request_uri, '/my-account/edit-account') !== false) {
            $wp->query_vars['cpm_page'] = 'my_account';
            $wp->query_vars['cpm_account_page'] = 'edit_account';
            $wp->query_vars['pagename'] = 'my-account';
            unset($wp->query_vars['error']);
        }
    }
    
    /**
     * Prevent 404 status for my-account subpages
     */
    public function prevent_404_for_my_account($status_header, $code, $description, $protocol) {
        if ($code == 404) {
            $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            
            // Check if it's a my-account subpage
            if (strpos($request_uri, '/my-account/orders') !== false || 
                strpos($request_uri, '/my-account/edit-account') !== false) {
                // Return 200 status instead of 404
                return "$protocol 200 OK";
            }
        }
        
        return $status_header;
    }
    
    /**
     * Handle main category page via template redirect
     */
    public function handle_main_category_page() {
        if (isset($_GET['cpm_main_category']) && $_GET['cpm_main_category'] == '1') {
            // Add filter to replace content
            add_filter('the_content', array($this, 'display_main_category_details'), 999);
            add_action('wp_head', array($this, 'hide_page_title_for_main_category'));
        }
    }
    
    /**
     * Hide page title for track order
     */
    public function hide_page_title_for_track_order() {
        echo '<style>.entry-title, .page-title, h1.entry-title, h1.page-title { display: none !important; }</style>';
    }
    
    /**
     * Display track order in content
     */
    public function display_track_order($content) {
        $request_uri = $_SERVER['REQUEST_URI'];
        if (strpos($request_uri, '/track-order') !== false || isset($_GET['cpm_track_order'])) {
            // Replace content with track order
            return $this->track_order_shortcode(array());
        }
        return $content;
    }
    
    /**
     * Hide page title for main category details
     */
    public function hide_page_title_for_main_category() {
        if (isset($_GET['cpm_main_category']) && $_GET['cpm_main_category'] == '1') {
            echo '<style>.entry-title, .page-title, h1.entry-title, h1.page-title { display: none !important; }</style>';
        }
    }
    
    /**
     * Display main category details in content
     */
    public function display_main_category_details($content) {
        if (isset($_GET['cpm_main_category']) && $_GET['cpm_main_category'] == '1') {
            // Replace content with main category details
            return $this->main_category_details_shortcode(array());
        }
        return $content;
    }
    
    /**
     * Products listing shortcode
     */
    public function products_shortcode($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'product_id' => 0
        ), $atts);
        
        if ($atts['product_id']) {
            // Show single product
            return $this->display_product($atts['product_id']);
        }
        
        // Show all products
        $products = $wpdb->get_results("SELECT * FROM " . CPM_Database::get_table('products') . " WHERE status = 'active' ORDER BY id DESC");
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/products-list.php';
        return ob_get_clean();
    }
    
    /**
     * Single product shortcode
     */
    public function product_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts);
        
        if (!$atts['id']) {
            return __('Product ID is required.', 'custom-product-manager');
        }
        
        return $this->display_product($atts['id']);
    }
    
    /**
     * Display single product
     */
    private function display_product($product_id) {
        global $wpdb;
        
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . CPM_Database::get_table('products') . " WHERE id = %d AND status = 'active'",
            $product_id
        ));
        
        if (!$product) {
            return __('Product not found.', 'custom-product-manager');
        }
        
        // Get categories
        $categories = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . CPM_Database::get_table('categories') . " 
            WHERE product_id = %d AND status = 'active' 
            ORDER BY display_order ASC, id ASC",
            $product_id
        ));
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/product-single.php';
        return ob_get_clean();
    }
    
    /**
     * Add product dropdown to header automatically
     */
    public function add_product_dropdown_to_header() {
        // Add to wp_nav_menu using filter (for menu integration)
        add_filter('wp_nav_menu_items', array($this, 'add_dropdown_to_menu'), 10, 2);
        
        // Add before header closes (most themes)
        add_action('wp_head', array($this, 'render_product_dropdown_in_header'), 99);
        
        // Fallback: Add at body start
        add_action('wp_body_open', array($this, 'render_product_dropdown_fallback'), 5);
    }
    
    /**
     * Add dropdown to navigation menu
     */
    public function add_dropdown_to_menu($items, $args) {
        // Add to any menu, or you can specify theme_location
        global $wpdb;
        $products = $wpdb->get_results("SELECT * FROM " . CPM_Database::get_table('products') . " WHERE status = 'active' ORDER BY name ASC");
        
        if (empty($products)) {
            return $items;
        }
        
        ob_start();
        ?>
        <li class="menu-item cpm-product-dropdown-menu-item">
            <div class="cpm-product-dropdown-wrapper">
                <select id="cpm-product-select-menu" class="cpm-product-select">
                    <option value=""><?php _e('Select Product', 'custom-product-manager'); ?></option>
                    <?php foreach ($products as $product) : ?>
                        <option value="<?php echo esc_attr($product->id); ?>"><?php echo esc_html($product->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="cpm-main-categories-container-menu" class="cpm-main-categories-container" style="display: none;"></div>
            </div>
        </li>
        <?php
        $dropdown = ob_get_clean();
        
        return $items . $dropdown;
    }
    
    /**
     * Render product dropdown in header (in head section)
     */
    public function render_product_dropdown_in_header() {
        global $wpdb;
        
        $products = $wpdb->get_results("SELECT * FROM " . CPM_Database::get_table('products') . " WHERE status = 'active' ORDER BY name ASC");
        
        if (empty($products)) {
            return;
        }
        
        // Only show once per page load
        static $shown = false;
        if ($shown) {
            return;
        }
        $shown = true;
        
        ?>
        <div class="cpm-product-dropdown-header" style="position: fixed; top: 0; left: 0; right: 0; background: #fff; padding: 10px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 9999; display: flex; align-items: center; justify-content: space-between;">
            <div class="cpm-product-dropdown-wrapper">
                <select id="cpm-product-select" class="cpm-product-select">
                    <option value=""><?php _e('Select Product', 'custom-product-manager'); ?></option>
                    <?php foreach ($products as $product) : ?>
                        <option value="<?php echo esc_attr($product->id); ?>"><?php echo esc_html($product->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="cpm-main-categories-container" class="cpm-main-categories-container" style="display: none;"></div>
            </div>
            
            <div class="cpm-header-auth">
                <?php if (is_user_logged_in()) : ?>
                    <div class="cpm-user-menu">
                        <span class="cpm-user-greeting"><?php _e('Hello,', 'custom-product-manager'); ?> <?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="cpm-logout-btn"><?php _e('Logout', 'custom-product-manager'); ?></a>
                    </div>
                <?php else : ?>
                    <div class="cpm-auth-buttons">
                        <a href="<?php echo home_url('/login/'); ?>" class="cpm-login-btn"><?php _e('Login', 'custom-product-manager'); ?></a>
                        <a href="<?php echo home_url('/register/'); ?>" class="cpm-register-btn"><?php _e('Sign Up', 'custom-product-manager'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <style>
        body { padding-top: 60px !important; }
        </style>
        <?php
    }
    
    /**
     * Render product dropdown fallback (if header method doesn't work)
     */
    public function render_product_dropdown_fallback() {
        // Only render if header method didn't already render
        if (did_action('wp_head')) {
            return;
        }
        
        global $wpdb;
        $products = $wpdb->get_results("SELECT * FROM " . CPM_Database::get_table('products') . " WHERE status = 'active' ORDER BY name ASC");
        
        if (empty($products)) {
            return;
        }
        
        ?>
        <div class="cpm-product-dropdown-header" style="position: fixed; top: 0; left: 0; right: 0; background: #fff; padding: 10px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 9999; display: flex; align-items: center; justify-content: space-between;">
            <div class="cpm-product-dropdown-wrapper">
                <select id="cpm-product-select" class="cpm-product-select">
                    <option value=""><?php _e('Select Product', 'custom-product-manager'); ?></option>
                    <?php foreach ($products as $product) : ?>
                        <option value="<?php echo esc_attr($product->id); ?>"><?php echo esc_html($product->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="cpm-main-categories-container" class="cpm-main-categories-container" style="display: none;"></div>
            </div>
            
            <div class="cpm-header-auth">
                <?php if (is_user_logged_in()) : ?>
                    <div class="cpm-user-menu">
                        <span class="cpm-user-greeting"><?php _e('Hello,', 'custom-product-manager'); ?> <?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="cpm-logout-btn"><?php _e('Logout', 'custom-product-manager'); ?></a>
                    </div>
                <?php else : ?>
                    <div class="cpm-auth-buttons">
                        <a href="<?php echo home_url('/login/'); ?>" class="cpm-login-btn"><?php _e('Login', 'custom-product-manager'); ?></a>
                        <a href="<?php echo home_url('/register/'); ?>" class="cpm-register-btn"><?php _e('Sign Up', 'custom-product-manager'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <style>
        body { padding-top: 60px !important; }
        </style>
        <?php
    }
    
    /**
     * Main category details page shortcode
     */
    public function main_category_details_shortcode($atts) {
        global $wpdb;
        
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        $main_category_id = isset($_GET['main_cat']) ? sanitize_text_field($_GET['main_cat']) : '';
        
        if (!$product_id || !$main_category_id) {
            return '<p>' . __('Invalid parameters.', 'custom-product-manager') . '</p>';
        }
        
        // Get product
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . CPM_Database::get_table('products') . " WHERE id = %d AND status = 'active'",
            $product_id
        ));
        
        if (!$product) {
            return '<p>' . __('Product not found.', 'custom-product-manager') . '</p>';
        }
        
        // Load product image URL if set in admin
        $product_image_url = get_option('_cpm_product_' . $product_id . '_image_url', '');
        
        // Get main categories data
        $option_name = '_cpm_product_' . $product_id . '_main_categories';
        $saved_data = get_option($option_name, '');
        $main_categories = array();
        
        if ($saved_data) {
            $main_categories = json_decode($saved_data, true);
        }
        
        if (!isset($main_categories[$main_category_id])) {
            return '<p>' . __('Main category not found.', 'custom-product-manager') . '</p>';
        }
        
        $main_category = $main_categories[$main_category_id];
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/main-category-details.php';
        return ob_get_clean();
    }
    
    /**
     * Track order shortcode
     */
    public function track_order_shortcode($atts) {
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/track-order.php';
        return ob_get_clean();
    }
    
    /**
     * Login shortcode
     */
    public function login_shortcode($atts) {
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/login.php';
        return ob_get_clean();
    }
    
    /**
     * Register shortcode
     */
    public function register_shortcode($atts) {
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/register.php';
        return ob_get_clean();
    }
    
    /**
     * Add rewrite rules for login and register pages
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^login/?$', 'index.php?cpm_page=login', 'top');
        add_rewrite_rule('^register/?$', 'index.php?cpm_page=register', 'top');
        add_rewrite_rule('^my-account/?$', 'index.php?cpm_page=my_account', 'top');
        
        // Use rewrite endpoints for subpages (WordPress recommended way)
        add_rewrite_endpoint('orders', EP_PAGES);
        add_rewrite_endpoint('edit-account', EP_PAGES);
        
        // Also add explicit rules as fallback
        add_rewrite_rule('^my-account/orders/?$', 'index.php?pagename=my-account&cpm_page=my_account&cpm_account_page=orders', 'top');
        add_rewrite_rule('^my-account/edit-account/?$', 'index.php?pagename=my-account&cpm_page=my_account&cpm_account_page=edit_account', 'top');
    }
    
    /**
     * Flush rewrite rules if needed (one-time check)
     */
    public function maybe_flush_rewrite_rules() {
        $rewrite_version = get_option('cpm_rewrite_rules_version', '0');
        if ($rewrite_version !== CPM_VERSION) {
            flush_rewrite_rules(false); // Soft flush
            update_option('cpm_rewrite_rules_version', CPM_VERSION);
        }
    }
    
    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'cpm_page';
        $vars[] = 'cpm_account_page';
        return $vars;
    }
    
    /**
     * Process login and register forms early (before WordPress redirects)
     */
    public function process_login_register_forms() {
        // Only process on login/register pages
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $parsed_uri = parse_url($request_uri);
        $path = isset($parsed_uri['path']) ? $parsed_uri['path'] : '';
        $path = trim($path, '/');
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH), '/');
        if ($site_path && strpos($path, $site_path) === 0) {
            $path = substr($path, strlen($site_path));
            $path = trim($path, '/');
        }
        
        // Process login form
        if (($path === 'login' || strpos($request_uri, '/login') !== false) && isset($_POST['cpm_login']) && isset($_POST['cpm_login_nonce']) && wp_verify_nonce($_POST['cpm_login_nonce'], 'cpm_login')) {
            $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $remember = isset($_POST['remember']) ? true : false;
            
            if (!empty($username) && !empty($password)) {
                $creds = array(
                    'user_login' => $username,
                    'user_password' => $password,
                    'remember' => $remember
                );
                
                $user = wp_signon($creds, false);
                
                if (!is_wp_error($user)) {
                    $redirect_url = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : home_url('/my-account/');
                    wp_safe_redirect($redirect_url);
                    exit;
                }
            }
        }
        
        // Process register form
        if (($path === 'register' || strpos($request_uri, '/register') !== false) && isset($_POST['cpm_register']) && isset($_POST['cpm_register_nonce']) && wp_verify_nonce($_POST['cpm_register_nonce'], 'cpm_register')) {
            $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
            $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
            
            if (!empty($username) && !empty($email) && !empty($password) && $password === $confirm_password && strlen($password) >= 6 && !username_exists($username) && !email_exists($email)) {
                $user_id = wp_create_user($username, $password, $email);
                
                if (!is_wp_error($user_id)) {
                    if ($first_name) {
                        update_user_meta($user_id, 'first_name', $first_name);
                    }
                    if ($last_name) {
                        update_user_meta($user_id, 'last_name', $last_name);
                    }
                    
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    
                    wp_redirect(home_url('/my-account/'));
                    exit;
                }
            }
        }
    }
    
    /**
     * Handle login and register pages (like WooCommerce)
     */
    public function handle_login_register_pages() {
        // Don't interfere with product detail pages or track order pages
        if (isset($_GET['cpm_main_category']) || isset($_GET['cpm_track_order'])) {
            return;
        }
        
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $parsed_uri = parse_url($request_uri);
        $path = isset($parsed_uri['path']) ? $parsed_uri['path'] : '';
        
        // Remove leading slash and get base path
        $path = trim($path, '/');
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH), '/');
        if ($site_path && strpos($path, $site_path) === 0) {
            $path = substr($path, strlen($site_path));
            $path = trim($path, '/');
        }
        
        // Check for login page
        if ($path === 'login' || strpos($request_uri, '/login') !== false || isset($_GET['cpm_login'])) {
            // Redirect if already logged in
            if (is_user_logged_in()) {
                wp_redirect(home_url('/my-account/'));
                exit;
            }
            add_filter('the_content', array($this, 'display_login_content'), 999);
            add_action('wp_head', array($this, 'hide_page_title'));
            // Also try to create/use a page if needed
            $this->ensure_login_page();
            return;
        }
        
        // Check for register page
        if ($path === 'register' || strpos($request_uri, '/register') !== false || isset($_GET['cpm_register'])) {
            // Redirect if already logged in
            if (is_user_logged_in()) {
                wp_redirect(home_url('/my-account/'));
                exit;
            }
            add_filter('the_content', array($this, 'display_register_content'), 999);
            add_action('wp_head', array($this, 'hide_page_title'));
            // Also try to create/use a page if needed
            $this->ensure_register_page();
            return;
        }
        
        // Check for my-account page and subpages
        // More robust path detection for subpages
        $account_page = '';
        $full_path = trim($request_uri, '/');
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH), '/');
        if ($site_path && strpos($full_path, $site_path) === 0) {
            $full_path = substr($full_path, strlen($site_path));
            $full_path = trim($full_path, '/');
        }
        
        // Normalize path for comparison
        $normalized_path = strtolower($full_path);
        $normalized_request_uri = strtolower($request_uri);
        
        // Check for orders subpage - multiple ways to detect
        if (strpos($normalized_path, 'my-account/orders') !== false || 
            strpos($normalized_request_uri, '/my-account/orders') !== false || 
            preg_match('/my-account\/orders/i', $full_path) ||
            preg_match('/my-account\/orders/i', $path) ||
            preg_match('/my-account\/orders/i', $request_uri)) {
            $account_page = 'orders';
        } 
        // Check for edit-account subpage
        elseif (strpos($normalized_path, 'my-account/edit-account') !== false || 
                strpos($normalized_request_uri, '/my-account/edit-account') !== false || 
                preg_match('/my-account\/edit-account/i', $full_path) ||
                preg_match('/my-account\/edit-account/i', $path) ||
                preg_match('/my-account\/edit-account/i', $request_uri)) {
            $account_page = 'edit_account';
        }
        
        // Check query var if path didn't match
        if (empty($account_page)) {
            $account_page = isset($_GET['cpm_account_page']) ? sanitize_text_field($_GET['cpm_account_page']) : '';
        }
        
        // Check if it's my-account page (main or subpage)
        $is_my_account = (
            $path === 'my-account' || 
            strpos($request_uri, '/my-account') !== false || 
            strpos($full_path, 'my-account') !== false ||
            strpos($normalized_path, 'my-account') !== false ||
            isset($_GET['cpm_my_account']) || 
            (isset($_GET['cpm_page']) && $_GET['cpm_page'] === 'my_account') || 
            !empty($account_page)
        );
        
        if ($is_my_account) {
            if (!is_user_logged_in()) {
                wp_redirect(home_url('/login/'));
                exit;
            }
            
            if ($account_page === 'orders') {
                add_filter('the_content', array($this, 'display_my_account_orders'), 999);
            } elseif ($account_page === 'edit_account') {
                add_filter('the_content', array($this, 'display_my_account_edit'), 999);
            } else {
                add_filter('the_content', array($this, 'display_my_account_content'), 999);
            }
            
            add_action('wp_head', array($this, 'hide_page_title'));
            // Also try to create/use a page if needed
            $this->ensure_my_account_page();
            return;
        }
    }
    
    /**
     * Ensure login page exists
     */
    private function ensure_login_page() {
        $page = get_page_by_path('login');
        if (!$page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Login',
                'post_content' => '<!-- Login Page - Content handled by plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'login'
            ));
        }
    }
    
    /**
     * Ensure register page exists
     */
    private function ensure_register_page() {
        $page = get_page_by_path('register');
        if (!$page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'Register',
                'post_content' => '<!-- Register Page - Content handled by plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'register'
            ));
        }
    }
    
    /**
     * Ensure my-account page exists
     */
    private function ensure_my_account_page() {
        $page = get_page_by_path('my-account');
        if (!$page) {
            $page_id = wp_insert_post(array(
                'post_title' => 'My Account',
                'post_content' => '<!-- My Account Page - Content handled by plugin -->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'my-account'
            ));
        }
    }
    
    /**
     * Hide page title
     */
    public function hide_page_title() {
        echo '<style>.entry-title, .page-title, h1.entry-title, h1.page-title { display: none !important; }</style>';
    }
    
    /**
     * Display login content
     */
    public function display_login_content($content) {
        // Always return login form if we're on login page
        return $this->get_login_form();
    }
    
    /**
     * Display register content
     */
    public function display_register_content($content) {
        // Always return register form if we're on register page
        return $this->get_register_form();
    }
    
    /**
     * Display my account content
     */
    public function display_my_account_content($content) {
        // Always return my account content if we're on my-account page
        return $this->get_my_account_content();
    }
    
    /**
     * Get login form HTML
     */
    private function get_login_form() {
        $error_message = '';
        $success_message = '';
        
        // Handle login form submission - process early to prevent WordPress redirect
        if (isset($_POST['cpm_login']) && isset($_POST['cpm_login_nonce']) && wp_verify_nonce($_POST['cpm_login_nonce'], 'cpm_login')) {
            $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $remember = isset($_POST['remember']) ? true : false;
            
            if (empty($username) || empty($password)) {
                $error_message = __('Please enter both username and password.', 'custom-product-manager');
            } else {
                $creds = array(
                    'user_login' => $username,
                    'user_password' => $password,
                    'remember' => $remember
                );
                
                $user = wp_signon($creds, false);
                
                if (is_wp_error($user)) {
                    $error_message = $user->get_error_message();
                } else {
                    $redirect_url = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : home_url('/my-account/');
                    wp_safe_redirect($redirect_url);
                    exit;
                }
            }
        }
        
        // Pass error message to template
        $error_message = isset($error_message) ? $error_message : '';
        $success_message = isset($success_message) ? $success_message : '';
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/login.php';
        return ob_get_clean();
    }
    
    /**
     * Get register form HTML
     */
    private function get_register_form() {
        $error_message = '';
        $success_message = '';
        
        // Handle registration form submission
        if (isset($_POST['cpm_register']) && wp_verify_nonce($_POST['cpm_register_nonce'], 'cpm_register')) {
            $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
            $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
            
            // Validation
            if (empty($username) || empty($email) || empty($password)) {
                $error_message = __('Please fill in all required fields.', 'custom-product-manager');
            } elseif ($password !== $confirm_password) {
                $error_message = __('Passwords do not match.', 'custom-product-manager');
            } elseif (strlen($password) < 6) {
                $error_message = __('Password must be at least 6 characters long.', 'custom-product-manager');
            } elseif (username_exists($username)) {
                $error_message = __('Username already exists. Please choose another.', 'custom-product-manager');
            } elseif (email_exists($email)) {
                $error_message = __('Email already registered. Please use another email or login.', 'custom-product-manager');
            } else {
                // Create user
                $user_id = wp_create_user($username, $password, $email);
                
                if (is_wp_error($user_id)) {
                    $error_message = $user_id->get_error_message();
                } else {
                    // Update user meta
                    if ($first_name) {
                        update_user_meta($user_id, 'first_name', $first_name);
                    }
                    if ($last_name) {
                        update_user_meta($user_id, 'last_name', $last_name);
                    }
                    
                    // Auto login user
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    
                    wp_redirect(home_url('/my-account/'));
                    exit;
                }
            }
        }
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/register.php';
        return ob_get_clean();
    }
    
    /**
     * Get my account content HTML
     */
    private function get_my_account_content() {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please login to view your account.', 'custom-product-manager') . '</p>';
        }
        
        $current_user = wp_get_current_user();
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/my-account.php';
        return ob_get_clean();
    }
    
    /**
     * Display my account orders page
     */
    public function display_my_account_orders($content) {
        return $this->get_my_account_orders_content();
    }
    
    /**
     * Display my account edit page
     */
    public function display_my_account_edit($content) {
        return $this->get_my_account_edit_content();
    }
    
    /**
     * Get my account orders content HTML
     */
    private function get_my_account_orders_content() {
        global $wpdb;
        $current_user = wp_get_current_user();
        
        if (!$current_user || !$current_user->ID) {
            return '<p>' . __('Please login to view your orders.', 'custom-product-manager') . '</p>';
        }
        
        // Get user's orders - check both user_id and billing_email
        // Use parentheses to ensure proper OR logic
        $orders = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . CPM_Database::get_table('orders') . " 
            WHERE (user_id = %d OR billing_email = %s)
            ORDER BY order_date DESC",
            $current_user->ID,
            $current_user->user_email
        ));
        
        // Debug: Log query results (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CPM Orders Query - User ID: ' . $current_user->ID . ', Email: ' . $current_user->user_email);
            error_log('CPM Orders Found: ' . (is_array($orders) ? count($orders) : 'not an array'));
            if (is_array($orders) && count($orders) > 0) {
                error_log('CPM First Order: ' . print_r($orders[0], true));
            }
        }
        
        // Make sure variables are available in the included file
        ob_start();
        $orders = $orders; // Ensure variable is in scope
        $current_user = $current_user; // Ensure variable is in scope
        include CPM_PLUGIN_DIR . 'frontend/views/my-account-orders.php';
        return ob_get_clean();
    }
    
    /**
     * Get my account edit content HTML
     */
    private function get_my_account_edit_content() {
        $current_user = wp_get_current_user();
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/my-account-edit.php';
        return ob_get_clean();
    }
    
    /**
     * Add dropdown JavaScript
     */
    public function add_dropdown_script() {
        $nonce = wp_create_nonce('cpm_frontend_nonce');
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Handle product selection (for both header and menu dropdowns)
            function handleProductSelect($select, $container) {
                $select.on('change', function() {
                    var productId = $(this).val();
                    
                    if (!productId) {
                        $container.hide().html('');
                        return;
                    }
                    
                    // Show loading
                    $container.html('<p><?php echo esc_js(__('Loading...', 'custom-product-manager')); ?></p>').show();
                    
                    // Get main categories via AJAX
                    $.ajax({
                        url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                        type: 'POST',
                        data: {
                            action: 'cpm_get_main_categories',
                            product_id: productId,
                            nonce: '<?php echo esc_js($nonce); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.main_categories) {
                                var html = '<div class="cpm-main-categories-list">';
                                html += '<h4><?php echo esc_js(__('Main Categories:', 'custom-product-manager')); ?></h4>';
                                html += '<ul>';
                                
                                $.each(response.data.main_categories, function(mainCatId, mainCat) {
                                    var currentUrl = window.location.href.split('?')[0];
                                    var separator = currentUrl.indexOf('?') > -1 ? '&' : '?';
                                    html += '<li><a href="' + currentUrl + separator + 'cpm_main_category=1&product_id=' + productId + '&main_cat=' + encodeURIComponent(mainCatId) + '" class="cpm-main-category-link">' + mainCat.name + '</a></li>';
                                });
                                
                                html += '</ul></div>';
                                $container.html(html);
                            } else {
                                $container.html('<p><?php echo esc_js(__('No main categories found.', 'custom-product-manager')); ?></p>');
                            }
                        },
                        error: function() {
                            $container.html('<p><?php echo esc_js(__('Error loading categories.', 'custom-product-manager')); ?></p>');
                        }
                    });
                });
            }
            
            // Initialize for header dropdown
            if ($('#cpm-product-select').length) {
                handleProductSelect($('#cpm-product-select'), $('#cpm-main-categories-container'));
            }
            
            // Initialize for menu dropdown
            if ($('#cpm-product-select-menu').length) {
                handleProductSelect($('#cpm-product-select-menu'), $('#cpm-main-categories-container-menu'));
            }
        });
        </script>
        <?php
    }
    
    /**
     * Statistics shortcode - displays business statistics
     */
    public function statistics_shortcode($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'layout' => 'grid', // grid or list
            'columns' => '4'
        ), $atts);
        
        // Get Countries Served - count unique countries from all products' main categories
        $countries = array();
        $products = $wpdb->get_results("SELECT id FROM " . CPM_Database::get_table('products') . " WHERE status = 'active'");
        
        foreach ($products as $product) {
            $option_name = '_cpm_product_' . $product->id . '_main_categories';
            $saved_data = get_option($option_name, '');
            
            if ($saved_data) {
                $main_categories = json_decode($saved_data, true);
                if (is_array($main_categories)) {
                    foreach ($main_categories as $main_cat) {
                        if (isset($main_cat['categories']) && is_array($main_cat['categories'])) {
                            foreach ($main_cat['categories'] as $cat) {
                                if (isset($cat['countries']) && is_array($cat['countries'])) {
                                    foreach ($cat['countries'] as $country) {
                                        if (isset($country['name']) && !empty($country['name'])) {
                                            $countries[$country['name']] = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $countries_count = count($countries);
        
        // Get Orders Delivered - count completed orders
        $orders_delivered = $wpdb->get_var(
            "SELECT COUNT(*) FROM " . CPM_Database::get_table('orders') . " WHERE order_status = 'completed'"
        );
        
        // Get Happy Customers - count unique customers who have approved reviews with rating > 4
        $happy_customers = $wpdb->get_var(
            "SELECT COUNT(DISTINCT reviewer_email) FROM " . CPM_Database::get_table('reviews') . 
            " WHERE status = 'approved' AND rating >= 4 AND reviewer_email IS NOT NULL AND reviewer_email != ''"
        );
        
        // Get In Business - years since first order or plugin installation
        $first_order_date = $wpdb->get_var(
            "SELECT MIN(order_date) FROM " . CPM_Database::get_table('orders') . " WHERE order_date IS NOT NULL"
        );
        
        $in_business_years = 0;
        if ($first_order_date) {
            $first_date = new DateTime($first_order_date);
            $now = new DateTime();
            $diff = $now->diff($first_date);
            $in_business_years = $diff->y;
        } else {
            // Fallback to plugin installation date
            $plugin_install_date = get_option('cpm_install_date', '');
            if ($plugin_install_date) {
                $first_date = new DateTime($plugin_install_date);
                $now = new DateTime();
                $diff = $now->diff($first_date);
                $in_business_years = $diff->y;
            } else {
                // Set install date if not exists
                $in_business_years = 0;
                update_option('cpm_install_date', current_time('mysql'));
            }
        }
        
        // If no years, show at least 1
        if ($in_business_years == 0) {
            $in_business_years = 1;
        }
        
        ob_start();
        include CPM_PLUGIN_DIR . 'frontend/views/statistics.php';
        return ob_get_clean();
    }
    
    /**
     * Automatically display statistics on home page
     */
    public function auto_display_statistics($content) {
        // Only show on front page/home page
        if (!is_front_page() && !is_home()) {
            return $content;
        }
        
        // Don't add if already in content (to avoid duplicates)
        if (strpos($content, 'cpm-statistics-wrapper') !== false) {
            return $content;
        }
        
        // Don't add if shortcode is already present
        if (has_shortcode($content, 'cpm_statistics')) {
            return $content;
        }
        
        // Get statistics HTML
        $statistics_html = $this->statistics_shortcode(array('layout' => 'grid', 'columns' => '4'));
        
        // Add statistics after content with some spacing
        $content .= '<div style="margin: 40px 0;">' . $statistics_html . '</div>';
        
        return $content;
    }
}


