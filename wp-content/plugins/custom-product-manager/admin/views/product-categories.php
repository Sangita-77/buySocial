<?php
/**
 * Comprehensive category/subcategory/variation management page
 */
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if (!$product_id) {
    echo '<div class="error"><p>' . __('Invalid product ID.', 'custom-product-manager') . '</p></div>';
    return;
}

$product = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM " . CPM_Database::get_table('products') . " WHERE id = %d",
    $product_id
));

if (!$product) {
    echo '<div class="error"><p>' . __('Product not found.', 'custom-product-manager') . '</p></div>';
    return;
}

// Handle saving
if (isset($_POST['save_categories']) && check_admin_referer('cpm_save_categories_' . $product_id)) {
    // Save categories
    if (isset($_POST['categories']) && is_array($_POST['categories'])) {
        foreach ($_POST['categories'] as $cat_data) {
            $cat_id = isset($cat_data['id']) ? intval($cat_data['id']) : 0;
            $cat_name = isset($cat_data['name']) ? sanitize_text_field($cat_data['name']) : '';
            $cat_desc = isset($cat_data['description']) ? wp_kses_post($cat_data['description']) : '';
            
            if (!$cat_name) continue;
            
            if ($cat_id) {
                // Update existing category
                $wpdb->update(
                    CPM_Database::get_table('categories'),
                    array(
                        'name' => $cat_name,
                        'description' => $cat_desc
                    ),
                    array('id' => $cat_id),
                    array('%s', '%s'),
                    array('%d')
                );
            } else {
                // Insert new category
                $wpdb->insert(
                    CPM_Database::get_table('categories'),
                    array(
                        'product_id' => $product_id,
                        'name' => $cat_name,
                        'description' => $cat_desc,
                        'status' => 'active'
                    ),
                    array('%d', '%s', '%s', '%s')
                );
                $cat_id = $wpdb->insert_id;
            }
            
            // Save subcategories for this category
            if (isset($cat_data['subcategories']) && is_array($cat_data['subcategories'])) {
                foreach ($cat_data['subcategories'] as $subcat_data) {
                    $subcat_id = isset($subcat_data['id']) ? intval($subcat_data['id']) : 0;
                    $subcat_name = isset($subcat_data['name']) ? sanitize_text_field($subcat_data['name']) : '';
                    $subcat_type = isset($subcat_data['type']) ? sanitize_text_field($subcat_data['type']) : 'select';
                    $subcat_options = isset($subcat_data['options']) ? array_map('sanitize_text_field', $subcat_data['options']) : array();
                    
                    if (!$subcat_name) continue;
                    
                    if ($subcat_id) {
                        $wpdb->update(
                            CPM_Database::get_table('subcategories'),
                            array(
                                'name' => $subcat_name,
                                'field_type' => $subcat_type
                            ),
                            array('id' => $subcat_id),
                            array('%s', '%s'),
                            array('%d')
                        );
                        
                        // Delete old options
                        $wpdb->delete(
                            CPM_Database::get_table('subcategory_options'),
                            array('subcategory_id' => $subcat_id),
                            array('%d')
                        );
                    } else {
                        $wpdb->insert(
                            CPM_Database::get_table('subcategories'),
                            array(
                                'category_id' => $cat_id,
                                'name' => $subcat_name,
                                'field_type' => $subcat_type,
                                'status' => 'active'
                            ),
                            array('%d', '%s', '%s', '%s')
                        );
                        $subcat_id = $wpdb->insert_id;
                    }
                    
                    // Insert options
                    if ($subcat_type === 'select' && !empty($subcat_options)) {
                        foreach ($subcat_options as $index => $option) {
                            if (trim($option)) {
                                $wpdb->insert(
                                    CPM_Database::get_table('subcategory_options'),
                                    array(
                                        'subcategory_id' => $subcat_id,
                                        'option_value' => trim($option),
                                        'display_order' => $index
                                    ),
                                    array('%d', '%s', '%d')
                                );
                            }
                        }
                    }
                }
            }
            
            // Save variations for this category
            if (isset($cat_data['variations']) && is_array($cat_data['variations'])) {
                foreach ($cat_data['variations'] as $var_data) {
                    $var_id = isset($var_data['id']) ? intval($var_data['id']) : 0;
                    $var_price = isset($var_data['price']) ? floatval($var_data['price']) : 0;
                    $var_data_json = isset($var_data['data']) ? json_encode($var_data['data']) : '{}';
                    
                    if ($var_price <= 0) continue;
                    
                    if ($var_id) {
                        $wpdb->update(
                            CPM_Database::get_table('variations'),
                            array(
                                'variation_data' => $var_data_json,
                                'price' => $var_price
                            ),
                            array('id' => $var_id),
                            array('%s', '%f'),
                            array('%d')
                        );
                    } else {
                        $wpdb->insert(
                            CPM_Database::get_table('variations'),
                            array(
                                'category_id' => $cat_id,
                                'variation_data' => $var_data_json,
                                'price' => $var_price,
                                'status' => 'active'
                            ),
                            array('%d', '%s', '%f', '%s')
                        );
                    }
                }
            }
        }
    }
    
    echo '<div class="notice notice-success"><p>' . __('Categories saved successfully.', 'custom-product-manager') . '</p></div>';
}

// Get all categories with subcategories and variations
$categories = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM " . CPM_Database::get_table('categories') . " 
    WHERE product_id = %d ORDER BY display_order ASC, id ASC",
    $product_id
));
?>

