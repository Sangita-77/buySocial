<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Products', 'custom-product-manager'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=cpm-add-product'); ?>" class="page-title-action"><?php _e('Add New', 'custom-product-manager'); ?></a>
    <hr class="wp-header-end">
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'custom-product-manager'); ?></th>
                <th><?php _e('Name', 'custom-product-manager'); ?></th>
                <th><?php _e('Status', 'custom-product-manager'); ?></th>
                <th><?php _e('Created', 'custom-product-manager'); ?></th>
                <th><?php _e('Actions', 'custom-product-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($products) : ?>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td><?php echo esc_html($product->id); ?></td>
                        <td><strong><a href="<?php echo admin_url('admin.php?page=cpm-add-product&product_id=' . $product->id); ?>"><?php echo esc_html($product->name); ?></a></strong></td>
                        <td><span class="status-<?php echo esc_attr($product->status); ?>"><?php echo esc_html(ucfirst($product->status)); ?></span></td>
                        <td><?php echo esc_html($product->created_at); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=cpm-add-product&product_id=' . $product->id); ?>"><?php _e('Edit', 'custom-product-manager'); ?></a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cpm-products&action=delete&product_id=' . $product->id), 'delete_product_' . $product->id); ?>" onclick="return confirm('<?php _e('Are you sure?', 'custom-product-manager'); ?>');"><?php _e('Delete', 'custom-product-manager'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php _e('No products found.', 'custom-product-manager'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>




