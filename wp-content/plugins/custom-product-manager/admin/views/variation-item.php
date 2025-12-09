<?php
if (!defined('ABSPATH')) {
    exit;
}

$variation_id = isset($variation) ? $variation->id : 0;
$variation_data = isset($variation) ? json_decode($variation->variation_data, true) : array();
$price = isset($variation) ? $variation->price : 0;

// Get subcategories to build variation form
global $wpdb;
$category_id = isset($variation) ? $variation->category_id : 0;
$subcategories = array();
if ($category_id) {
    $subcategories = $wpdb->get_results($wpdb->prepare(
        "SELECT sc.*, 
        (SELECT GROUP_CONCAT(option_value SEPARATOR ',') FROM " . CPM_Database::get_table('subcategory_options') . " WHERE subcategory_id = sc.id) as options
        FROM " . CPM_Database::get_table('subcategories') . " sc
        WHERE sc.category_id = %d ORDER BY sc.display_order ASC",
        $category_id
    ));
}
?>

<div class="variation-box">
    <div class="variation-header">
        <h4><?php _e('Variation', 'custom-product-manager'); ?></h4>
        <button type="button" class="button delete-variation-btn"><?php _e('Delete', 'custom-product-manager'); ?></button>
    </div>
    
    <input type="hidden" name="variation_id[]" value="<?php echo esc_attr($variation_id); ?>" />
    
    <div class="variation-fields">
        <?php foreach ($subcategories as $subcat) : ?>
            <div class="variation-field">
                <label><?php echo esc_html($subcat->name); ?></label>
                <?php if ($subcat->field_type === 'select') : ?>
                    <select name="variation_data[<?php echo esc_attr($variation_id); ?>][<?php echo esc_attr($subcat->id); ?>]" class="variation-select">
                        <option value=""><?php _e('Select...', 'custom-product-manager'); ?></option>
                        <?php
                        $options = explode(',', $subcat->options);
                        $selected_value = isset($variation_data[$subcat->id]) ? $variation_data[$subcat->id] : '';
                        foreach ($options as $option) {
                            $option = trim($option);
                            if ($option) {
                                echo '<option value="' . esc_attr($option) . '" ' . selected($selected_value, $option, false) . '>' . esc_html($option) . '</option>';
                            }
                        }
                        ?>
                    </select>
                <?php elseif ($subcat->field_type === 'number') : ?>
                    <input type="number" name="variation_data[<?php echo esc_attr($variation_id); ?>][<?php echo esc_attr($subcat->id); ?>]" 
                           value="<?php echo isset($variation_data[$subcat->id]) ? esc_attr($variation_data[$subcat->id]) : ''; ?>" 
                           class="variation-number" placeholder="<?php _e('Enter number', 'custom-product-manager'); ?>" />
                <?php else : ?>
                    <input type="text" name="variation_data[<?php echo esc_attr($variation_id); ?>][<?php echo esc_attr($subcat->id); ?>]" 
                           value="<?php echo isset($variation_data[$subcat->id]) ? esc_attr($variation_data[$subcat->id]) : ''; ?>" 
                           class="variation-text" placeholder="<?php _e('Enter value', 'custom-product-manager'); ?>" />
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="variation-field">
            <label><?php _e('Price', 'custom-product-manager'); ?></label>
            <input type="number" name="variation_price[]" value="<?php echo esc_attr($price); ?>" 
                   step="0.01" min="0" class="variation-price" placeholder="0.00" required />
        </div>
    </div>
</div>

<style>
.variation-box {
    background: #f5f5f5;
    padding: 15px;
    margin: 10px 0;
    border: 1px solid #ddd;
}
.variation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.variation-fields {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}
.variation-field label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}
.variation-field input,
.variation-field select {
    width: 100%;
    padding: 5px;
}
</style>