<div class="wrap">
    <h1><?php echo sprintf(__('Manage Categories for: %s', 'custom-product-manager'), esc_html($product->name)); ?></h1>
    <p><a href="<?php echo admin_url('admin.php?page=cpm-add-product&product_id=' . $product_id); ?>"><?php _e('← Back to Product', 'custom-product-manager'); ?></a></p>
    
    <div id="cpm-categories-manager">
        <form method="post" id="cpm-categories-form">
            <?php wp_nonce_field('cpm_save_categories_' . $product_id, 'cpm_save_categories'); ?>
            
            <div id="categories-list">
                <?php if ($categories && !empty($categories)) : ?>
                    <?php foreach ($categories as $category) : ?>
                        <?php
                        $subcategories = $wpdb->get_results($wpdb->prepare(
                            "SELECT sc.*,
                            (SELECT GROUP_CONCAT(option_value SEPARATOR '|||') FROM " . CPM_Database::get_table('subcategory_options') . " WHERE subcategory_id = sc.id ORDER BY display_order) as options
                            FROM " . CPM_Database::get_table('subcategories') . " sc
                            WHERE sc.category_id = %d ORDER BY sc.display_order ASC",
                            $category->id
                        ));
                        
                        $variations = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM " . CPM_Database::get_table('variations') . " 
                            WHERE category_id = %d ORDER BY id ASC",
                            $category->id
                        ));
                        ?>
                        <div class="category-wrapper" data-category-id="<?php echo esc_attr($category->id); ?>">
                            <?php include CPM_PLUGIN_DIR . 'admin/views/category-full-form.php'; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="description"><?php _e('No categories yet. Click "Add Category" to create your first category.', 'custom-product-manager'); ?></p>
                <?php endif; ?>
            </div>
            
            <p>
                <button type="button" class="button" id="add-category-full"><?php _e('Add Category', 'custom-product-manager'); ?></button>
                <button type="submit" name="save_categories" class="button button-primary"><?php _e('Save All Categories', 'custom-product-manager'); ?></button>
            </p>
        </form>
    </div>
</div>

<!-- Hidden template for new category -->
<div id="category-template" style="display: none;">
    <div class="category-wrapper" data-category-id="TEMPLATE_ID">
        <div class="category-full-form">
            <div class="category-header-section">
                <h3>
                    <input type="text" name="categories[TEMPLATE_ID][name]" 
                           placeholder="<?php _e('Category Name (e.g., Buy Facebook Likes)', 'custom-product-manager'); ?>" 
                           class="regular-text" required />
                    <input type="hidden" name="categories[TEMPLATE_ID][id]" value="0" />
                </h3>
                <button type="button" class="button delete-category-full"><?php _e('Delete Category', 'custom-product-manager'); ?></button>
            </div>
            
            <div class="category-description-section">
                <textarea name="categories[TEMPLATE_ID][description]" 
                          rows="2" placeholder="<?php _e('Category Description (optional)', 'custom-product-manager'); ?>" 
                          class="large-text"></textarea>
            </div>
            
            <div class="subcategories-section">
                <h4><?php _e('Sub-Categories', 'custom-product-manager'); ?></h4>
                <div class="subcategories-list"></div>
                <button type="button" class="button add-subcat-full"><?php _e('Add Sub-Category', 'custom-product-manager'); ?></button>
            </div>
            
            <div class="variations-section">
                <h4><?php _e('Variations & Prices', 'custom-product-manager'); ?></h4>
                <div class="variations-list"></div>
                <button type="button" class="button add-variation-full"><?php _e('Add Variation/Price', 'custom-product-manager'); ?></button>
            </div>
        </div>
    </div>
</div>

<style>
.category-wrapper {
    border: 2px solid #0073aa;
    margin: 20px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 5px;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log('Category management page loaded');
    
    // Fallback: If admin.js didn't load, at least handle the basic add category
    if (typeof categoryCounter === 'undefined') {
        var categoryCounter = Date.now();
        
        $('#add-category-full').on('click', function(e) {
            e.preventDefault();
            categoryCounter++;
            var newCategoryId = 'new_' + categoryCounter;
            
            var categoryHtml = '<div class="category-wrapper" data-category-id="' + newCategoryId + '">';
            categoryHtml += '<div class="category-full-form">';
            categoryHtml += '<div class="category-header-section">';
            categoryHtml += '<h3>';
            categoryHtml += '<input type="text" name="categories[' + newCategoryId + '][name]" placeholder="Category Name (e.g., Buy Facebook Likes)" class="regular-text" required />';
            categoryHtml += '<input type="hidden" name="categories[' + newCategoryId + '][id]" value="0" />';
            categoryHtml += '</h3>';
            categoryHtml += '<button type="button" class="button delete-category-full">Delete Category</button>';
            categoryHtml += '</div>';
            categoryHtml += '<div class="category-description-section">';
            categoryHtml += '<textarea name="categories[' + newCategoryId + '][description]" rows="2" placeholder="Category Description (optional)" class="large-text"></textarea>';
            categoryHtml += '</div>';
            categoryHtml += '<div class="subcategories-section">';
            categoryHtml += '<h4>Sub-Categories</h4>';
            categoryHtml += '<div class="subcategories-list"></div>';
            categoryHtml += '<button type="button" class="button add-subcat-full">Add Sub-Category</button>';
            categoryHtml += '</div>';
            categoryHtml += '<div class="variations-section">';
            categoryHtml += '<h4>Variations & Prices</h4>';
            categoryHtml += '<div class="variations-list"></div>';
            categoryHtml += '<button type="button" class="button add-variation-full">Add Variation/Price</button>';
            categoryHtml += '</div>';
            categoryHtml += '</div>';
            categoryHtml += '</div>';
            
            $('#categories-list').append(categoryHtml);
            
            $('html, body').animate({
                scrollTop: $('#categories-list .category-wrapper').last().offset().top - 100
            }, 500);
        });
        
        $(document).on('click', '.delete-category-full', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this category?')) {
                $(this).closest('.category-wrapper').fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }
});
</script>

