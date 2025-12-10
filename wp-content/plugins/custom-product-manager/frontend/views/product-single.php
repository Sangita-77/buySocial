<?php
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
?>

<div class="cpm-product-single">
    <h1><?php echo esc_html($product->name); ?></h1>
    
    <?php if ($product->description) : ?>
        <div class="cpm-product-description">
            <?php echo wp_kses_post($product->description); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($categories) : ?>
        <div class="cpm-categories">
            <?php foreach ($categories as $category) : ?>
                <div class="cpm-category-box" data-category-id="<?php echo esc_attr($category->id); ?>">
                    <h3><?php echo esc_html($category->name); ?></h3>
                    
                    <?php if ($category->description) : ?>
                        <p class="category-description"><?php echo esc_html($category->description); ?></p>
                    <?php endif; ?>
                    
                    <?php
                    // Get subcategories for this category
                    $subcategories = $wpdb->get_results($wpdb->prepare(
                        "SELECT sc.*,
                        (SELECT GROUP_CONCAT(option_value SEPARATOR '|||') FROM " . CPM_Database::get_table('subcategory_options') . " WHERE subcategory_id = sc.id ORDER BY display_order) as options
                        FROM " . CPM_Database::get_table('subcategories') . " sc
                        WHERE sc.category_id = %d AND sc.status = 'active'
                        ORDER BY sc.display_order ASC, sc.id ASC",
                        $category->id
                    ));
                    ?>
                    
                    <?php if ($subcategories) : ?>
                        <form class="cpm-product-form" data-category-id="<?php echo esc_attr($category->id); ?>">
                            <?php foreach ($subcategories as $subcat) : ?>
                                <div class="cpm-field-group">
                                    <label><?php echo esc_html($subcat->name); ?></label>
                                    
                                    <?php if ($subcat->field_type === 'select') : ?>
                                        <?php
                                        $options = $subcat->options ? explode('|||', $subcat->options) : array();
                                        ?>
                                        <select name="variation[<?php echo esc_attr($subcat->id); ?>]" class="cpm-variation-select" required>
                                            <option value=""><?php _e('Select...', 'custom-product-manager'); ?></option>
                                            <?php foreach ($options as $option) : ?>
                                                <option value="<?php echo esc_attr(trim($option)); ?>"><?php echo esc_html(trim($option)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php elseif ($subcat->field_type === 'number') : ?>
                                        <input type="number" name="variation[<?php echo esc_attr($subcat->id); ?>]" 
                                               class="cpm-variation-number" min="1" required />
                                    <?php else : ?>
                                        <input type="text" name="variation[<?php echo esc_attr($subcat->id); ?>]" 
                                               class="cpm-variation-text" required />
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="cpm-price-section">
                                <div class="cpm-price-display">
                                    <span class="price-label"><?php _e('Price:', 'custom-product-manager'); ?></span>
                                    <span class="price-value"><?php _e('Select options to see price', 'custom-product-manager'); ?></span>
                                </div>
                                
                                <div class="cpm-quantity-section">
                                    <label><?php _e('Quantity:', 'custom-product-manager'); ?></label>
                                    <input type="number" name="quantity" value="1" min="1" class="cpm-quantity" />
                                </div>
                                
                                <button type="submit" class="cpm-add-to-cart-btn" disabled>
                                    <?php _e('Add to Cart', 'custom-product-manager'); ?>
                                </button>
                            </div>
                            
                            <input type="hidden" name="variation_id" class="variation-id" />
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p><?php _e('No categories available for this product.', 'custom-product-manager'); ?></p>
    <?php endif; ?>
</div>





