<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get error and success messages from template args
$error_message = isset($error_message) ? $error_message : '';
$success_message = isset($success_message) ? $success_message : '';

// Note: Form handling is done in class-cpm-frontend.php get_register_form() method
?>

<div class="cpm-auth-container">
    <div class="cpm-auth-box">
        <div class="cpm-auth-header">
            <h2><?php _e('Create Account', 'custom-product-manager'); ?></h2>
            <p><?php _e('Join us today! Create your account to get started.', 'custom-product-manager'); ?></p>
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
        
        <form method="post" action="<?php echo esc_url(home_url('/register/')); ?>" class="cpm-auth-form" id="cpm-register-form">
            <?php wp_nonce_field('cpm_register', 'cpm_register_nonce'); ?>
            
            <div class="cpm-form-row">
                <div class="cpm-form-group">
                    <label for="first_name"><?php _e('First Name', 'custom-product-manager'); ?></label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           value="<?php echo isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : ''; ?>" 
                           autocomplete="given-name" />
                    <span class="cpm-input-icon">👤</span>
                </div>
                
                <div class="cpm-form-group">
                    <label for="last_name"><?php _e('Last Name', 'custom-product-manager'); ?></label>
                    <input type="text" 
                           id="last_name" 
                           name="last_name" 
                           value="<?php echo isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : ''; ?>" 
                           autocomplete="family-name" />
                    <span class="cpm-input-icon">👤</span>
                </div>
            </div>
            
            <div class="cpm-form-group">
                <label for="username"><?php _e('Username', 'custom-product-manager'); ?> <span class="required">*</span></label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?php echo isset($_POST['username']) ? esc_attr($_POST['username']) : ''; ?>" 
                       required 
                       autocomplete="username" />
                <span class="cpm-input-icon">👤</span>
            </div>
            
            <div class="cpm-form-group">
                <label for="email"><?php _e('Email Address', 'custom-product-manager'); ?> <span class="required">*</span></label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo isset($_POST['email']) ? esc_attr($_POST['email']) : ''; ?>" 
                       required 
                       autocomplete="email" />
                <span class="cpm-input-icon">✉</span>
            </div>
            
            <div class="cpm-form-group">
                <label for="password"><?php _e('Password', 'custom-product-manager'); ?> <span class="required">*</span></label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="new-password" 
                       minlength="6" />
                <span class="cpm-input-icon">🔒</span>
                <span class="cpm-toggle-password" data-target="password">👁</span>
                <small class="cpm-help-text"><?php _e('Minimum 6 characters', 'custom-product-manager'); ?></small>
            </div>
            
            <div class="cpm-form-group">
                <label for="confirm_password"><?php _e('Confirm Password', 'custom-product-manager'); ?> <span class="required">*</span></label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       required 
                       autocomplete="new-password" 
                       minlength="6" />
                <span class="cpm-input-icon">🔒</span>
                <span class="cpm-toggle-password" data-target="confirm_password">👁</span>
            </div>
            
            <button type="submit" name="cpm_register" class="cpm-auth-submit-btn">
                <?php _e('Create Account', 'custom-product-manager'); ?>
            </button>
            
            <div class="cpm-auth-divider">
                <span><?php _e('OR', 'custom-product-manager'); ?></span>
            </div>
            
            <div class="cpm-auth-footer">
                <p><?php _e('Already have an account?', 'custom-product-manager'); ?> 
                    <a href="<?php echo home_url('/login/'); ?>" class="cpm-auth-link"><?php _e('Login', 'custom-product-manager'); ?></a>
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
    
    // Password match validation
    $('#confirm_password').on('keyup', function() {
        var password = $('#password').val();
        var confirm = $(this).val();
        
        if (confirm && password !== confirm) {
            $(this).css('border-color', '#dc3545');
            if (!$('#password-mismatch').length) {
                $(this).after('<small id="password-mismatch" style="color: #dc3545; display: block; margin-top: 5px;">Passwords do not match</small>');
            }
        } else {
            $(this).css('border-color', '#28a745');
            $('#password-mismatch').remove();
        }
    });
});
</script>

