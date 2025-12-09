jQuery(document).ready(function($) {
    
    // Handle variation selection and price calculation
    $('.cpm-product-form').each(function() {
        var $form = $(this);
        var categoryId = $form.data('category-id');
        var $priceDisplay = $form.find('.price-value');
        var $addToCartBtn = $form.find('.cpm-add-to-cart-btn');
        var $variationIdInput = $form.find('.variation-id');
        
        // Watch for changes in variation fields
        $form.on('change', '.cpm-variation-select, .cpm-variation-number, .cpm-variation-text', function() {
            updatePrice($form, categoryId, $priceDisplay, $addToCartBtn, $variationIdInput);
        });
        
        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            
            var variationId = $variationIdInput.val();
            var quantity = $form.find('.cpm-quantity').val() || 1;
            
            if (!variationId) {
                alert('Please select all options to add to cart.');
                return;
            }
            
            // Add to cart via AJAX
            $.ajax({
                url: cpmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'cpm_add_to_cart',
                    variation_id: variationId,
                    quantity: quantity,
                    nonce: cpmAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message || 'Added to cart!');
                        updateCartCount();
                        
                        // Optionally redirect to cart
                        // window.location.href = cartPageUrl;
                    } else {
                        alert(response.data.message || 'Error adding to cart.');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
    
    function updatePrice($form, categoryId, $priceDisplay, $addToCartBtn, $variationIdInput) {
        var selectedValues = {};
        var allFilled = true;
        
        // Collect all variation values
        $form.find('[name^="variation["]').each(function() {
            var name = $(this).attr('name');
            var matches = name.match(/variation\[(\d+)\]/);
            if (matches) {
                var subcatId = matches[1];
                var value = $(this).val();
                if (value) {
                    selectedValues[subcatId] = value;
                } else {
                    allFilled = false;
                }
            }
        });
        
        if (!allFilled || Object.keys(selectedValues).length === 0) {
            $priceDisplay.text('Select options to see price');
            $addToCartBtn.prop('disabled', true);
            $variationIdInput.val('');
            return;
        }
        
        // Get price via AJAX
        $.ajax({
            url: cpmAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'cpm_get_variation_price',
                category_id: categoryId,
                selected_values: selectedValues,
                nonce: cpmAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $priceDisplay.text('$' + parseFloat(response.data.price).toFixed(2));
                    $addToCartBtn.prop('disabled', false);
                    $variationIdInput.val(response.data.variation_id);
                } else {
                    $priceDisplay.text('No matching variation found');
                    $addToCartBtn.prop('disabled', true);
                    $variationIdInput.val('');
                }
            },
            error: function() {
                $priceDisplay.text('Error loading price');
                $addToCartBtn.prop('disabled', true);
            }
        });
    }
    
    // Update cart count
    function updateCartCount() {
        $.ajax({
            url: cpmAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'cpm_get_cart_count',
                nonce: cpmAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update cart count in header/menu if exists
                    $('.cpm-cart-count').text(response.data.count);
                }
            }
        });
    }
    
    // Update quantity in cart
    $(document).on('change', '.cpm-update-quantity', function() {
        var itemId = $(this).data('item-id');
        var quantity = $(this).val();
        var $row = $(this).closest('tr');
        var $button = $(this);
        
        if (quantity <= 0) {
            alert('Quantity must be at least 1');
            $button.val(1);
            return;
        }
        
        $.ajax({
            url: cpmAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'cpm_update_cart_item',
                item_id: itemId,
                quantity: quantity,
                nonce: cpmAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $row.find('.item-subtotal').text(response.data.subtotal);
                    $('.cart-total').text(response.data.cart_total);
                } else {
                    alert(response.data.message || 'Error updating quantity');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Remove item from cart
    $(document).on('click', '.cpm-remove-item', function() {
        var itemId = $(this).data('item-id');
        var $row = $(this).closest('tr');
        var $button = $(this);
        
        if (!confirm('Remove this item from cart?')) {
            return;
        }
        
        $.ajax({
            url: cpmAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'cpm_remove_cart_item',
                item_id: itemId,
                nonce: cpmAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(function() {
                        $(this).remove();
                        $('.cart-total').text(response.data.cart_total);
                        updateCartCount();
                        
                        // Check if cart is empty
                        if ($('.cpm-cart-table tbody tr').length <= 1) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.data.message || 'Error removing item');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Initialize cart count on page load
    updateCartCount();
});

