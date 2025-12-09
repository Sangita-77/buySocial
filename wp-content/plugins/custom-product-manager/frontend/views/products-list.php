<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cpm-products-list">
    <?php if ($products) : ?>
        <div class="cpm-products-grid">
            <?php foreach ($products as $product) : ?>
                <div class="cpm-product-card">
                    <h3><a href="<?php echo add_query_arg('product_id', $product->id); ?>"><?php echo esc_html($product->name); ?></a></h3>
                    <?php if ($product->description) : ?>
                        <div class="cpm-product-description">
                            <?php echo wp_kses_post($product->description); ?>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo add_query_arg('product_id', $product->id); ?>" class="cpm-view-product-btn"><?php _e('View Product', 'custom-product-manager'); ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p><?php _e('No products found.', 'custom-product-manager'); ?></p>
    <?php endif; ?>
</div>




