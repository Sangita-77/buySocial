<?php
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;

$category_id = isset($category) ? $category->id : 0;
$is_new = $category_id === 0;

// Get subcategories for this category
$subcategories = array();
if ($category_id) {
    $subcategories = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('subcategories') . " 
        WHERE category_id = %d ORDER BY display_order ASC, id ASC",
        $category_id
    ));
}

// Get variations for this category
$variations = array();
if ($category_id) {
    $variations = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('variations') . " 
        WHERE category_id = %d ORDER BY id ASC",
        $category_id
    ));
}
?>

<div class="category-box">
    <div class="category-header">
        <h3>
            <input type="text" name="category_name[]" class="category-name" value="<?php echo esc_attr($category->name ?? ''); ?>" placeholder="<?php _e('Category Name (e.g., Buy Facebook Likes)', 'custom-product-manager'); ?>" />
        </h3>
        <div class="category-actions">
            <button type="button" class="button add-subcategory-btn"><?php _e('Add Sub-Category', 'custom-product-manager'); ?></button>
            <button type="button" class="button add-variation-btn"><?php _e('Add Variation/Price', 'custom-product-manager'); ?></button>
            <button type="button" class="button delete-category-btn"><?php _e('Delete', 'custom-product-manager'); ?></button>
        </div>
    </div>
    
    <div class="category-content">
        <textarea name="category_description[]" class="category-description" rows="3" placeholder="<?php _e('Category Description (optional)', 'custom-product-manager'); ?>"><?php echo esc_textarea($category->description ?? ''); ?></textarea>
        
        <input type="hidden" name="category_id[]" value="<?php echo esc_attr($category_id); ?>" />
        <input type="hidden" name="category_display_order[]" value="<?php echo esc_attr($category->display_order ?? 0); ?>" />
        
        <div class="subcategories-container">
            <h4><?php _e('Sub-Categories', 'custom-product-manager'); ?></h4>
            <div class="subcategories-list">
                <?php if ($subcategories) : ?>
                    <?php foreach ($subcategories as $subcat) : ?>
                        <div class="subcategory-item" data-subcat-id="<?php echo esc_attr($subcat->id); ?>">
                            <?php include CPM_PLUGIN_DIR . 'admin/views/subcategory-item.php'; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="variations-container">
            <h4><?php _e('Variations & Prices', 'custom-product-manager'); ?></h4>
            <div class="variations-list">
                <?php if ($variations) : ?>
                    <?php foreach ($variations as $variation) : ?>
                        <div class="variation-item" data-variation-id="<?php echo esc_attr($variation->id); ?>">
                            <?php include CPM_PLUGIN_DIR . 'admin/views/variation-item.php'; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.category-box {
    border: 1px solid #ddd;
    margin: 15px 0;
    padding: 15px;
    background: #fff;
}
.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.category-header h3 {
    margin: 0;
}
.category-header input.category-name {
    font-size: 16px;
    font-weight: bold;
    width: 300px;
}
.category-actions {
    display: flex;
    gap: 5px;
}
.category-content {
    margin-top: 15px;
}
.subcategories-container, .variations-container {
    margin-top: 20px;
}
.subcategory-item, .variation-item {
    border: 1px solid #eee;
    padding: 10px;
    margin: 10px 0;
    background: #f9f9f9;
}
</style>





