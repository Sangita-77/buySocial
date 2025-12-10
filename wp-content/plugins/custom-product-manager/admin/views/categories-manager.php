<?php
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$categories = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM " . CPM_Database::get_table('categories') . " 
    WHERE product_id = %d ORDER BY display_order ASC, id ASC",
    $product_id
));
?>

<button type="button" class="button" id="add-category-btn"><?php _e('Add Category', 'custom-product-manager'); ?></button>

<div id="categories-list">
    <?php if ($categories) : ?>
        <?php foreach ($categories as $category) : ?>
            <div class="category-item" data-category-id="<?php echo esc_attr($category->id); ?>">
                <?php include CPM_PLUGIN_DIR . 'admin/views/category-item.php'; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="category-template" style="display: none;">
    <div class="category-item" data-category-id="0">
        <?php
        $category = (object) array('id' => 0, 'name' => '', 'description' => '', 'display_order' => 0);
        include CPM_PLUGIN_DIR . 'admin/views/category-item.php';
        ?>
    </div>
</div>





