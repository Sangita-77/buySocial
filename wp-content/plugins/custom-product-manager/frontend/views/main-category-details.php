<?php
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
?>

<div class="cpm-product-page-wrapper">
    <div class="cpm-breadcrumb">
        <a href="<?php echo home_url(); ?>"><?php _e('Home', 'custom-product-manager'); ?></a> &gt; 
        <a href="?cpm_products=1"><?php echo esc_html($product->name); ?></a> &gt; 
        <?php echo esc_html($main_category['name']); ?>
    </div>
    
    <div class="cpm-product-layout">
        <!-- Left Side: Product Visual -->
        <div class="cpm-product-visual">
            <div class="cpm-product-image-box">
                <div class="cpm-product-preview">
                    <div class="cpm-social-post-preview">
                        <div class="cpm-post-header">
                            <div class="cpm-profile-pic"></div>
                            <div class="cpm-username"><?php echo esc_html($main_category['name']); ?></div>
                        </div>
                        <div class="cpm-post-content">
                            <div class="cpm-social-icon">
                                <?php 
                                $icon_class = 'cpm-icon-facebook';
                                if (stripos($main_category['name'], 'instagram') !== false) {
                                    $icon_class = 'cpm-icon-instagram';
                                } elseif (stripos($main_category['name'], 'twitter') !== false || stripos($main_category['name'], 'x') !== false) {
                                    $icon_class = 'cpm-icon-twitter';
                                } elseif (stripos($main_category['name'], 'youtube') !== false) {
                                    $icon_class = 'cpm-icon-youtube';
                                } elseif (stripos($main_category['name'], 'tiktok') !== false) {
                                    $icon_class = 'cpm-icon-tiktok';
                                }
                                ?>
                                <span class="<?php echo esc_attr($icon_class); ?>"></span>
                            </div>
                        </div>
                        <div class="cpm-post-footer">
                            <span class="cpm-like-icon">👍</span>
                            <span class="cpm-likes-text"><?php _e('Likes', 'custom-product-manager'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side: Product Details and Form -->
        <div class="cpm-product-details">
            <h1 class="cpm-product-title"><?php echo esc_html($main_category['name']); ?></h1>
            
            <?php
            // Calculate average rating and total reviews for this product
            $total_reviews = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM " . CPM_Database::get_table('reviews') . " 
                WHERE product_id = %d AND status = 'approved'",
                $product_id
            ));
            
            $avg_rating = $wpdb->get_var($wpdb->prepare(
                "SELECT AVG(rating) FROM " . CPM_Database::get_table('reviews') . " 
                WHERE product_id = %d AND status = 'approved'",
                $product_id
            ));
            $avg_rating = $avg_rating ? round($avg_rating, 1) : 0;
            
            // Calculate star display
            $full_stars = floor($avg_rating);
            $has_half = ($avg_rating - $full_stars) >= 0.5;
            ?>
            
            <div class="cpm-product-rating">
                <div class="cpm-stars">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $full_stars) {
                            echo '<span class="cpm-star cpm-star-filled">★</span>';
                        } elseif ($i == $full_stars + 1 && $has_half) {
                            echo '<span class="cpm-star cpm-star-half">★</span>';
                        } else {
                            echo '<span class="cpm-star cpm-star-empty">★</span>';
                        }
                    }
                    ?>
                </div>
                <span class="cpm-rating-text"><?php echo esc_html($avg_rating); ?> / 5</span>
                <?php if ($total_reviews > 0) : ?>
                    <span class="cpm-reviews-text"><?php echo esc_html($total_reviews); ?> <?php _e('Verified customer reviews', 'custom-product-manager'); ?></span>
                <?php else : ?>
                    <span class="cpm-reviews-text"><?php _e('No reviews yet', 'custom-product-manager'); ?></span>
                <?php endif; ?>
            </div>
            
            <?php if ($product->description) : ?>
                <div class="cpm-product-description">
                    <?php echo wp_kses_post($product->description); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($main_category['categories']) && is_array($main_category['categories']) && !empty($main_category['categories'])) : ?>
                <form class="cpm-product-selection-form" id="cpm-product-selection-form">
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />
                    <input type="hidden" name="main_category_id" value="<?php echo esc_attr($main_category_id); ?>" />
                    <input type="hidden" name="main_category_name" value="<?php echo esc_attr($main_category['name']); ?>" />
                    
                    <!-- Category Selection -->
                    <div class="cpm-form-group">
                        <label for="cpm-category-select">
                            <?php echo sprintf(__('Select %s Type', 'custom-product-manager'), esc_html($main_category['name'])); ?> <span class="cpm-required">*</span>
                        </label>
                        <select id="cpm-category-select" name="category_id" class="cpm-select-field" required>
                            <option value=""><?php _e('-- Select Type --', 'custom-product-manager'); ?></option>
                            <?php foreach ($main_category['categories'] as $cat_id => $category) : ?>
                                <option value="<?php echo esc_attr($cat_id); ?>" 
                                        data-category-name="<?php echo esc_attr($category['name']); ?>"
                                        data-category-data='<?php echo json_encode($category); ?>'>
                                    <?php echo esc_html($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="cpm-info-box" id="cpm-category-info" style="display: none;">
                            <span class="cpm-info-icon">📦</span>
                            <span class="cpm-info-text"></span>
                        </div>
                    </div>
                    
                    <!-- Country Selection (hidden initially) -->
                    <div class="cpm-form-group" id="cpm-country-group" style="display: none;">
                        <label for="cpm-country-select"><?php _e('Select Target Country', 'custom-product-manager'); ?> <span class="cpm-required">*</span></label>
                        <select id="cpm-country-select" name="country_id" class="cpm-select-field">
                            <option value=""><?php _e('-- Select Country --', 'custom-product-manager'); ?></option>
                        </select>
                    </div>
                    
                    <!-- Quantity Selection (hidden initially) -->
                    <div class="cpm-form-group" id="cpm-quantity-group" style="display: none;">
                        <label for="cpm-quantity-select"><?php _e('Select Quantity', 'custom-product-manager'); ?> <span class="cpm-required">*</span></label>
                        <div class="cpm-quantity-wrapper">
                            <select id="cpm-quantity-select" name="quantity_id" class="cpm-select-field">
                                <option value=""><?php _e('-- Select Quantity --', 'custom-product-manager'); ?></option>
                            </select>
                            <span class="cpm-delivery-time" id="cpm-delivery-time" style="display: none;"></span>
                        </div>
                    </div>
                    
                    <!-- URL Input (optional, can be shown after quantity) -->
                    <div class="cpm-form-group" id="cpm-url-group" style="display: none;">
                        <label for="cpm-page-url"><?php _e('Enter Page URL', 'custom-product-manager'); ?> <span class="cpm-required">*</span></label>
                        <div class="cpm-url-wrapper">
                            <input type="url" id="cpm-page-url" name="page_url" class="cpm-url-field" placeholder="https://" />
                            <button type="button" class="cpm-sample-url-btn" id="cpm-sample-url-btn"><?php _e('Sample URLs ?', 'custom-product-manager'); ?></button>
                        </div>
                        <div class="cpm-url-validation-message" id="cpm-url-validation-message" style="display: none;"></div>
                    </div>
                    
                    <!-- Pricing Section -->
                    <div class="cpm-pricing-section" id="cpm-pricing-section" style="display: none;">
                        <div class="cpm-price-display-main">
                            <span class="cpm-current-price" id="cpm-current-price">$0.00</span>
                            <span class="cpm-original-price" id="cpm-original-price" style="display: none;">
                                <span class="cpm-strikethrough"></span>
                            </span>
                            <span class="cpm-savings-badge" id="cpm-savings-badge" style="display: none;"></span>
                        </div>
                        <input type="hidden" name="selected_price" id="cpm-selected-price-input" value="" />
                        <input type="hidden" name="selected_price_id" id="cpm-selected-price-id" value="" />
                        <input type="hidden" name="original_price" id="cpm-original-price-input" value="" />
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="cpm-action-buttons" id="cpm-action-buttons" style="display: none;">
                        <button type="button" class="cpm-add-to-cart-btn" id="cpm-add-to-cart-btn">
                            <span class="cpm-cart-icon">🛒</span>
                            <?php _e('ADD TO CART', 'custom-product-manager'); ?>
                        </button>
                        <button type="button" class="cpm-buy-now-btn" id="cpm-buy-now-btn">
                            <span class="cpm-bolt-icon">⚡</span>
                            <?php _e('BUY NOW', 'custom-product-manager'); ?>
                        </button>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="cpm-trust-badges">
                        <div class="cpm-trust-item">
                            <span class="cpm-trust-icon">✓</span>
                            <span><?php _e('100% Money Back Guarantee', 'custom-product-manager'); ?></span>
                        </div>
                        <div class="cpm-trust-item">
                            <span class="cpm-trust-icon">★</span>
                            <span><?php _e('5-Star Customer Support', 'custom-product-manager'); ?></span>
                        </div>
                        <div class="cpm-trust-item">
                            <span class="cpm-trust-icon">🔒</span>
                            <span><?php _e('Secure Transaction', 'custom-product-manager'); ?></span>
                        </div>
                    </div>
                </form>
            <?php else : ?>
                <p><?php _e('No categories available for this main category.', 'custom-product-manager'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var mainCategoryData = <?php echo json_encode($main_category); ?>;
    var productId = <?php echo intval($product_id); ?>;
    var mainCategoryId = '<?php echo esc_js($main_category_id); ?>';
    
    // Category selection
    $('#cpm-category-select').on('change', function() {
        var categoryId = $(this).val();
        var $countryGroup = $('#cpm-country-group');
        var $quantityGroup = $('#cpm-quantity-group');
        var $urlGroup = $('#cpm-url-group');
        var $pricingSection = $('#cpm-pricing-section');
        var $actionButtons = $('#cpm-action-buttons');
        var $categoryInfo = $('#cpm-category-info');
        
        // Reset downstream selections
        $('#cpm-country-select').html('<option value=""><?php echo esc_js(__('-- Select Country --', 'custom-product-manager')); ?></option>');
        $('#cpm-quantity-select').html('<option value=""><?php echo esc_js(__('-- Select Quantity --', 'custom-product-manager')); ?></option>');
        $('#cpm-current-price').text('$0.00');
        $('#cpm-selected-price-input').val('');
        $('#cpm-selected-price-id').val('');
        $('#cpm-delivery-time').hide().text('');
        
        $quantityGroup.hide();
        $urlGroup.hide();
        $pricingSection.hide();
        $actionButtons.hide();
        
        if (!categoryId) {
            $countryGroup.hide();
            $categoryInfo.hide();
            return;
        }
        
        // Get category data
        var categoryData = mainCategoryData.categories[categoryId];
        if (!categoryData || !categoryData.countries) {
            $countryGroup.hide();
            $categoryInfo.hide();
            return;
        }
        
        // Show info box
        var categoryName = $('#cpm-category-select option:selected').data('category-name') || categoryData.name;
        $categoryInfo.find('.cpm-info-text').text('<?php echo esc_js(__('We will deliver to your', 'custom-product-manager')); ?> ' + categoryName.toLowerCase() + '.');
        $categoryInfo.show();
        
        // Populate countries
        var $countrySelect = $('#cpm-country-select');
        $countrySelect.html('<option value=""><?php echo esc_js(__('-- Select Country --', 'custom-product-manager')); ?></option>');
        
        $.each(categoryData.countries, function(countryId, country) {
            $countrySelect.append('<option value="' + countryId + '" data-country-data=\'' + JSON.stringify(country) + '\'>' + country.name + '</option>');
        });
        
        $countryGroup.show();
    });
    
    // Country selection
    $('#cpm-country-select').on('change', function() {
        var countryId = $(this).val();
        var categoryId = $('#cpm-category-select').val();
        var $quantityGroup = $('#cpm-quantity-group');
        var $urlGroup = $('#cpm-url-group');
        var $pricingSection = $('#cpm-pricing-section');
        var $actionButtons = $('#cpm-action-buttons');
        
        // Reset downstream selections
        $('#cpm-quantity-select').html('<option value=""><?php echo esc_js(__('-- Select Quantity --', 'custom-product-manager')); ?></option>');
        $('#cpm-current-price').text('$0.00');
        $('#cpm-selected-price-input').val('');
        $('#cpm-selected-price-id').val('');
        $('#cpm-delivery-time').hide().text('');
        
        $urlGroup.hide();
        $pricingSection.hide();
        $actionButtons.hide();
        
        if (!countryId || !categoryId) {
            $quantityGroup.hide();
            return;
        }
        
        // Get country data
        var countryData = mainCategoryData.categories[categoryId].countries[countryId];
        if (!countryData || !countryData.quantities) {
            $quantityGroup.hide();
            return;
        }
        
        // Populate quantities
        var $quantitySelect = $('#cpm-quantity-select');
        $quantitySelect.html('<option value=""><?php echo esc_js(__('-- Select Quantity --', 'custom-product-manager')); ?></option>');
        
        $.each(countryData.quantities, function(qtyId, quantity) {
            var qtyText = quantity.quantity;
            // You can add delivery time here if stored in quantity data
            $quantitySelect.append('<option value="' + qtyId + '" data-quantity-data=\'' + JSON.stringify(quantity) + '\'>' + qtyText + '</option>');
        });
        
        $quantityGroup.show();
    });
    
    // Quantity selection
    $('#cpm-quantity-select').on('change', function() {
        var quantityId = $(this).val();
        var countryId = $('#cpm-country-select').val();
        var categoryId = $('#cpm-category-select').val();
        var $urlGroup = $('#cpm-url-group');
        var $pricingSection = $('#cpm-pricing-section');
        var $actionButtons = $('#cpm-action-buttons');
        
        // Reset
        $('#cpm-current-price').text('$0.00');
        $('#cpm-selected-price-input').val('');
        $('#cpm-selected-price-id').val('');
        $('#cpm-original-price').hide();
        $('#cpm-savings-badge').hide();
        $actionButtons.hide();
        
        if (!quantityId || !countryId || !categoryId) {
            $pricingSection.hide();
            $urlGroup.hide();
            return;
        }
        
        // Get quantity data
        var quantityData = mainCategoryData.categories[categoryId].countries[countryId].quantities[quantityId];
        if (!quantityData || !quantityData.prices) {
            $pricingSection.hide();
            $urlGroup.hide();
            return;
        }
        
        // Get first price (you can modify to show all prices or let user select)
        var prices = quantityData.prices;
        var firstPriceId = Object.keys(prices)[0];
        var firstPrice = prices[firstPriceId];
        
        if (firstPrice && firstPrice.price) {
            var price = parseFloat(firstPrice.price);
            var originalPrice = price * 1.33; // Example: 25% discount, adjust as needed
            var savings = originalPrice - price;
            
            $('#cpm-current-price').text('$' + price.toFixed(2));
            $('#cpm-selected-price-input').val(price);
            $('#cpm-selected-price-id').val(firstPriceId);
            
            // Show original price and savings if there's a discount
            if (savings > 0.01) {
                $('#cpm-original-price .cpm-strikethrough').text('$' + originalPrice.toFixed(2));
                $('#cpm-original-price').show();
                $('#cpm-savings-badge').text('<?php echo esc_js(__('Save', 'custom-product-manager')); ?> $' + savings.toFixed(2)).show();
            }
            
            $pricingSection.show();
            $urlGroup.show();
            $actionButtons.show();
        } else {
            $pricingSection.hide();
            $urlGroup.hide();
        }
    });
    
    // Sample URL button
    $('#cpm-sample-url-btn').on('click', function() {
        var categoryName = $('#cpm-category-select option:selected').data('category-name') || '';
        var sampleUrls = {
            'Facebook Page': 'https://www.facebook.com/yourpage',
            'Facebook Post': 'https://www.facebook.com/username/posts/123456789',
            'Instagram': 'https://www.instagram.com/username/',
            'Twitter': 'https://twitter.com/username/status/123456789'
        };
        
        var url = sampleUrls[categoryName] || 'https://www.facebook.com/yourpage';
        alert('<?php echo esc_js(__('Sample URL:', 'custom-product-manager')); ?>\n' + url);
    });
    
    // URL validation
    var urlValidationTimeout;
    var isValidUrl = false;
    var isUrlValidating = false;
    
    $('#cpm-page-url').on('blur', function() {
        validateUrl();
    });
    
    $('#cpm-page-url').on('input', function() {
        clearTimeout(urlValidationTimeout);
        var $input = $(this);
        var $message = $('#cpm-url-validation-message');
        
        // Clear previous validation state
        $input.removeClass('cpm-url-valid cpm-url-invalid');
        $message.hide().removeClass('cpm-url-success cpm-url-error');
        isValidUrl = false;
        
        var url = $input.val().trim();
        if (url === '') {
            return;
        }
        
        // Basic URL format validation
        var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
        if (!urlPattern.test(url) && !url.startsWith('http://') && !url.startsWith('https://')) {
            showUrlError('<?php echo esc_js(__('Please enter a valid URL format.', 'custom-product-manager')); ?>');
            return;
        }
        
        // Debounce validation - wait 1 second after user stops typing
        urlValidationTimeout = setTimeout(function() {
            validateUrl();
        }, 1000);
    });
    
    function validateUrl() {
        var url = $('#cpm-page-url').val().trim();
        var $input = $('#cpm-page-url');
        var $message = $('#cpm-url-validation-message');
        
        if (url === '') {
            $input.removeClass('cpm-url-valid cpm-url-invalid');
            $message.hide();
            isValidUrl = false;
            return;
        }
        
        // Basic URL format check
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            url = 'https://' + url;
        }
        
        isUrlValidating = true;
        $input.addClass('cpm-url-validating');
        $message.show().removeClass('cpm-url-success cpm-url-error').addClass('cpm-url-validating')
            .html('<?php echo esc_js(__('Validating URL...', 'custom-product-manager')); ?>');
        
        // Get selected platform from main category or category selection
        var selectedPlatform = '';
        var mainCategoryName = '<?php echo esc_js($main_category['name']); ?>';
        var categoryName = $('#cpm-category-select option:selected').data('category-name') || '';
        
        // Determine platform from main category name or category name
        if (mainCategoryName) {
            var nameLower = mainCategoryName.toLowerCase();
            if (nameLower.indexOf('instagram') !== -1) {
                selectedPlatform = 'instagram';
            } else if (nameLower.indexOf('facebook') !== -1 || nameLower.indexOf('fb') !== -1) {
                selectedPlatform = 'facebook';
            } else if (nameLower.indexOf('twitter') !== -1 || nameLower.indexOf('x ') !== -1) {
                selectedPlatform = 'twitter';
            } else if (nameLower.indexOf('youtube') !== -1) {
                selectedPlatform = 'youtube';
            } else if (nameLower.indexOf('tiktok') !== -1) {
                selectedPlatform = 'tiktok';
            }
        }
        
        // If not found in main category, check category name
        if (!selectedPlatform && categoryName) {
            var catNameLower = categoryName.toLowerCase();
            if (catNameLower.indexOf('instagram') !== -1) {
                selectedPlatform = 'instagram';
            } else if (catNameLower.indexOf('facebook') !== -1 || catNameLower.indexOf('fb') !== -1) {
                selectedPlatform = 'facebook';
            } else if (catNameLower.indexOf('twitter') !== -1 || catNameLower.indexOf('x ') !== -1) {
                selectedPlatform = 'twitter';
            } else if (catNameLower.indexOf('youtube') !== -1) {
                selectedPlatform = 'youtube';
            } else if (catNameLower.indexOf('tiktok') !== -1) {
                selectedPlatform = 'tiktok';
            }
        }
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'cpm_validate_url',
                url: url,
                platform: selectedPlatform,
                nonce: '<?php echo wp_create_nonce('cpm_nonce'); ?>'
            },
            success: function(response) {
                isUrlValidating = false;
                $input.removeClass('cpm-url-validating');
                
                if (response.success) {
                    isValidUrl = true;
                    $input.addClass('cpm-url-valid').removeClass('cpm-url-invalid');
                    $message.removeClass('cpm-url-validating cpm-url-error').addClass('cpm-url-success')
                        .html('✓ <?php echo esc_js(__('Post URL is valid and accessible', 'custom-product-manager')); ?>');
                } else {
                    isValidUrl = false;
                    $input.addClass('cpm-url-invalid').removeClass('cpm-url-valid');
                    $message.removeClass('cpm-url-validating cpm-url-success').addClass('cpm-url-error')
                        .html('✗ ' + (response.data.message || '<?php echo esc_js(__('Post not found or URL is not accessible', 'custom-product-manager')); ?>'));
                }
            },
            error: function() {
                isUrlValidating = false;
                isValidUrl = false;
                $input.removeClass('cpm-url-validating cpm-url-valid').addClass('cpm-url-invalid');
                $message.removeClass('cpm-url-validating cpm-url-success').addClass('cpm-url-error')
                    .html('✗ <?php echo esc_js(__('Error validating URL. Please try again.', 'custom-product-manager')); ?>');
            }
        });
    }
    
    function showUrlError(message) {
        var $input = $('#cpm-page-url');
        var $message = $('#cpm-url-validation-message');
        isValidUrl = false;
        $input.addClass('cpm-url-invalid').removeClass('cpm-url-valid');
        $message.show().removeClass('cpm-url-success cpm-url-validating').addClass('cpm-url-error')
            .html('✗ ' + message);
    }
    
    // Add to Cart button
    $('#cpm-add-to-cart-btn').on('click', function() {
        var pageUrl = $('#cpm-page-url').val();
        var formData = {
            product_id: productId,
            main_category_id: mainCategoryId,
            category_id: $('#cpm-category-select').val(),
            country_id: $('#cpm-country-select').val(),
            quantity_id: $('#cpm-quantity-select').val(),
            price: $('#cpm-selected-price-input').val(),
            price_id: $('#cpm-selected-price-id').val(),
            page_url: pageUrl,
            action: 'cpm_add_to_cart_custom',
            nonce: '<?php echo wp_create_nonce('cpm_nonce'); ?>'
        };
        
        // Validate
        if (!formData.category_id || !formData.country_id || !formData.quantity_id || !formData.price) {
            alert('<?php echo esc_js(__('Please select all required options.', 'custom-product-manager')); ?>');
            return;
        }
        
        if (!pageUrl || pageUrl.trim() === '') {
            alert('<?php echo esc_js(__('Please enter a page URL.', 'custom-product-manager')); ?>');
            return;
        }
        
        // Check if URL is being validated
        if (isUrlValidating) {
            alert('<?php echo esc_js(__('Please wait for URL validation to complete.', 'custom-product-manager')); ?>');
            return;
        }
        
        // Check if URL is valid
        if (!isValidUrl) {
            if (confirm('<?php echo esc_js(__('The post URL may not be accessible. Do you want to continue anyway?', 'custom-product-manager')); ?>')) {
                // User wants to proceed anyway
            } else {
                return;
            }
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="cpm-cart-icon">🛒</span> <?php echo esc_js(__('Adding...', 'custom-product-manager')); ?>');
        
        // Add to cart via AJAX
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('<?php echo esc_js(__('Added to cart!', 'custom-product-manager')); ?>');
                    // Update cart count if exists
                    if (typeof updateCartCount === 'function') {
                        updateCartCount();
                    }
                } else {
                    alert(response.data.message || '<?php echo esc_js(__('Error adding to cart.', 'custom-product-manager')); ?>');
                }
                $btn.prop('disabled', false).html('<span class="cpm-cart-icon">🛒</span> <?php echo esc_js(__('ADD TO CART', 'custom-product-manager')); ?>');
            },
            error: function() {
                alert('<?php echo esc_js(__('Error adding to cart.', 'custom-product-manager')); ?>');
                $btn.prop('disabled', false).html('<span class="cpm-cart-icon">🛒</span> <?php echo esc_js(__('ADD TO CART', 'custom-product-manager')); ?>');
            }
        });
    });
    
    // Buy Now button
    $('#cpm-buy-now-btn').on('click', function() {
        var pageUrl = $('#cpm-page-url').val();
        var formData = {
            product_id: productId,
            main_category_id: mainCategoryId,
            category_id: $('#cpm-category-select').val(),
            country_id: $('#cpm-country-select').val(),
            quantity_id: $('#cpm-quantity-select').val(),
            price: $('#cpm-selected-price-input').val(),
            price_id: $('#cpm-selected-price-id').val(),
            page_url: pageUrl,
            action: 'cpm_buy_now',
            nonce: '<?php echo wp_create_nonce('cpm_nonce'); ?>'
        };
        
        // Validate
        if (!formData.category_id || !formData.country_id || !formData.quantity_id || !formData.price) {
            alert('<?php echo esc_js(__('Please select all required options.', 'custom-product-manager')); ?>');
            return;
        }
        
        if (!pageUrl || pageUrl.trim() === '') {
            alert('<?php echo esc_js(__('Please enter a page URL.', 'custom-product-manager')); ?>');
            return;
        }
        
        // Check if URL is being validated
        if (isUrlValidating) {
            alert('<?php echo esc_js(__('Please wait for URL validation to complete.', 'custom-product-manager')); ?>');
            return;
        }
        
        // Check if URL is valid
        if (!isValidUrl) {
            if (confirm('<?php echo esc_js(__('The post URL may not be accessible. Do you want to continue anyway?', 'custom-product-manager')); ?>')) {
                // User wants to proceed anyway
            } else {
                return;
            }
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="cpm-bolt-icon">⚡</span> <?php echo esc_js(__('Processing...', 'custom-product-manager')); ?>');
        
        // Add to cart and redirect to checkout
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.checkout_url || '<?php echo home_url('/checkout'); ?>';
                } else {
                    alert(response.data.message || '<?php echo esc_js(__('Error processing order.', 'custom-product-manager')); ?>');
                    $btn.prop('disabled', false).html('<span class="cpm-bolt-icon">⚡</span> <?php echo esc_js(__('BUY NOW', 'custom-product-manager')); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Error processing order.', 'custom-product-manager')); ?>');
                $btn.prop('disabled', false).html('<span class="cpm-bolt-icon">⚡</span> <?php echo esc_js(__('BUY NOW', 'custom-product-manager')); ?>');
            }
        });
    });
});
</script>

