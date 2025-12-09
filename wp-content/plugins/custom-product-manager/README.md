# Custom Product Manager

A comprehensive WordPress plugin for managing hierarchical products with multiple levels of variations, similar to WooCommerce but customized for selling social media services (like Facebook likes, followers, etc.).

## Features

- **Hierarchical Product Structure**: 
  - Main Products (e.g., "Facebook")
  - Categories (e.g., "Buy Facebook Likes", "Buy Facebook Followers")
  - Sub-Categories (e.g., "Select Facebook Like Type", "Select Target Country", "Select Quantity")
  - Variations with Dynamic Pricing

- **Admin Interface**:
  - Easy product creation and management
  - Category and sub-category management
  - Variation and pricing management
  - Order management system

- **Frontend Features**:
  - Product display with dynamic variation selection
  - Real-time price calculation based on selections
  - Shopping cart functionality
  - Checkout with billing address
  - Email order confirmation

- **Order Management**:
  - Complete order tracking
  - Email notifications to buyers
  - Order status management

## Installation

1. Upload the `custom-product-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create necessary database tables and pages

## Usage

### Creating a Product

1. Go to **Custom Products > Add Product**
2. Enter product name and description
3. Save the product
4. Click **"Manage Categories, Sub-Categories & Variations"** to set up the product structure

### Setting Up Categories

1. After creating a product, click on "Manage Categories"
2. Add a Category (e.g., "Buy Facebook Likes")
3. Add Sub-Categories:
   - "Select Facebook Like Type" (Dropdown with options like "Real Likes", "Fast Likes")
   - "Select Target Country" (Dropdown with country options)
   - "Select Quantity" (Number input)
4. Add Variations with Prices:
   - For each combination of sub-category values, set a price
   - Example: Real Likes + USA + 1000 = $10.00

### Frontend Display

Use the shortcode `[cpm_products]` to display all products, or `[cpm_product id="1"]` to display a specific product.

The plugin automatically creates pages for:
- Products listing
- Cart
- Checkout

### Cart and Checkout

Customers can:
1. Select product variations
2. See real-time price updates
3. Add items to cart
4. Checkout with billing information
5. Receive order confirmation via email

## Database Structure

The plugin creates the following tables:
- `wp_cpm_products` - Main products
- `wp_cpm_categories` - Product categories
- `wp_cpm_subcategories` - Sub-categories (variation fields)
- `wp_cpm_subcategory_options` - Options for dropdown sub-categories
- `wp_cpm_variations` - Product variations with prices
- `wp_cpm_cart` - Shopping cart items
- `wp_cpm_orders` - Customer orders
- `wp_cpm_order_items` - Order line items

## Shortcodes

- `[cpm_products]` - Display all products
- `[cpm_product id="1"]` - Display a specific product
- `[cpm_cart]` - Display shopping cart
- `[cpm_checkout]` - Display checkout page

## Email Notifications

The plugin sends order confirmation emails to customers with:
- Order number
- Order items and variations
- Billing information
- Total amount

## Customization

You can customize the plugin by:
- Modifying CSS files in `/assets/css/`
- Customizing templates in `/frontend/views/`
- Adding payment gateway integrations in the checkout class

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Notes

- Payment integration is set up for demo purposes. You'll need to integrate actual payment gateways for production use.
- Session management is used for cart functionality
- The plugin is designed to work independently without WooCommerce

## Support

For issues or questions, please check the plugin documentation or contact support.

## License

GPL v2 or later




