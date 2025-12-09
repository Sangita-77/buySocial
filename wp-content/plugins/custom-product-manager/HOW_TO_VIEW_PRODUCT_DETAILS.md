# How to View Product Details Page

## Method 1: Using the Header Dropdown (Recommended)

1. Go to your website's frontend (homepage or any page)
2. You should see a product dropdown in the header (automatically added by the plugin)
3. Select a product from the dropdown
4. Main categories will appear below the dropdown
5. Click on any main category
6. The product details page will load automatically

## Method 2: Direct URL Access

You can access the product details page directly using this URL format:

```
http://yoursite.com/any-page/?cpm_main_category=1&product_id=1&main_cat=maincat_1
```

Replace:
- `yoursite.com/any-page/` - Any page on your WordPress site (homepage, about page, etc.)
- `product_id=1` - The ID of your product (check in admin: Products > Edit Product)
- `main_cat=maincat_1` - The main category ID (this is the ID you see when editing the product in admin)

## Method 3: Create a Dedicated Page

1. Go to WordPress Admin > Pages > Add New
2. Create a new page (e.g., "Product Details")
3. Add this shortcode: `[cpm_main_category_details]`
4. Publish the page
5. Access it with URL parameters: `?cpm_main_category=1&product_id=1&main_cat=maincat_1`

## Finding Your Product and Main Category IDs

1. Go to WordPress Admin > Custom Product Manager > Products
2. Edit the product you want to view
3. Scroll down to see the saved main categories
4. The main category ID will be shown in the form (e.g., `maincat_1`, `maincat_2`)
5. The product ID is shown in the URL when editing: `?post=1` means product_id is 1

## Troubleshooting

**If you don't see the dropdown in header:**
- Make sure you have at least one active product
- Clear your browser cache
- Check if the plugin is activated

**If the page shows "Invalid parameters":**
- Make sure you're using the correct product_id
- Make sure you're using the correct main_cat ID (check in admin)
- Make sure the product has main categories saved

**If the page shows "Product not found":**
- Make sure the product status is "active"
- Make sure you're using the correct product_id

**If the page shows "Main category not found":**
- Make sure you've saved main categories for that product
- Make sure you're using the correct main_cat ID