<!-- Reviews Section -->
<div class="cpm-reviews-section">
    <?php
    // Get product ID for reviews
    $review_product_id = $product_id;
    
    // Get approved reviews for this product
    global $wpdb;
    
    // Pagination
    $reviews_per_page = 5;
    $current_page = isset($_GET['review_page']) ? max(1, intval($_GET['review_page'])) : 1;
    $offset = ($current_page - 1) * $reviews_per_page;
    
    // Get total count
    $total_reviews = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM " . CPM_Database::get_table('reviews') . " 
        WHERE product_id = %d AND status = 'approved'",
        $review_product_id
    ));
    
    // Get reviews with pagination
    $reviews = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . CPM_Database::get_table('reviews') . " 
        WHERE product_id = %d AND status = 'approved' 
        ORDER BY created_at DESC 
        LIMIT %d OFFSET %d",
        $review_product_id,
        $reviews_per_page,
        $offset
    ));
    
    // Calculate average rating
    $avg_rating = $wpdb->get_var($wpdb->prepare(
        "SELECT AVG(rating) FROM " . CPM_Database::get_table('reviews') . " 
        WHERE product_id = %d AND status = 'approved'",
        $review_product_id
    ));
    $avg_rating = $avg_rating ? round($avg_rating, 1) : 0;
    
    // Calculate total pages
    $total_pages = ceil($total_reviews / $reviews_per_page);
    ?>
    
    <!-- Leave A Review Section -->
    <div class="cpm-review-form-section">
        <h2 class="cpm-review-section-title"><?php _e('Review On Your Experience', 'custom-product-manager'); ?></h2>
        <p class="cpm-review-intro"><?php _e('Your feedback is extremely valuable to us. We would love to hear your ideas on how we can do better. Please use this \'Leave a review\' section below to share your thoughts on our services.', 'custom-product-manager'); ?></p>
        
        <div class="cpm-review-form-wrapper">
            <h3 class="cpm-review-form-title"><?php _e('Leave A Review', 'custom-product-manager'); ?></h3>
            <form id="cpm-review-form" class="cpm-review-form">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($review_product_id); ?>" />
                <?php wp_nonce_field('cpm_submit_review', 'cpm_review_nonce'); ?>
                
                <div class="cpm-form-row">
                    <div class="cpm-form-group">
                        <label for="reviewer_name"><?php _e('Your Name', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="text" id="reviewer_name" name="reviewer_name" required />
                    </div>
                    
                    <div class="cpm-form-group">
                        <label for="reviewer_email"><?php _e('Your Email', 'custom-product-manager'); ?> <span class="required">*</span></label>
                        <input type="email" id="reviewer_email" name="reviewer_email" required />
                    </div>
                </div>
                
                <div class="cpm-form-group">
                    <label for="review_text"><?php _e('Your Review', 'custom-product-manager'); ?> <span class="required">*</span></label>
                    <textarea id="review_text" name="review_text" rows="5" placeholder="<?php _e('Type your review', 'custom-product-manager'); ?>" required></textarea>
                </div>
                
                <div class="cpm-form-group">
                    <label><?php _e('Your Rating', 'custom-product-manager'); ?> <span class="required">*</span></label>
                    <div class="cpm-star-rating-input">
                        <input type="hidden" name="rating" id="review_rating" value="5" required />
                        <div class="cpm-stars-select">
                            <span class="cpm-star" data-rating="1">★</span>
                            <span class="cpm-star" data-rating="2">★</span>
                            <span class="cpm-star" data-rating="3">★</span>
                            <span class="cpm-star" data-rating="4">★</span>
                            <span class="cpm-star active" data-rating="5">★</span>
                        </div>
                    </div>
                </div>
                
                <div class="cpm-form-group">
                    <button type="submit" class="cpm-submit-review-btn"><?php _e('Post Your Review', 'custom-product-manager'); ?></button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Read Reviews Section -->
    <div class="cpm-reviews-display-section">
        <h2 class="cpm-reviews-section-title"><?php _e('Read our customer reviews', 'custom-product-manager'); ?></h2>
        
        <?php if ($total_reviews > 0) : ?>
            <div class="cpm-overall-rating">
                <div class="cpm-rating-stars-large">
                    <?php
                    $full_stars = floor($avg_rating);
                    $has_half = ($avg_rating - $full_stars) >= 0.5;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $full_stars) {
                            echo '<span class="cpm-star-full">★</span>';
                        } elseif ($i == $full_stars + 1 && $has_half) {
                            echo '<span class="cpm-star-half">★</span>';
                        } else {
                            echo '<span class="cpm-star-empty">★</span>';
                        }
                    }
                    ?>
                </div>
                <span class="cpm-rating-value"><?php echo esc_html($avg_rating); ?>/5</span>
                <span class="cpm-review-count"><?php echo esc_html($total_reviews); ?> <?php _e('Verified Customer reviews', 'custom-product-manager'); ?></span>
            </div>
            
            <div class="cpm-reviews-list">
                <?php foreach ($reviews as $review) : ?>
                    <div class="cpm-review-item">
                        <div class="cpm-review-header">
                            <div class="cpm-reviewer-info">
                                <strong class="cpm-reviewer-name"><?php echo esc_html($review->reviewer_name); ?></strong>
                                <?php if ($review->verified_purchase) : ?>
                                    <span class="cpm-verified-badge">
                                        <span class="cpm-check-icon">✓</span> <?php _e('Verified Purchase', 'custom-product-manager'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="cpm-review-meta">
                                <span class="cpm-review-date"><?php echo esc_html(date_i18n('jS M, Y', strtotime($review->created_at))); ?></span>
                            </div>
                        </div>
                        <div class="cpm-review-rating">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $review->rating) {
                                    echo '<span class="cpm-star-filled">★</span>';
                                } else {
                                    echo '<span class="cpm-star-empty">★</span>';
                                }
                            }
                            ?>
                        </div>
                        <div class="cpm-review-text">
                            <?php echo nl2br(esc_html($review->review_text)); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1) : ?>
                <div class="cpm-reviews-pagination">
                    <?php
                    $base_url = remove_query_arg('review_page');
                    $base_url = add_query_arg(array(
                        'cpm_main_category' => 1,
                        'product_id' => $product_id,
                        'main_cat' => $main_category_id
                    ), $base_url);
                    
                    // Previous button
                    if ($current_page > 1) :
                        $prev_url = add_query_arg('review_page', $current_page - 1, $base_url);
                    ?>
                        <a href="<?php echo esc_url($prev_url); ?>" class="cpm-pagination-btn"><?php _e('Previous', 'custom-product-manager'); ?></a>
                    <?php endif; ?>
                    
                    <?php
                    // Page numbers
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) :
                        $first_url = add_query_arg('review_page', 1, $base_url);
                    ?>
                        <a href="<?php echo esc_url($first_url); ?>" class="cpm-pagination-number">1</a>
                        <?php if ($start_page > 2) : ?>
                            <span class="cpm-pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++) : 
                        $page_url = add_query_arg('review_page', $i, $base_url);
                        $is_current = ($i == $current_page);
                    ?>
                        <a href="<?php echo esc_url($page_url); ?>" 
                           class="cpm-pagination-number <?php echo $is_current ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php
                    if ($end_page < $total_pages) :
                        $last_url = add_query_arg('review_page', $total_pages, $base_url);
                    ?>
                        <?php if ($end_page < $total_pages - 1) : ?>
                            <span class="cpm-pagination-ellipsis">...</span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($last_url); ?>" class="cpm-pagination-number"><?php echo $total_pages; ?></a>
                    <?php endif; ?>
                    
                    <?php
                    // Next button
                    if ($current_page < $total_pages) :
                        $next_url = add_query_arg('review_page', $current_page + 1, $base_url);
                    ?>
                        <a href="<?php echo esc_url($next_url); ?>" class="cpm-pagination-btn"><?php _e('Next', 'custom-product-manager'); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <p class="cpm-no-reviews"><?php _e('No reviews yet. Be the first to review this product!', 'custom-product-manager'); ?></p>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Star rating selection
    $('.cpm-stars-select .cpm-star').on('click', function() {
        var rating = $(this).data('rating');
        $('#review_rating').val(rating);
        
        $('.cpm-stars-select .cpm-star').removeClass('active');
        $('.cpm-stars-select .cpm-star').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).addClass('active');
            }
        });
    });
    
    // Hover effect on stars
    $('.cpm-stars-select .cpm-star').on('mouseenter', function() {
        var rating = $(this).data('rating');
        $('.cpm-stars-select .cpm-star').removeClass('hover');
        $('.cpm-stars-select .cpm-star').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).addClass('hover');
            }
        });
    }).on('mouseleave', function() {
        $('.cpm-stars-select .cpm-star').removeClass('hover');
    });
    
    // Submit review form
    $('#cpm-review-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'cpm_submit_review',
            product_id: $('input[name="product_id"]').val(),
            reviewer_name: $('#reviewer_name').val(),
            reviewer_email: $('#reviewer_email').val(),
            review_text: $('#review_text').val(),
            rating: $('#review_rating').val(),
            nonce: $('input[name="cpm_review_nonce"]').val()
        };
        
        if (!formData.reviewer_name || !formData.reviewer_email || !formData.review_text || !formData.rating) {
            alert('<?php echo esc_js(__('Please fill in all required fields.', 'custom-product-manager')); ?>');
            return;
        }
        
        var $btn = $('.cpm-submit-review-btn');
        $btn.prop('disabled', true).text('<?php echo esc_js(__('Submitting...', 'custom-product-manager')); ?>');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('<?php echo esc_js(__('Thank you for your review! It will be reviewed by our team before being published.', 'custom-product-manager')); ?>');
                    $('#cpm-review-form')[0].reset();
                    $('#review_rating').val(5);
                    $('.cpm-stars-select .cpm-star').removeClass('active');
                    $('.cpm-stars-select .cpm-star[data-rating="5"]').addClass('active');
                    // Optionally reload reviews
                    // location.reload();
                } else {
                    alert(response.data.message || '<?php echo esc_js(__('Error submitting review. Please try again.', 'custom-product-manager')); ?>');
                }
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Post Your Review', 'custom-product-manager')); ?>');
            },
            error: function() {
                alert('<?php echo esc_js(__('Error submitting review. Please try again.', 'custom-product-manager')); ?>');
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Post Your Review', 'custom-product-manager')); ?>');
            }
        });
    });
});
</script>


