<?php
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;

$subcat_id = isset($subcat) ? $subcat->id : 0;
$subcat_name = isset($subcat) ? $subcat->name : '';
$subcat_type = isset($subcat) ? $subcat->field_type : 'select';

// Get options for this subcategory
$options = array();
if ($subcat_id) {
    $options = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('subcategory_options') . " 
        WHERE subcategory_id = %d ORDER BY display_order ASC",
        $subcat_id
    ));
}
?>

<div class="subcategory-box">
    <div class="subcategory-header">
        <input type="text" name="subcat_name[]" class="subcat-name" value="<?php echo esc_attr($subcat_name); ?>" placeholder="<?php _e('Sub-Category Name (e.g., Select Facebook Like Type)', 'custom-product-manager'); ?>" />
        <select name="subcat_type[]" class="subcat-type">
            <option value="select" <?php selected($subcat_type, 'select'); ?>><?php _e('Dropdown/Select', 'custom-product-manager'); ?></option>
            <option value="number" <?php selected($subcat_type, 'number'); ?>><?php _e('Number Input', 'custom-product-manager'); ?></option>
            <option value="text" <?php selected($subcat_type, 'text'); ?>><?php _e('Text Input', 'custom-product-manager'); ?></option>
        </select>
        <button type="button" class="button delete-subcat-btn"><?php _e('Delete', 'custom-product-manager'); ?></button>
    </div>
    
    <input type="hidden" name="subcat_id[]" value="<?php echo esc_attr($subcat_id); ?>" />
    
    <?php if ($subcat_type === 'select') : ?>
        <div class="subcat-options-container">
            <label><strong><?php _e('Options:', 'custom-product-manager'); ?></strong></label>
            <div class="options-list">
                <?php if ($options) : ?>
                    <?php foreach ($options as $option) : ?>
                        <div class="option-item">
                            <input type="text" name="subcat_option[<?php echo esc_attr($subcat_id); ?>][]" value="<?php echo esc_attr($option->option_value); ?>" placeholder="<?php _e('Option value', 'custom-product-manager'); ?>" />
                            <button type="button" class="button delete-option-btn"><?php _e('Remove', 'custom-product-manager'); ?></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <button type="button" class="button add-option-btn"><?php _e('Add Option', 'custom-product-manager'); ?></button>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.subcategory-box {
    margin: 10px 0;
}
.subcategory-header {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}
.subcat-name {
    flex: 1;
    max-width: 300px;
}
.subcat-type {
    width: 150px;
}
.options-list {
    margin-top: 10px;
}
.option-item {
    display: flex;
    gap: 5px;
    margin: 5px 0;
}
.option-item input {
    flex: 1;
    max-width: 250px;
}
</style>




