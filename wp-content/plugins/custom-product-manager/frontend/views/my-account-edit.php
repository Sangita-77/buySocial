<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user = isset($current_user) ? $current_user : wp_get_current_user();

// Handle form submission
$update_message = '';
$update_error = '';

if (isset($_POST['cpm_update_account']) && wp_verify_nonce($_POST['cpm_update_account_nonce'], 'cpm_update_account')) {
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $display_name = isset($_POST['display_name']) ? sanitize_text_field($_POST['display_name']) : '';
    
    if ($email && is_email($email)) {
        $user_data = array(
            'ID' => $current_user->ID,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_email' => $email,
            'display_name' => $display_name
        );
        
        $result = wp_update_user($user_data);
        
        if (!is_wp_error($result)) {
            $update_message = __('Account details updated successfully.', 'custom-product-manager');
            $current_user = wp_get_current_user(); // Refresh user data
        } else {
            $update_error = $result->get_error_message();
        }
    } else {
        $update_error = __('Please enter a valid email address.', 'custom-product-manager');
    }
}
?>

<div class="cpm-my-account-wrapper">
    <div class="cpm-my-account-container">
        <div class="cpm-my-account-header">
            <h1><?php _e('Edit Account', 'custom-product-manager'); ?></h1>
            <a href="<?php echo home_url('/my-account/'); ?>" class="cpm-back-link"><?php _e('← Back to Account', 'custom-product-manager'); ?></a>
        </div>
        
        <div class="cpm-my-account-content">
            <?php if ($update_message) : ?>
                <div class="cpm-message cpm-success"><?php echo esc_html($update_message); ?></div>
            <?php endif; ?>
            
            <?php if ($update_error) : ?>
                <div class="cpm-message cpm-error"><?php echo esc_html($update_error); ?></div>
            <?php endif; ?>
            
            <form method="post" class="cpm-edit-account-form">
                <?php wp_nonce_field('cpm_update_account', 'cpm_update_account_nonce'); ?>
                
                <div class="cpm-form-group">
                    <label for="first_name"><?php _e('First Name', 'custom-product-manager'); ?></label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>" />
                </div>
                
                <div class="cpm-form-group">
                    <label for="last_name"><?php _e('Last Name', 'custom-product-manager'); ?></label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>" />
                </div>
                
                <div class="cpm-form-group">
                    <label for="display_name"><?php _e('Display Name', 'custom-product-manager'); ?></label>
                    <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr($current_user->display_name); ?>" />
                </div>
                
                <div class="cpm-form-group">
                    <label for="email"><?php _e('Email Address', 'custom-product-manager'); ?> <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required />
                </div>
                
                <div class="cpm-form-group">
                    <button type="submit" name="cpm_update_account" class="cpm-submit-btn"><?php _e('Save Changes', 'custom-product-manager'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

