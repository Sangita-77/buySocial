<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Product Reviews', 'custom-product-manager'); ?></h1>
    
    <ul class="subsubsub">
        <li><a href="?page=cpm-reviews&status=all" <?php echo ($status_filter === 'all') ? 'class="current"' : ''; ?>><?php _e('All', 'custom-product-manager'); ?> <span class="count">(<?php echo isset($all_count) ? $all_count : count($reviews); ?>)</span></a> |</li>
        <li><a href="?page=cpm-reviews&status=pending" <?php echo ($status_filter === 'pending') ? 'class="current"' : ''; ?>><?php _e('Pending', 'custom-product-manager'); ?> <span class="count">(<?php echo $pending_count; ?>)</span></a> |</li>
        <li><a href="?page=cpm-reviews&status=approved" <?php echo ($status_filter === 'approved') ? 'class="current"' : ''; ?>><?php _e('Approved', 'custom-product-manager'); ?> <span class="count">(<?php echo $approved_count; ?>)</span></a> |</li>
        <li><a href="?page=cpm-reviews&status=rejected" <?php echo ($status_filter === 'rejected') ? 'class="current"' : ''; ?>><?php _e('Rejected', 'custom-product-manager'); ?> <span class="count">(<?php echo $rejected_count; ?>)</span></a></li>
    </ul>
    
    <?php
    // Check if table exists
    $table_name = CPM_Database::get_table('reviews');
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    
    if (!$table_exists) {
        echo '<div class="notice notice-error"><p><strong>' . __('Error:', 'custom-product-manager') . '</strong> ' . __('Reviews table does not exist. Please deactivate and reactivate the plugin to create the table.', 'custom-product-manager') . '</p></div>';
    } else {
        // Debug info (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $total_reviews_debug = $wpdb->get_var("SELECT COUNT(*) FROM " . $table_name);
            $pending_debug = $wpdb->get_var("SELECT COUNT(*) FROM " . $table_name . " WHERE status = 'pending'");
            echo '<div class="notice notice-info"><p>Debug: Total reviews in database: ' . $total_reviews_debug . ', Pending: ' . $pending_debug . ', Current filter: ' . esc_html($status_filter) . '</p></div>';
        }
    }
    ?>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Reviewer', 'custom-product-manager'); ?></th>
                <th><?php _e('Product', 'custom-product-manager'); ?></th>
                <th><?php _e('Rating', 'custom-product-manager'); ?></th>
                <th><?php _e('Review', 'custom-product-manager'); ?></th>
                <th><?php _e('Status', 'custom-product-manager'); ?></th>
                <th><?php _e('Date', 'custom-product-manager'); ?></th>
                <th><?php _e('Actions', 'custom-product-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($reviews) : ?>
                <?php foreach ($reviews as $review) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($review->reviewer_name); ?></strong><br>
                            <small><?php echo esc_html($review->reviewer_email); ?></small>
                            <?php if ($review->verified_purchase) : ?>
                                <br><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php _e('Verified Purchase', 'custom-product-manager'); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($review->product_name ? $review->product_name : __('Product #' . $review->product_id, 'custom-product-manager')); ?></td>
                        <td>
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $review->rating) {
                                    echo '<span style="color: #ffc107;">★</span>';
                                } else {
                                    echo '<span style="color: #ddd;">★</span>';
                                }
                            }
                            ?>
                            <span>(<?php echo $review->rating; ?>/5)</span>
                        </td>
                        <td><?php echo esc_html(wp_trim_words($review->review_text, 20)); ?></td>
                        <td>
                            <span class="status-<?php echo esc_attr($review->status); ?>">
                                <?php echo esc_html(ucfirst($review->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($review->created_at))); ?></td>
                        <td>
                            <?php if ($review->status === 'pending') : ?>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cpm-reviews&action=approve&review_id=' . $review->id), 'cpm_review_action'); ?>" class="button button-small"><?php _e('Approve', 'custom-product-manager'); ?></a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cpm-reviews&action=reject&review_id=' . $review->id), 'cpm_review_action'); ?>" class="button button-small"><?php _e('Reject', 'custom-product-manager'); ?></a>
                            <?php elseif ($review->status === 'approved') : ?>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cpm-reviews&action=reject&review_id=' . $review->id), 'cpm_review_action'); ?>" class="button button-small"><?php _e('Reject', 'custom-product-manager'); ?></a>
                            <?php elseif ($review->status === 'rejected') : ?>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cpm-reviews&action=approve&review_id=' . $review->id), 'cpm_review_action'); ?>" class="button button-small"><?php _e('Approve', 'custom-product-manager'); ?></a>
                            <?php endif; ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cpm-reviews&action=delete&review_id=' . $review->id), 'cpm_review_action'); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this review?', 'custom-product-manager'); ?>');"><?php _e('Delete', 'custom-product-manager'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7"><?php _e('No reviews found.', 'custom-product-manager'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.status-pending { color: #ffc107; font-weight: bold; }
.status-approved { color: #28a745; font-weight: bold; }
.status-rejected { color: #dc3545; font-weight: bold; }
</style>

