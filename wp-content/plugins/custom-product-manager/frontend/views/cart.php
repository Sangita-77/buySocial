<?php
if (!defined('ABSPATH')) {
    exit;
}

$cart_items = cpm_get_cart_items();
$cart_total = cpm_get_cart_total();
$cart_page_id = get_option('cpm_cart_page_id');
$checkout_page_id = get_option('cpm_checkout_page_id');
?>

<div class="cpm-cart">
    <h1><?php _e('Shopping Cart', 'custom-product-manager'); ?></h1>
    
    <?php if (!empty($cart_items)) : ?>
        <table class="cpm-cart-table">
            <thead>
                <tr>
                    <th><?php _e('Product', 'custom-product-manager'); ?></th>
                    <th><?php _e('Main Category', 'custom-product-manager'); ?></th>
                    <th><?php _e('Category', 'custom-product-manager'); ?></th>
                    <th><?php _e('Country', 'custom-product-manager'); ?></th>
                    <th><?php _e('Post URL', 'custom-product-manager'); ?></th>
                    <th><?php _e('Price', 'custom-product-manager'); ?></th>
                    <th><?php _e('Subtotal', 'custom-product-manager'); ?></th>
                    <th><?php _e('Actions', 'custom-product-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item) : ?>
                    <?php
                    global $wpdb;
                    
                    // Get variation data (new structure with main categories)
                    // variation_data is stored as JSON in the cart table
                    $variation_data = !empty($item->variation_data) ? json_decode($item->variation_data, true) : null;
                    
                    $product_name = '';
                    $main_category_name = '';
                    $category_name = '';
                    $country_name = '';
                    $page_url = '';
                    
                    // Debug: Check if variation_data exists
                    if (empty($item->variation_data)) {
                        error_log('CPM Cart: Item ' . $item->id . ' has no variation_data');
                    }
                    
                    if ($variation_data && is_array($variation_data) && isset($variation_data['product_id'])) {
                        // Get page URL from variation data
                        $page_url = isset($variation_data['page_url']) && !empty($variation_data['page_url']) ? esc_url($variation_data['page_url']) : '';
                        
                        // Debug: Log if page_url is missing
                        if (empty($page_url) && defined('WP_DEBUG') && WP_DEBUG) {
                            error_log('CPM Cart: Item ' . $item->id . ' has no page_url in variation_data. Data: ' . print_r($variation_data, true));
                        }
                        
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
                        } else {
                            error_log('CPM Cart: Product not found for ID: ' . (isset($variation_data['product_id']) ? $variation_data['product_id'] : 'N/A'));
                        }
                    } else {
                        error_log('CPM Cart: Invalid variation_data for item ' . $item->id . ': ' . print_r($variation_data, true));
                    }
                    ?>
                    <tr data-item-id="<?php echo esc_attr($item->id); ?>">
                        <td>
                            <strong><?php echo esc_html($product_name ? $product_name : __('Product #' . $item->id, 'custom-product-manager')); ?></strong>
                            <?php if (empty($product_name)) : ?>
                                <small style="display: block; color: #999; font-size: 11px;">
                                    Debug: variation_data = <?php echo esc_html(substr($item->variation_data, 0, 100)); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($main_category_name ? $main_category_name : '-'); ?></td>
                        <td><?php echo esc_html($category_name ? $category_name : '-'); ?></td>
                        <td><?php echo esc_html($country_name ? $country_name : '-'); ?></td>
                        <td>
                            <?php if ($page_url) : ?>
                                <a href="<?php echo esc_url($page_url); ?>" target="_blank" rel="noopener noreferrer" class="cpm-post-url-link" title="<?php echo esc_attr($page_url); ?>">
                                    <?php 
                                    // Truncate long URLs for display
                                    $display_url = strlen($page_url) > 50 ? substr($page_url, 0, 50) . '...' : $page_url;
                                    echo esc_html($display_url); 
                                    ?>
                                </a>
                            <?php else : ?>
                                <span class="cpm-no-url" title="<?php echo esc_attr(__('No URL provided', 'custom-product-manager')); ?>">-</span>
                                <?php if (defined('WP_DEBUG') && WP_DEBUG) : ?>
                                    <small style="display: block; color: #999; font-size: 11px;">
                                        Debug: variation_data keys = <?php echo esc_html(isset($variation_data) && is_array($variation_data) ? implode(', ', array_keys($variation_data)) : 'N/A'); ?>
                                    </small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo cpm_format_price($item->price); ?></td>
                        <td class="item-subtotal"><?php echo cpm_format_price($item->price * $item->quantity); ?></td>
                        <td>
                            <button type="button" class="cpm-remove-item" data-item-id="<?php echo esc_attr($item->id); ?>">
                                <?php _e('Remove', 'custom-product-manager'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="total-label"><?php _e('Total:', 'custom-product-manager'); ?></td>
                    <td class="cart-total"><?php echo cpm_format_price($cart_total); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="cpm-cart-actions">
            <a href="<?php echo get_permalink($cart_page_id); ?>" class="button"><?php _e('Continue Shopping', 'custom-product-manager'); ?></a>
            <?php if ($checkout_page_id) : ?>
                <a href="<?php echo get_permalink($checkout_page_id); ?>" class="button button-primary"><?php _e('Proceed to Checkout', 'custom-product-manager'); ?></a>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <p><?php _e('Your cart is empty.', 'custom-product-manager'); ?></p>
        <?php if ($cart_page_id) : ?>
            <a href="<?php echo get_permalink($cart_page_id); ?>" class="button"><?php _e('Continue Shopping', 'custom-product-manager'); ?></a>
        <?php endif; ?>
    <?php endif; ?>
</div>


