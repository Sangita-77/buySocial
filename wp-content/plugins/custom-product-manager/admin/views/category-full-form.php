<?php
/**
 * Complete category form with subcategories and variations
 */
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;

$category_id = isset($category) ? $category->id : 0;
$is_new = $category_id === 0;

if (!isset($subcategories)) {
    $subcategories = array();
}
if (!isset($variations)) {
    $variations = array();
}
?>

<div class="category-full-form">
    <div class="category-header-section">
        <h3>
            <input type="text" name="categories[<?php echo esc_attr($category_id); ?>][name]" 
                   value="<?php echo esc_attr($category->name ?? ''); ?>" 
                   placeholder="<?php _e('Category Name (e.g., Buy Facebook Likes)', 'custom-product-manager'); ?>" 
                   class="regular-text" required />
            <input type="hidden" name="categories[<?php echo esc_attr($category_id); ?>][id]" value="<?php echo esc_attr($category_id); ?>" />
        </h3>
        <button type="button" class="button delete-category-full"><?php _e('Delete Category', 'custom-product-manager'); ?></button>
    </div>
    
    <div class="category-description-section">
        <textarea name="categories[<?php echo esc_attr($category_id); ?>][description]" 
                  rows="2" placeholder="<?php _e('Category Description (optional)', 'custom-product-manager'); ?>" 
                  class="large-text"><?php echo esc_textarea($category->description ?? ''); ?></textarea>
    </div>
    
    <div class="subcategories-section">
        <h4><?php _e('Sub-Categories', 'custom-product-manager'); ?></h4>
        <div class="subcategories-list">
            <?php foreach ($subcategories as $subcat) : ?>
                <div class="subcategory-full-item" data-subcat-id="<?php echo esc_attr($subcat->id); ?>">
                    <div class="subcat-header">
                        <input type="text" name="categories[<?php echo esc_attr($category_id); ?>][subcategories][<?php echo esc_attr($subcat->id); ?>][name]" 
                               value="<?php echo esc_attr($subcat->name); ?>" 
                               placeholder="<?php _e('Sub-Category Name', 'custom-product-manager'); ?>" 
                               class="regular-text" />
                        <input type="hidden" name="categories[<?php echo esc_attr($category_id); ?>][subcategories][<?php echo esc_attr($subcat->id); ?>][id]" value="<?php echo esc_attr($subcat->id); ?>" />
                        <select name="categories[<?php echo esc_attr($category_id); ?>][subcategories][<?php echo esc_attr($subcat->id); ?>][type]">
                            <option value="select" <?php selected($subcat->field_type, 'select'); ?>><?php _e('Dropdown', 'custom-product-manager'); ?></option>
                            <option value="number" <?php selected($subcat->field_type, 'number'); ?>><?php _e('Number', 'custom-product-manager'); ?></option>
                            <option value="text" <?php selected($subcat->field_type, 'text'); ?>><?php _e('Text', 'custom-product-manager'); ?></option>
                        </select>
                        <button type="button" class="button delete-subcat-full"><?php _e('Delete', 'custom-product-manager'); ?></button>
                    </div>
                    <?php if ($subcat->field_type === 'select') : ?>
                        <?php
                        $options = $subcat->options ? explode('|||', $subcat->options) : array();
                        ?>
                        <div class="subcat-options">
                            <label><?php _e('Options:', 'custom-product-manager'); ?></label>
                            <?php foreach ($options as $opt) : ?>
                                <div class="option-row">
                                    <input type="text" name="categories[<?php echo esc_attr($category_id); ?>][subcategories][<?php echo esc_attr($subcat->id); ?>][options][]" 
                                           value="<?php echo esc_attr(trim($opt)); ?>" 
                                           placeholder="<?php _e('Option value', 'custom-product-manager'); ?>" />
                                    <button type="button" class="button delete-option-full"><?php _e('Remove', 'custom-product-manager'); ?></button>
                                </div>
                            <?php endforeach; ?>
                            <button type="button" class="button add-option-full"><?php _e('Add Option', 'custom-product-manager'); ?></button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button add-subcat-full"><?php _e('Add Sub-Category', 'custom-product-manager'); ?></button>
    </div>
    
    <div class="variations-section">
        <h4><?php _e('Variations & Prices', 'custom-product-manager'); ?></h4>
        <div class="variations-list">
            <?php foreach ($variations as $variation) : ?>
                <?php
                $var_data = json_decode($variation->variation_data, true);
                ?>
                <div class="variation-full-item" data-variation-id="<?php echo esc_attr($variation->id); ?>">
                    <input type="hidden" name="categories[<?php echo esc_attr($category_id); ?>][variations][<?php echo esc_attr($variation->id); ?>][id]" value="<?php echo esc_attr($variation->id); ?>" />
                    <div class="variation-fields-row">
                        <?php foreach ($subcategories as $subcat) : ?>
                            <div class="variation-field">
                                <label><?php echo esc_html($subcat->name); ?></label>
                                <?php if ($subcat->field_type === 'select') : ?>
                                    <?php
                                    $options = $subcat->options ? explode('|||', $subcat->options) : array();
                                    $selected = isset($var_data[$subcat->id]) ? $var_data[$subcat->id] : '';
                                    ?>
                                    <select name="categories[<?php echo esc_attr($category_id); ?>][variations][<?php echo esc_attr($variation->id); ?>][data][<?php echo esc_attr($subcat->id); ?>]">
                                        <option value=""><?php _e('Select...', 'custom-product-manager'); ?></option>
                                        <?php foreach ($options as $opt) : ?>
                                            <option value="<?php echo esc_attr(trim($opt)); ?>" <?php selected($selected, trim($opt)); ?>><?php echo esc_html(trim($opt)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($subcat->field_type === 'number') : ?>
                                    <input type="number" name="categories[<?php echo esc_attr($category_id); ?>][variations][<?php echo esc_attr($variation->id); ?>][data][<?php echo esc_attr($subcat->id); ?>]" 
                                           value="<?php echo isset($var_data[$subcat->id]) ? esc_attr($var_data[$subcat->id]) : ''; ?>" />
                                <?php else : ?>
                                    <input type="text" name="categories[<?php echo esc_attr($category_id); ?>][variations[<?php echo esc_attr($variation->id); ?>][data][<?php echo esc_attr($subcat->id); ?>]" 
                                           value="<?php echo isset($var_data[$subcat->id]) ? esc_attr($var_data[$subcat->id]) : ''; ?>" />
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="variation-field">
                            <label><?php _e('Price', 'custom-product-manager'); ?></label>
                            <input type="number" name="categories[<?php echo esc_attr($category_id); ?>][variations][<?php echo esc_attr($variation->id); ?>][price]" 
                                   value="<?php echo esc_attr($variation->price); ?>" 
                                   step="0.01" min="0" required />
                        </div>
                        <div class="variation-field">
                            <button type="button" class="button delete-variation-full"><?php _e('Delete', 'custom-product-manager'); ?></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button add-variation-full"><?php _e('Add Variation/Price', 'custom-product-manager'); ?></button>
    </div>
</div>

<style>
.category-full-form {
    background: #fff;
    padding: 20px;
    margin: 15px 0;
}
.category-header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.category-header-section h3 {
    margin: 0;
    flex: 1;
}
.category-header-section input {
    width: 100%;
    max-width: 400px;
}
.subcategories-section, .variations-section {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid #ddd;
}
.subcategory-full-item, .variation-full-item {
    background: #f5f5f5;
    padding: 15px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 3px;
}
.subcat-header {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}
.subcat-header input {
    flex: 1;
}
.option-row {
    display: flex;
    gap: 5px;
    margin: 5px 0;
}
.option-row input {
    flex: 1;
    max-width: 300px;
}
.variation-fields-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    align-items: end;
}
.variation-field label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 12px;
}
</style>




