<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get error and success messages from template args
$error_message = isset($error_message) ? $error_message : '';
$success_message = isset($success_message) ? $success_message : '';

// Note: Form handling is done in class-cpm-frontend.php get_login_form() method
?>

<div class="cpm-auth-container">
    <div class="cpm-auth-box">
        <div class="cpm-auth-header">
            <h2><?php _e('Login', 'custom-product-manager'); ?></h2>
            <p><?php _e('Welcome back! Please login to your account.', 'custom-product-manager'); ?></p>
        </div>
        
        <?php if ($error_message) : ?>
            <div class="cpm-auth-error">
                <span class="cpm-error-icon">⚠</span>
                <?php echo esc_html($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message) : ?>
            <div class="cpm-auth-success">
                <span class="cpm-success-icon">✓</span>
                <?php echo esc_html($success_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(home_url('/login/')); ?>" class="cpm-auth-form" id="cpm-login-form">
            <?php wp_nonce_field('cpm_login', 'cpm_login_nonce'); ?>
            
            <div class="cpm-form-group">
                <label for="username"><?php _e('Username or Email', 'custom-product-manager'); ?> <span class="required">*</span></label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?php echo isset($_POST['username']) ? esc_attr($_POST['username']) : ''; ?>" 
                       required 
                       autocomplete="username" />
                <span class="cpm-input-icon">👤</span>
            </div>
            
            <div class="cpm-form-group">
                <label for="password"><?php _e('Password', 'custom-product-manager'); ?> <span class="required">*</span></label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="current-password" />
                <span class="cpm-input-icon">🔒</span>
                <span class="cpm-toggle-password" data-target="password">👁</span>
            </div>
            
            <div class="cpm-form-options">
                <label class="cpm-checkbox-label">
                    <input type="checkbox" name="remember" value="1" />
                    <span><?php _e('Remember me', 'custom-product-manager'); ?></span>
                </label>
                <a href="<?php echo wp_lostpassword_url(); ?>" class="cpm-forgot-password"><?php _e('Forgot Password?', 'custom-product-manager'); ?></a>
            </div>
            
            <button type="submit" name="cpm_login" class="cpm-auth-submit-btn">
                <?php _e('Login', 'custom-product-manager'); ?>
            </button>
            
            <div class="cpm-auth-divider">
                <span><?php _e('OR', 'custom-product-manager'); ?></span>
            </div>
            
            <div class="cpm-auth-footer">
                <p><?php _e("Don't have an account?", 'custom-product-manager'); ?> 
                    <a href="<?php echo home_url('/register/'); ?>" class="cpm-auth-link"><?php _e('Sign Up', 'custom-product-manager'); ?></a>
                </p>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Toggle password visibility
    $('.cpm-toggle-password').on('click', function() {
        var target = $(this).data('target');
        var $input = $('#' + target);
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).text(type === 'password' ? '👁' : '🙈');
    });
});
</script>

