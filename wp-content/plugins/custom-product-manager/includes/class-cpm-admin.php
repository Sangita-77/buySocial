<?php
/**
 * Admin interface class
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPM_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Custom Products', 'custom-product-manager'),
            __('Custom Products', 'custom-product-manager'),
            'manage_options',
            'cpm-products',
            array($this, 'products_page'),
            'dashicons-products',
            30
        );
        
        add_submenu_page(
            'cpm-products',
            __('All Products', 'custom-product-manager'),
            __('All Products', 'custom-product-manager'),
            'manage_options',
            'cpm-products',
            array($this, 'products_page')
        );
        
        add_submenu_page(
            'cpm-products',
            __('Add Product', 'custom-product-manager'),
            __('Add Product', 'custom-product-manager'),
            'manage_options',
            'cpm-add-product',
            array($this, 'add_product_page')
        );
        
        add_submenu_page(
            'cpm-products',
            __('Orders', 'custom-product-manager'),
            __('Orders', 'custom-product-manager'),
            'manage_options',
            'cpm-orders',
            array($this, 'orders_page')
        );
        
        add_submenu_page(
            'cpm-products',
            __('Reviews', 'custom-product-manager'),
            __('Reviews', 'custom-product-manager'),
            'manage_options',
            'cpm-reviews',
            array($this, 'reviews_page')
        );
        
        add_submenu_page(
            null, // Hidden page
            __('Manage Categories', 'custom-product-manager'),
            __('Manage Categories', 'custom-product-manager'),
            'manage_options',
            'cpm-manage-categories',
            array($this, 'manage_categories_page')
        );
    }
    
    /**
     * Products listing page
     */
    public function products_page() {
        global $wpdb;
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        
        if ($action === 'delete' && $product_id && check_admin_referer('delete_product_' . $product_id)) {
            $wpdb->delete(
                CPM_Database::get_table('products'),
                array('id' => $product_id),
                array('%d')
            );
            echo '<div class="notice notice-success"><p>' . __('Product deleted successfully.', 'custom-product-manager') . '</p></div>';
        }
        
        $products = $wpdb->get_results("SELECT * FROM " . CPM_Database::get_table('products') . " ORDER BY id DESC");
        
        include CPM_PLUGIN_DIR . 'admin/views/products-list.php';
    }
    
    /**
     * Add/Edit product page
     */
    public function add_product_page() {
        global $wpdb;
        
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        $product = null;
        
        if ($product_id) {
            $product = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM " . CPM_Database::get_table('products') . " WHERE id = %d",
                $product_id
            ));
        }
        
        // Handle form submission
        if (isset($_POST['save_product']) && check_admin_referer('cpm_save_product')) {
            $name = sanitize_text_field($_POST['product_name']);
            $description = wp_kses_post($_POST['product_description']);
            $status = sanitize_text_field($_POST['product_status']);
            
            if ($product_id) {
                $wpdb->update(
                    CPM_Database::get_table('products'),
                    array(
                        'name' => $name,
                        'description' => $description,
                        'status' => $status
                    ),
                    array('id' => $product_id),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
                $message = __('Product updated successfully.', 'custom-product-manager');
            } else {
                $wpdb->insert(
                    CPM_Database::get_table('products'),
                    array(
                        'name' => $name,
                        'description' => $description,
                        'status' => $status
                    ),
                    array('%s', '%s', '%s')
                );
                $product_id = $wpdb->insert_id;
                $message = __('Product added successfully.', 'custom-product-manager');
            }
            
            // Save product image (stored as an option keyed by product ID)
            if ($product_id) {
                $product_image_url = isset($_POST['product_image']) ? esc_url_raw($_POST['product_image']) : '';
                $image_option_name = '_cpm_product_' . $product_id . '_image_url';
                if (!empty($product_image_url)) {
                    update_option($image_option_name, $product_image_url);
                } else {
                    // Clear image if field is empty
                    delete_option($image_option_name);
                }
            }
            
            // Save main categories data
            if (isset($_POST['main_categories']) && is_array($_POST['main_categories']) && !empty($_POST['main_categories'])) {
                // Debug: Log what we received
                error_log('CPM: Received ' . count($_POST['main_categories']) . ' main categories in POST');
                
                // Sanitize the data before saving
                $sanitized_main_categories = array();
                foreach ($_POST['main_categories'] as $main_cat_id => $main_cat_data) {
                    // Keep original ID but sanitize it - don't use sanitize_key as it might cause collisions
                    $clean_main_cat_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $main_cat_id);
                    
                    if (!isset($main_cat_data['name']) || empty(trim($main_cat_data['name']))) {
                        error_log('CPM: Skipping empty main category: ' . $main_cat_id);
                        continue; // Skip empty main categories
                    }
                    
                    error_log('CPM: Processing main category: ' . $main_cat_id . ' -> ' . $clean_main_cat_id . ' with name: ' . $main_cat_data['name']);
                    
                    $sanitized_main_categories[$clean_main_cat_id] = array(
                        'name' => sanitize_text_field($main_cat_data['name']),
                        'categories' => array()
                    );
                    
                    if (isset($main_cat_data['categories']) && is_array($main_cat_data['categories'])) {
                        foreach ($main_cat_data['categories'] as $cat_id => $cat_data) {
                            if (!isset($cat_data['name']) || empty(trim($cat_data['name']))) {
                                continue; // Skip empty categories
                            }
                            
                            $clean_cat_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $cat_id);
                            
                            $sanitized_main_categories[$clean_main_cat_id]['categories'][$clean_cat_id] = array(
                                'name' => sanitize_text_field($cat_data['name']),
                                'countries' => array()
                            );
                            
                            if (isset($cat_data['countries']) && is_array($cat_data['countries'])) {
                                foreach ($cat_data['countries'] as $country_id => $country_data) {
                                    if (!isset($country_data['name']) || empty(trim($country_data['name']))) {
                                        continue; // Skip empty countries
                                    }
                                    
                                    $clean_country_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $country_id);
                                    
                                    $sanitized_main_categories[$clean_main_cat_id]['categories'][$clean_cat_id]['countries'][$clean_country_id] = array(
                                        'name' => sanitize_text_field($country_data['name']),
                                        'quantities' => array()
                                    );
                                    
                                    if (isset($country_data['quantities']) && is_array($country_data['quantities'])) {
                                        foreach ($country_data['quantities'] as $qty_id => $qty_data) {
                                            if (!isset($qty_data['quantity']) || empty(trim($qty_data['quantity']))) {
                                                continue; // Skip empty quantities
                                            }
                                            
                                            $clean_qty_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $qty_id);
                                            
                                            // Quantity is text field, so keep as text
                                            $sanitized_main_categories[$clean_main_cat_id]['categories'][$clean_cat_id]['countries'][$clean_country_id]['quantities'][$clean_qty_id] = array(
                                                'quantity' => sanitize_text_field($qty_data['quantity']),
                                                'prices' => array()
                                            );
                                            
                                            if (isset($qty_data['prices']) && is_array($qty_data['prices'])) {
                                                foreach ($qty_data['prices'] as $price_id => $price_data) {
                                                    if (!isset($price_data['price']) || empty(trim($price_data['price']))) {
                                                        continue; // Skip empty prices
                                                    }
                                                    
                                                    $clean_price_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $price_id);
                                                    
                                                    $sanitized_main_categories[$clean_main_cat_id]['categories'][$clean_cat_id]['countries'][$clean_country_id]['quantities'][$clean_qty_id]['prices'][$clean_price_id] = array(
                                                        'price' => floatval($price_data['price'])
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (!empty($sanitized_main_categories)) {
                    error_log('CPM: Saving ' . count($sanitized_main_categories) . ' main categories');
                    $main_categories_data = json_encode($sanitized_main_categories, JSON_UNESCAPED_UNICODE);
                    $option_name = '_cpm_product_' . $product_id . '_main_categories';
                    update_option($option_name, $main_categories_data);
                    
                    // Verify what was saved
                    $verify = get_option($option_name, '');
                    $verify_data = json_decode($verify, true);
                    error_log('CPM: Verified saved data contains ' . (is_array($verify_data) ? count($verify_data) : 0) . ' main categories');
                } else {
                    error_log('CPM: No main categories to save after sanitization');
                }
            } else {
                error_log('CPM: No main_categories in POST or empty array');
            }
            
            // Show how many main categories were saved
            if (isset($sanitized_main_categories) && !empty($sanitized_main_categories)) {
                $main_cat_count = count($sanitized_main_categories);
                $message .= ' ' . sprintf(__('(%d main category/categories saved)', 'custom-product-manager'), $main_cat_count);
            }
            
            echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
            $product = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM " . CPM_Database::get_table('products') . " WHERE id = %d",
                $product_id
            ));
        }
        
        // Load saved main categories data
        $saved_main_categories = array();
        if ($product_id) {
            $option_name = '_cpm_product_' . $product_id . '_main_categories';
            $saved_data = get_option($option_name, '');
            if ($saved_data) {
                $saved_main_categories = json_decode($saved_data, true);
                if (!is_array($saved_main_categories)) {
                    $saved_main_categories = array();
                }
            }
        }
        
        // Load saved product image URL (if any)
        $product_image_url = '';
        if ($product_id) {
            $product_image_url = get_option('_cpm_product_' . $product_id . '_image_url', '');
        }
        
        // Get categories for this product
        $categories = array();
        if ($product_id) {
            $categories = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM " . CPM_Database::get_table('categories') . " 
                WHERE product_id = %d ORDER BY display_order ASC, id ASC",
                $product_id
            ));
        }
        
        include CPM_PLUGIN_DIR . 'admin/views/product-form.php';
    }
    
    /**
     * Orders listing page
     */
    public function orders_page() {
        global $wpdb;
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        
        // Check for order status update first (can come from POST when viewing order)
        if (isset($_POST['order_status']) && isset($_POST['_wpnonce'])) {
            // Get order_id from POST if not in GET (form submission from order view)
            $update_order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : $order_id;
            
            if ($update_order_id && check_admin_referer('update_order_status_' . $update_order_id)) {
                $status = sanitize_text_field($_POST['order_status']);
                $result = $wpdb->update(
                    CPM_Database::get_table('orders'),
                    array('order_status' => $status),
                    array('id' => $update_order_id),
                    array('%s'),
                    array('%d')
                );
                
                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>' . __('Order status updated.', 'custom-product-manager') . '</p></div>';
                    // Update order_id to show the updated order
                    $order_id = $update_order_id;
                    $action = 'view';
                } else {
                    echo '<div class="notice notice-error"><p>' . __('Error updating order status.', 'custom-product-manager') . '</p></div>';
                }
            }
        }
        
        // Handle legacy update_status action from URL
        if ($action === 'update_status' && $order_id && check_admin_referer('update_order_status_' . $order_id)) {
            $status = sanitize_text_field($_POST['order_status']);
            $wpdb->update(
                CPM_Database::get_table('orders'),
                array('order_status' => $status),
                array('id' => $order_id),
                array('%s'),
                array('%d')
            );
            echo '<div class="notice notice-success"><p>' . __('Order status updated.', 'custom-product-manager') . '</p></div>';
            // Redirect to view the order after update
            $action = 'view';
        }
        
        if ($action === 'view' && $order_id) {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM " . CPM_Database::get_table('orders') . " WHERE id = %d",
                $order_id
            ));
            
            $order_items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM " . CPM_Database::get_table('order_items') . " WHERE order_id = %d",
                $order_id
            ));
            
            include CPM_PLUGIN_DIR . 'admin/views/order-view.php';
            return;
        }
        
        $orders = $wpdb->get_results("SELECT * FROM " . CPM_Database::get_table('orders') . " ORDER BY id DESC LIMIT 50");
        
        include CPM_PLUGIN_DIR . 'admin/views/orders-list.php';
    }
    
    /**
     * Reviews management page
     */
    public function reviews_page() {
        global $wpdb;
        
        // Handle review actions
        if (isset($_GET['action']) && isset($_GET['review_id']) && check_admin_referer('cpm_review_action')) {
            $review_id = intval($_GET['review_id']);
            $action = sanitize_text_field($_GET['action']);
            
            if ($action === 'approve') {
                $result = $wpdb->update(
                    CPM_Database::get_table('reviews'),
                    array('status' => 'approved'),
                    array('id' => $review_id),
                    array('%s'),
                    array('%d')
                );
                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>' . __('Review approved.', 'custom-product-manager') . '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>' . __('Error approving review.', 'custom-product-manager') . '</p></div>';
                }
            } elseif ($action === 'reject') {
                $result = $wpdb->update(
                    CPM_Database::get_table('reviews'),
                    array('status' => 'rejected'),
                    array('id' => $review_id),
                    array('%s'),
                    array('%d')
                );
                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>' . __('Review rejected.', 'custom-product-manager') . '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>' . __('Error rejecting review.', 'custom-product-manager') . '</p></div>';
                }
            } elseif ($action === 'delete') {
                $result = $wpdb->delete(
                    CPM_Database::get_table('reviews'),
                    array('id' => $review_id),
                    array('%d')
                );
                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>' . __('Review deleted.', 'custom-product-manager') . '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>' . __('Error deleting review.', 'custom-product-manager') . '</p></div>';
                }
            }
        }
        
        // Get filter
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        
        // Build query
        if ($status_filter !== 'all') {
            $reviews = $wpdb->get_results($wpdb->prepare("
                SELECT r.*, p.name as product_name
                FROM " . CPM_Database::get_table('reviews') . " r
                LEFT JOIN " . CPM_Database::get_table('products') . " p ON r.product_id = p.id
                WHERE r.status = %s
                ORDER BY r.created_at DESC
            ", $status_filter));
        } else {
            $reviews = $wpdb->get_results("
                SELECT r.*, p.name as product_name
                FROM " . CPM_Database::get_table('reviews') . " r
                LEFT JOIN " . CPM_Database::get_table('products') . " p ON r.product_id = p.id
                ORDER BY r.created_at DESC
            ");
        }
        
        // Get counts
        $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM " . CPM_Database::get_table('reviews') . " WHERE status = 'pending'");
        $approved_count = $wpdb->get_var("SELECT COUNT(*) FROM " . CPM_Database::get_table('reviews') . " WHERE status = 'approved'");
        $rejected_count = $wpdb->get_var("SELECT COUNT(*) FROM " . CPM_Database::get_table('reviews') . " WHERE status = 'rejected'");
        $all_count = $wpdb->get_var("SELECT COUNT(*) FROM " . CPM_Database::get_table('reviews'));
        
        // Debug: Log if no reviews found
        if (empty($reviews)) {
            $total_in_db = $wpdb->get_var("SELECT COUNT(*) FROM " . CPM_Database::get_table('reviews'));
            error_log('CPM Reviews: No reviews found. Total in DB: ' . $total_in_db . ', Filter: ' . $status_filter);
        }
        
        include CPM_PLUGIN_DIR . 'admin/views/reviews-list.php';
    }
    
    /**
     * Manage categories page
     */
    public function manage_categories_page() {
        include CPM_PLUGIN_DIR . 'admin/views/product-categories.php';
    }
}

