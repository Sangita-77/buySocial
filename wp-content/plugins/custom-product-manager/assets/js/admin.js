jQuery(document).ready(function($) {
    
    var categoryCounter = Date.now(); // Use timestamp for unique IDs
    
    // Add new category
    $('#add-category-full').on('click', function(e) {
        e.preventDefault();
        categoryCounter++;
        var newCategoryId = 'new_' + categoryCounter;
        
        // Create new category HTML
        var categoryHtml = createNewCategoryHTML(newCategoryId);
        $('#categories-list').append(categoryHtml);
        
        // Scroll to the new category
        $('html, body').animate({
            scrollTop: $('#categories-list .category-wrapper').last().offset().top - 100
        }, 500);
    });
    
    // Delete category
    $(document).on('click', '.delete-category-full', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this category? All sub-categories and variations will be deleted.')) {
            $(this).closest('.category-wrapper').fadeOut(300, function() {
                $(this).remove();
                if ($('#categories-list .category-wrapper').length === 0) {
                    $('#categories-list').html('');
                }
            });
        }
    });
    
    // Add subcategory
    $(document).on('click', '.add-subcat-full', function(e) {
        e.preventDefault();
        var $categoryWrapper = $(this).closest('.category-wrapper');
        var categoryIdAttr = $categoryWrapper.find('input[type="hidden"][name*="[id]"]').attr('name');
        var categoryMatch = categoryIdAttr.match(/categories\[([^\]]+)\]/);
        var categoryKey = categoryMatch ? categoryMatch[1] : 'new_' + categoryCounter;
        
        var subcatCounter = Date.now();
        var newSubcatId = 'new_' + subcatCounter;
        
        var subcatHtml = createNewSubcategoryHTML(categoryKey, newSubcatId);
        $(this).siblings('.subcategories-list').append(subcatHtml);
    });
    
    // Delete subcategory
    $(document).on('click', '.delete-subcat-full', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this sub-category?')) {
            $(this).closest('.subcategory-full-item').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
    
    // Handle subcategory type change
    $(document).on('change', '.subcat-type-select', function() {
        var $subcatItem = $(this).closest('.subcategory-full-item');
        var type = $(this).val();
        var $optionsDiv = $subcatItem.find('.subcat-options');
        
        if (type === 'select') {
            if ($optionsDiv.length === 0) {
                var categoryIdAttr = $subcatItem.find('input[type="hidden"][name*="[id]"]').attr('name');
                var categoryMatch = categoryIdAttr.match(/categories\[([^\]]+)\]/);
                var categoryKey = categoryMatch ? categoryMatch[1] : 'new_0';
                var subcatIdAttr = $subcatItem.find('input[type="hidden"][name*="[id]"]').attr('name');
                var subcatMatch = subcatIdAttr.match(/subcategories\[([^\]]+)\]/);
                var subcatKey = subcatMatch ? subcatMatch[1] : 'new_0';
                
                var optionsHtml = '<div class="subcat-options">';
                optionsHtml += '<label><strong>Options (e.g., Facebook Page, Facebook Post):</strong></label>';
                optionsHtml += '<div class="options-list"></div>';
                optionsHtml += '<button type="button" class="button add-option-full">Add Option</button>';
                optionsHtml += '</div>';
                
                $(this).closest('.subcat-header').after(optionsHtml);
            } else {
                $optionsDiv.show();
            }
        } else {
            $optionsDiv.hide();
        }
    });
    
    // Add option to subcategory
    $(document).on('click', '.add-option-full', function(e) {
        e.preventDefault();
        var $subcatItem = $(this).closest('.subcategory-full-item');
        var categoryIdAttr = $subcatItem.find('input[type="hidden"][name*="[id]"]').attr('name');
        var categoryMatch = categoryIdAttr.match(/categories\[([^\]]+)\]/);
        var categoryKey = categoryMatch ? categoryMatch[1] : 'new_0';
        var subcatIdAttr = $subcatItem.find('input[type="hidden"][name*="[id]"]').attr('name');
        var subcatMatch = subcatIdAttr.match(/subcategories\[([^\]]+)\]/);
        var subcatKey = subcatMatch ? subcatMatch[1] : 'new_0';
        
        var optionHtml = '<div class="option-row">';
        optionHtml += '<input type="text" name="categories[' + categoryKey + '][subcategories][' + subcatKey + '][options][]" placeholder="Enter option (e.g., Facebook Page, Facebook Post)" class="regular-text" />';
        optionHtml += '<button type="button" class="button delete-option-full">Remove</button>';
        optionHtml += '</div>';
        
        var $optionsList = $(this).siblings('.options-list');
        if ($optionsList.length === 0) {
            $optionsList = $(this).parent().find('.options-list');
        }
        $optionsList.append(optionHtml);
        
        // Focus on the new input for better UX
        $optionsList.find('.option-row').last().find('input').focus();
    });
    
    // Delete option
    $(document).on('click', '.delete-option-full', function(e) {
        e.preventDefault();
        $(this).closest('.option-row').fadeOut(200, function() {
            $(this).remove();
        });
    });
    
    // Add variation
    $(document).on('click', '.add-variation-full', function(e) {
        e.preventDefault();
        var $categoryWrapper = $(this).closest('.category-wrapper');
        var categoryIdAttr = $categoryWrapper.find('input[type="hidden"][name*="[id]"]').attr('name');
        var categoryMatch = categoryIdAttr.match(/categories\[([^\]]+)\]/);
        var categoryKey = categoryMatch ? categoryMatch[1] : 'new_0';
        
        var varCounter = Date.now();
        var newVarId = 'new_' + varCounter;
        
        // Collect subcategories for this category
        var subcategories = [];
        $categoryWrapper.find('.subcategory-full-item').each(function() {
            var subcatIdAttr = $(this).find('input[type="hidden"][name*="[id]"]').attr('name');
            var subcatMatch = subcatIdAttr ? subcatIdAttr.match(/subcategories\[([^\]]+)\]/) : null;
            var subcatKey = subcatMatch ? subcatMatch[1] : 'new_0';
            var subcatName = $(this).find('input[name*="[name]"]').val() || 'Sub-Category';
            var subcatType = $(this).find('select[name*="[type]"]').val() || 'select';
            
            var options = [];
            if (subcatType === 'select') {
                $(this).find('input[name*="[options]"]').each(function() {
                    if ($(this).val()) {
                        options.push($(this).val());
                    }
                });
            }
            
            subcategories.push({
                key: subcatKey,
                name: subcatName,
                type: subcatType,
                options: options
            });
        });
        
        if (subcategories.length === 0) {
            alert('Please add sub-categories first before adding variations.');
            return;
        }
        
        var variationHtml = createNewVariationHTML(categoryKey, newVarId, subcategories);
        $(this).siblings('.variations-list').append(variationHtml);
    });
    
    // Delete variation
    $(document).on('click', '.delete-variation-full', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this variation?')) {
            $(this).closest('.variation-full-item').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
    
    // Create new category HTML
    function createNewCategoryHTML(categoryId) {
        var html = '<div class="category-wrapper" data-category-id="' + categoryId + '">';
        html += '<div class="category-full-form">';
        html += '<div class="category-header-section">';
        html += '<h3>';
        html += '<input type="text" name="categories[' + categoryId + '][name]" placeholder="Category Name (e.g., Buy Facebook Likes)" class="regular-text" required />';
        html += '<input type="hidden" name="categories[' + categoryId + '][id]" value="0" />';
        html += '</h3>';
        html += '<button type="button" class="button delete-category-full">Delete Category</button>';
        html += '</div>';
        html += '<div class="category-description-section">';
        html += '<textarea name="categories[' + categoryId + '][description]" rows="2" placeholder="Category Description (optional)" class="large-text"></textarea>';
        html += '</div>';
        html += '<div class="subcategories-section">';
        html += '<h4>Sub-Categories</h4>';
        html += '<div class="subcategories-list"></div>';
        html += '<button type="button" class="button add-subcat-full">Add Sub-Category</button>';
        html += '</div>';
        html += '<div class="variations-section">';
        html += '<h4>Variations & Prices</h4>';
        html += '<div class="variations-list"></div>';
        html += '<button type="button" class="button add-variation-full">Add Variation/Price</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        return html;
    }
    
    // Create new subcategory HTML
    function createNewSubcategoryHTML(categoryKey, subcatId) {
        var html = '<div class="subcategory-full-item" data-subcat-id="' + subcatId + '">';
        html += '<div class="subcat-header">';
        html += '<input type="text" name="categories[' + categoryKey + '][subcategories][' + subcatId + '][name]" placeholder="Sub-Category Name (e.g., Select Facebook Like Type)" class="regular-text" />';
        html += '<input type="hidden" name="categories[' + categoryKey + '][subcategories][' + subcatId + '][id]" value="0" />';
        html += '<select name="categories[' + categoryKey + '][subcategories][' + subcatId + '][type]" class="subcat-type-select">';
        html += '<option value="select" selected>Dropdown</option>';
        html += '<option value="number">Number</option>';
        html += '<option value="text">Text</option>';
        html += '</select>';
        html += '<button type="button" class="button delete-subcat-full">Delete</button>';
        html += '</div>';
        // Add options section by default for dropdown type
        html += '<div class="subcat-options">';
        html += '<label><strong>Options (e.g., Facebook Page, Facebook Post):</strong></label>';
        html += '<div class="options-list"></div>';
        html += '<button type="button" class="button add-option-full">Add Option</button>';
        html += '</div>';
        html += '</div>';
        return html;
    }
    
    // Create new variation HTML
    function createNewVariationHTML(categoryKey, varId, subcategories) {
        var html = '<div class="variation-full-item" data-variation-id="' + varId + '">';
        html += '<input type="hidden" name="categories[' + categoryKey + '][variations][' + varId + '][id]" value="0" />';
        html += '<div class="variation-fields-row">';
        
        subcategories.forEach(function(subcat) {
            html += '<div class="variation-field">';
            html += '<label>' + subcat.name + '</label>';
            
            if (subcat.type === 'select') {
                html += '<select name="categories[' + categoryKey + '][variations][' + varId + '][data][' + subcat.key + ']">';
                html += '<option value="">Select...</option>';
                subcat.options.forEach(function(opt) {
                    html += '<option value="' + opt + '">' + opt + '</option>';
                });
                html += '</select>';
            } else if (subcat.type === 'number') {
                html += '<input type="number" name="categories[' + categoryKey + '][variations][' + varId + '][data][' + subcat.key + ']" />';
            } else {
                html += '<input type="text" name="categories[' + categoryKey + '][variations][' + varId + '][data][' + subcat.key + ']" />';
            }
            html += '</div>';
        });
        
        html += '<div class="variation-field">';
        html += '<label>Price</label>';
        html += '<input type="number" name="categories[' + categoryKey + '][variations][' + varId + '][price]" step="0.01" min="0" required />';
        html += '</div>';
        html += '<div class="variation-field">';
        html += '<button type="button" class="button delete-variation-full">Delete</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        return html;
    }
    
    // ============================================
    // New Hierarchical Form Structure
    // ============================================
    
    // Initialize hierarchicalCounter if it doesn't exist, or use existing one
    if (typeof hierarchicalCounter === 'undefined') {
        var hierarchicalCounter = {
            mainCategory: 0,
            category: 0,
            country: 0,
            quantity: 0,
            price: 0
        };
    }
    
    // Make it global so it can be accessed from product-form.php
    window.hierarchicalCounter = hierarchicalCounter;
    
    console.log('CPM: Initialized hierarchicalCounter:', hierarchicalCounter);
    
    // Add Main Category button
    $(document).on('click', '#add-main-category-btn', function(e) {
        e.preventDefault();
        hierarchicalCounter.mainCategory++;
        var mainCategoryId = 'maincat_' + hierarchicalCounter.mainCategory;
        
        var mainCategoryHtml = '<div class="cpm-main-category-item" data-main-category-id="' + mainCategoryId + '" style="margin: 15px 0; padding: 15px; border: 2px solid #0073aa; background: #f0f8ff; border-radius: 4px;">';
        mainCategoryHtml += '<div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">';
        mainCategoryHtml += '<input type="text" name="main_categories[' + mainCategoryId + '][name]" placeholder="Enter Main Category Name" class="regular-text cpm-main-category-name" required />';
        mainCategoryHtml += '<button type="button" class="button cpm-add-category-btn">Add Category</button>';
        mainCategoryHtml += '<button type="button" class="button cpm-delete-main-category-btn" style="background: #dc3232; color: #fff;">Delete</button>';
        mainCategoryHtml += '</div>';
        mainCategoryHtml += '<div class="cpm-main-category-content"></div>';
        mainCategoryHtml += '</div>';
        
        $('#cpm-main-categories-wrapper').append(mainCategoryHtml);
    });
    
    // Add Category button (against main category)
    $(document).on('click', '.cpm-add-category-btn', function(e) {
        e.preventDefault();
        var $mainCategoryItem = $(this).closest('.cpm-main-category-item');
        var mainCategoryId = $mainCategoryItem.data('main-category-id');
        
        hierarchicalCounter.category++;
        var categoryId = 'cat_' + hierarchicalCounter.category;
        
        var categoryHtml = '<div class="cpm-category-item" data-category-id="' + categoryId + '" data-main-category-id="' + mainCategoryId + '" style="margin: 10px 0 10px 30px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px;">';
        categoryHtml += '<div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">';
        categoryHtml += '<input type="text" name="main_categories[' + mainCategoryId + '][categories][' + categoryId + '][name]" placeholder="Enter Category Name" class="regular-text cpm-category-name" required />';
        categoryHtml += '<button type="button" class="button cpm-add-country-btn">Add Country</button>';
        categoryHtml += '<button type="button" class="button cpm-delete-category-btn" style="background: #dc3232; color: #fff;">Delete</button>';
        categoryHtml += '</div>';
        categoryHtml += '<div class="cpm-category-content"></div>';
        categoryHtml += '</div>';
        
        var $categoriesContainer = $mainCategoryItem.find('.cpm-categories-container');
        if ($categoriesContainer.length === 0) {
            $categoriesContainer = $('<div class="cpm-categories-container" style="margin-top: 10px;"></div>');
            $mainCategoryItem.find('.cpm-main-category-content').append($categoriesContainer);
        }
        $categoriesContainer.append(categoryHtml);
    });
    
    // Add Country button (against category - directly, no options)
    $(document).on('click', '.cpm-add-country-btn', function(e) {
        e.preventDefault();
        
        // Ensure hierarchicalCounter exists
        if (typeof hierarchicalCounter === 'undefined' || !hierarchicalCounter) {
            hierarchicalCounter = window.hierarchicalCounter || {
                mainCategory: 0,
                category: 0,
                country: 0,
                quantity: 0,
                price: 0
            };
        }
        
        hierarchicalCounter.country++;
        var countryId = 'country_' + hierarchicalCounter.country;
        var $categoryItem = $(this).closest('.cpm-category-item');
        var categoryId = $categoryItem.data('category-id');
        var $mainCategoryItem = $categoryItem.closest('.cpm-main-category-item');
        var mainCategoryId = $mainCategoryItem.data('main-category-id');
        
        console.log('CPM: Adding new country with ID:', countryId, 'Counter value:', hierarchicalCounter.country);
        
        var countryHtml = '<div class="cpm-country-item" data-country-id="' + countryId + '" style="margin: 10px 0 10px 30px; padding: 10px; border: 1px solid #ccc; background: #fff; border-radius: 3px;">';
        countryHtml += '<div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">';
        countryHtml += '<input type="text" name="main_categories[' + mainCategoryId + '][categories][' + categoryId + '][countries][' + countryId + '][name]" placeholder="Enter Country Name" class="regular-text" required />';
        countryHtml += '<button type="button" class="button cpm-add-quantity-btn">Add Quantity</button>';
        countryHtml += '<button type="button" class="button cpm-delete-country-btn" style="background: #dc3232; color: #fff;">Delete</button>';
        countryHtml += '</div>';
        countryHtml += '<div class="cpm-country-content"></div>';
        countryHtml += '</div>';
        
        var $countriesContainer = $categoryItem.find('.cpm-countries-container');
        if ($countriesContainer.length === 0) {
            $countriesContainer = $('<div class="cpm-countries-container" style="margin-top: 10px;"></div>');
            $categoryItem.find('.cpm-category-content').append($countriesContainer);
        }
        $countriesContainer.append(countryHtml);
    });
    
    // Add Quantity button (against country)
    $(document).on('click', '.cpm-add-quantity-btn', function(e) {
        e.preventDefault();
        
        // Ensure hierarchicalCounter exists
        if (typeof hierarchicalCounter === 'undefined' || !hierarchicalCounter) {
            hierarchicalCounter = window.hierarchicalCounter || {
                mainCategory: 0,
                category: 0,
                country: 0,
                quantity: 0,
                price: 0
            };
        }
        
        hierarchicalCounter.quantity++;
        var quantityId = 'qty_' + hierarchicalCounter.quantity;
        var $countryItem = $(this).closest('.cpm-country-item');
        var countryId = $countryItem.data('country-id');
        var $categoryItem = $countryItem.closest('.cpm-category-item');
        var categoryId = $categoryItem.data('category-id');
        var $mainCategoryItem = $categoryItem.closest('.cpm-main-category-item');
        var mainCategoryId = $mainCategoryItem.data('main-category-id');
        
        console.log('CPM: Adding new quantity with ID:', quantityId, 'Counter value:', hierarchicalCounter.quantity);
        
        var quantityHtml = '<div class="cpm-quantity-item" data-quantity-id="' + quantityId + '" style="margin: 10px 0 10px 30px; padding: 10px; border: 1px solid #bbb; background: #f5f5f5; border-radius: 3px;">';
        quantityHtml += '<div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">';
        quantityHtml += '<input type="text" name="main_categories[' + mainCategoryId + '][categories][' + categoryId + '][countries][' + countryId + '][quantities][' + quantityId + '][quantity]" placeholder="Enter Quantity (e.g., 1000, 5000, 10000)" class="regular-text" required />';
        quantityHtml += '<button type="button" class="button cpm-add-price-btn">Add Price</button>';
        quantityHtml += '<button type="button" class="button cpm-delete-quantity-btn" style="background: #dc3232; color: #fff;">Delete</button>';
        quantityHtml += '</div>';
        quantityHtml += '<div class="cpm-quantity-content"></div>';
        quantityHtml += '</div>';
        
        var $quantitiesContainer = $countryItem.find('.cpm-quantities-container');
        if ($quantitiesContainer.length === 0) {
            $quantitiesContainer = $('<div class="cpm-quantities-container" style="margin-top: 10px;"></div>');
            $countryItem.find('.cpm-country-content').append($quantitiesContainer);
        }
        $quantitiesContainer.append(quantityHtml);
    });
    
    // Add Price button (against quantity)
    $(document).on('click', '.cpm-add-price-btn', function(e) {
        e.preventDefault();
        
        // Ensure hierarchicalCounter exists
        if (typeof hierarchicalCounter === 'undefined' || !hierarchicalCounter) {
            hierarchicalCounter = window.hierarchicalCounter || {
                mainCategory: 0,
                category: 0,
                country: 0,
                quantity: 0,
                price: 0
            };
        }
        
        hierarchicalCounter.price++;
        var priceId = 'price_' + hierarchicalCounter.price;
        var $quantityItem = $(this).closest('.cpm-quantity-item');
        var quantityId = $quantityItem.data('quantity-id');
        var $countryItem = $quantityItem.closest('.cpm-country-item');
        var countryId = $countryItem.data('country-id');
        var $categoryItem = $countryItem.closest('.cpm-category-item');
        var categoryId = $categoryItem.data('category-id');
        var $mainCategoryItem = $categoryItem.closest('.cpm-main-category-item');
        var mainCategoryId = $mainCategoryItem.data('main-category-id');
        
        console.log('CPM: Adding new price with ID:', priceId, 'Counter value:', hierarchicalCounter.price);
        
        var priceHtml = '<div class="cpm-price-item" data-price-id="' + priceId + '" style="margin: 10px 0 10px 30px; padding: 10px; border: 1px solid #aaa; background: #fff; border-radius: 3px;">';
        priceHtml += '<div style="display: flex; gap: 10px; align-items: center;">';
        priceHtml += '<input type="number" name="main_categories[' + mainCategoryId + '][categories][' + categoryId + '][countries][' + countryId + '][quantities][' + quantityId + '][prices][' + priceId + '][price]" placeholder="Enter Price" class="regular-text" step="0.01" min="0" required />';
        priceHtml += '<button type="button" class="button cpm-delete-price-btn" style="background: #dc3232; color: #fff;">Delete</button>';
        priceHtml += '</div>';
        priceHtml += '</div>';
        
        var $pricesContainer = $quantityItem.find('.cpm-prices-container');
        if ($pricesContainer.length === 0) {
            $pricesContainer = $('<div class="cpm-prices-container" style="margin-top: 10px;"></div>');
            $quantityItem.find('.cpm-quantity-content').append($pricesContainer);
        }
        $pricesContainer.append(priceHtml);
    });
    
    // Delete handlers
    $(document).on('click', '.cpm-delete-main-category-btn', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this main category and all its data?')) {
            $(this).closest('.cpm-main-category-item').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
    
    $(document).on('click', '.cpm-delete-category-btn', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this category and all its data?')) {
            $(this).closest('.cpm-category-item').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
    
    
    $(document).on('click', '.cpm-delete-country-btn', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this country and all its data?')) {
            $(this).closest('.cpm-country-item').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
    
    $(document).on('click', '.cpm-delete-quantity-btn', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this quantity and all its prices?')) {
            $(this).closest('.cpm-quantity-item').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
    
    $(document).on('click', '.cpm-delete-price-btn', function(e) {
        e.preventDefault();
        $(this).closest('.cpm-price-item').fadeOut(200, function() {
            $(this).remove();
        });
    });
    
});
