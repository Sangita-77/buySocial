# Custom Product Manager - Complete Plugin Details

## 📋 Overview

**Custom Product Manager** is a comprehensive WordPress e-commerce plugin designed specifically for selling social media services (Facebook likes, Instagram followers, Twitter followers, etc.) with a hierarchical product structure and dynamic pricing system. It's a custom-built alternative to WooCommerce, tailored for service-based products.

**Version:** 1.0.0  
**Author:** Sangita Singh  
**License:** GPL v2 or later

---

## 🎯 Main Purpose

The plugin enables you to:
- Sell social media services with complex variation structures
- Manage products with multiple levels of categories and sub-categories
- Handle dynamic pricing based on customer selections
- Process orders with post URL validation
- Track orders and manage customer accounts

---

## 🏗️ Plugin Architecture

### Core Components

1. **Main Plugin Class** (`Custom_Product_Manager`)
   - Singleton pattern
   - Initializes all components
   - Manages scripts and styles

2. **Database Handler** (`CPM_Database`)
   - Creates and manages database tables
   - Handles activation/deactivation

3. **Admin Interface** (`CPM_Admin`)
   - Product management
   - Category and variation management
   - Order management
   - Review management

4. **Frontend Handler** (`CPM_Frontend`)
   - Product display
   - Page routing
   - User account pages
   - Track order functionality

5. **Cart System** (`CPM_Cart`)
   - Shopping cart functionality
   - Session management

6. **Checkout System** (`CPM_Checkout`)
   - Order processing
   - Billing information handling

7. **Email System** (`CPM_Email`)
   - Order confirmation emails
   - Email templates

8. **AJAX Handlers** (`cpm-ajax.php`)
   - All AJAX operations
   - URL validation
   - Cart operations

---

## 📊 Database Structure

The plugin creates **9 main database tables**:

### 1. `wp_cpm_products`
Stores main products (e.g., "Facebook", "Instagram", "Twitter")
- `id` - Primary key
- `name` - Product name
- `description` - Product description
- `status` - active/inactive
- `created_at`, `updated_at` - Timestamps

### 2. `wp_cpm_categories`
Stores product categories (e.g., "Buy Facebook Likes", "Buy Facebook Followers")
- `id` - Primary key
- `product_id` - Foreign key to products
- `name` - Category name
- `description` - Category description
- `display_order` - Sorting order
- `status` - active/inactive

### 3. `wp_cpm_subcategories`
Stores sub-categories (variation fields like "Select Country", "Select Quantity")
- `id` - Primary key
- `category_id` - Foreign key to categories
- `name` - Sub-category name
- `field_type` - select/number/text
- `display_order` - Sorting order

### 4. `wp_cpm_subcategory_options`
Stores options for dropdown sub-categories
- `id` - Primary key
- `subcategory_id` - Foreign key to subcategories
- `option_value` - Option text
- `display_order` - Sorting order

### 5. `wp_cpm_variations`
Stores product variations with prices
- `id` - Primary key
- `category_id` - Foreign key to categories
- `variation_data` - JSON data of selected options
- `price` - Variation price
- `stock_status` - in_stock/out_of_stock
- `status` - active/inactive

### 6. `wp_cpm_cart`
Stores shopping cart items
- `id` - Primary key
- `session_id` - User session identifier
- `variation_id` - Foreign key to variations
- `quantity` - Item quantity
- `price` - Item price
- `variation_data` - JSON data including post URL
- `created_at`, `updated_at` - Timestamps

### 7. `wp_cpm_orders`
Stores customer orders
- `id` - Primary key
- `order_number` - Unique order identifier
- `user_id` - WordPress user ID (0 for guests)
- `billing_first_name`, `billing_last_name` - Customer name
- `billing_email`, `billing_phone` - Contact info
- `billing_address_1`, `billing_address_2` - Address lines
- `billing_city`, `billing_state`, `billing_postcode`, `billing_country` - Location
- `payment_method` - Payment method used
- `payment_status` - pending/paid/failed
- `order_total` - Total order amount
- `order_status` - pending/processing/completed/cancelled
- `order_date` - Order timestamp

### 8. `wp_cpm_order_items`
Stores individual items in orders
- `id` - Primary key
- `order_id` - Foreign key to orders
- `variation_id` - Foreign key to variations
- `product_name` - Product name at time of order
- `variation_data` - JSON data including post URL and variation details
- `quantity` - Item quantity
- `price` - Item price
- `subtotal` - Line total

### 9. `wp_cpm_reviews`
Stores product reviews
- `id` - Primary key
- `product_id` - Foreign key to products
- `reviewer_name` - Reviewer's name
- `reviewer_email` - Reviewer's email
- `review_text` - Review content
- `rating` - Rating (1-5)
- `status` - pending/approved/rejected
- `verified_purchase` - Whether reviewer purchased
- `created_at` - Review timestamp

---

## 🔑 Key Features

### 1. Hierarchical Product Structure

**New Structure (Main Categories System):**
- **Product** → **Main Category** → **Category** → **Country** → **Quantity** → **Price**
- Example: Facebook → Buy Facebook Likes → Facebook Reel → Worldwide → 50 Likes (2-3 days) → $4.00

**Old Structure (Legacy):**
- **Product** → **Category** → **Sub-Categories** → **Variations** → **Price**

### 2. Dynamic Price Calculation
- Real-time price updates based on customer selections
- Prices stored per variation combination
- Automatic subtotal calculation

### 3. Post URL Validation
- **Platform Detection:** Automatically detects Facebook, Instagram, Twitter, YouTube, TikTok
- **URL Pattern Validation:** Validates post/reel/video URL formats
- **Accessibility Check:** Verifies if post is accessible (with lenient handling for blocked requests)
- **Platform Matching:** Ensures URL matches selected platform
- **Stored in Cart & Orders:** Post URL saved with each cart item and order

### 4. Shopping Cart System
- Session-based cart (works for guests and logged-in users)
- AJAX add to cart
- Cart persistence across sessions
- Real-time cart count updates

### 5. Checkout & Order Processing
- Billing address form
- Auto-fill for logged-in users (from profile or previous orders)
- Order number generation (format: ORD-YYYYMMDD-XXXXX)
- Order confirmation emails
- Payment method selection (demo mode)

### 6. User Account System
- User registration and login
- My Account dashboard
- Order history page (`/my-account/orders/`)
- Edit account page (`/my-account/edit-account/`)
- Track order functionality

### 7. Order Management
- Admin order list with status management
- Order details view with all information
- Order status updates (pending/processing/completed/cancelled)
- Payment status tracking

### 8. Product Reviews
- Customer reviews with ratings (1-5 stars)
- Review moderation (pending/approved/rejected)
- Verified purchase badge
- Average rating calculation
- Review display on product pages

### 9. Email Notifications
- Order confirmation emails
- HTML email templates
- Includes order details, billing info, and track order link

### 10. URL Routing
- Custom rewrite rules for clean URLs:
  - `/login/` - Login page
  - `/register/` - Registration page
  - `/my-account/` - Account dashboard
  - `/my-account/orders/` - Order history
  - `/my-account/edit-account/` - Edit account
  - `/track-order/` - Track order page
  - Product detail pages with custom routing

---

## 📁 File Structure

```
custom-product-manager/
├── custom-product-manager.php    # Main plugin file
├── README.md                      # Basic documentation
├── QUICK_START.md                 # Quick start guide
├── PLUGIN_DETAILS.md              # This file
│
├── admin/                         # Admin interface
│   └── views/
│       ├── products-list.php      # Products listing
│       ├── product-form.php       # Add/Edit product
│       ├── product-categories.php # Category management
│       ├── order-view.php         # Order details
│       ├── orders-list.php        # Orders listing
│       └── reviews-list.php       # Reviews management
│
├── frontend/                      # Frontend templates
│   └── views/
│       ├── products-list.php      # Products listing page
│       ├── product-single.php     # Single product view
│       ├── main-category-details.php # Product detail with variations
│       ├── cart.php               # Shopping cart
│       ├── checkout.php           # Checkout page
│       ├── track-order.php        # Track order page
│       ├── login.php              # Login page
│       ├── register.php           # Registration page
│       ├── my-account.php         # Account dashboard
│       ├── my-account-orders.php  # Order history
│       └── my-account-edit.php    # Edit account
│
├── includes/                      # Core functionality
│   ├── class-cpm-database.php     # Database operations
│   ├── class-cpm-admin.php        # Admin interface
│   ├── class-cpm-frontend.php     # Frontend handling
│   ├── class-cpm-cart.php         # Cart system
│   ├── class-cpm-checkout.php     # Checkout processing
│   ├── class-cpm-email.php        # Email system
│   ├── cpm-ajax.php               # AJAX handlers
│   └── cpm-functions.php          # Helper functions
│
└── assets/                        # Static assets
    ├── css/
    │   ├── admin.css              # Admin styles
    │   └── frontend.css           # Frontend styles
    └── js/
        ├── admin.js               # Admin scripts
        └── frontend.js            # Frontend scripts
```

---

## 🎨 Frontend Features

### Product Display
- Product listing with images and descriptions
- Product detail pages with variation selectors
- Dynamic price display
- Real-time price calculation
- Add to cart functionality
- Buy now (direct checkout) option

### Variation Selection
- Main category selection
- Category selection
- Country selection
- Quantity selection
- Post URL input with validation
- Real-time price updates

### Cart & Checkout
- Shopping cart with item management
- Remove items from cart
- Quantity updates
- Checkout form with billing details
- Auto-filled billing for logged-in users
- Order summary display

### User Account
- Account dashboard
- Order history with status
- Track order functionality
- Edit account information
- Logout functionality

---

## 🔧 Admin Features

### Product Management
- Add/Edit/Delete products
- Product status management
- Product descriptions

### Category Management
- **New System:** Main categories with nested structure
  - Main Category → Category → Country → Quantity → Price
- **Legacy System:** Categories → Sub-categories → Variations
- Drag-and-drop ordering
- Category status management

### Order Management
- View all orders
- Filter by status
- Order details view
- Update order status
- Update payment status
- View order items with post URLs

### Review Management
- View all reviews
- Approve/Reject reviews
- Review moderation

---

## 🔌 Shortcodes

- `[cpm_products]` - Display all products
- `[cpm_product id="1"]` - Display specific product
- `[cpm_cart]` - Display shopping cart
- `[cpm_checkout]` - Display checkout page
- `[cpm_track_order]` - Display track order form

---

## 🌐 URL Routes

The plugin creates custom URL routes:

- `/login/` - User login page
- `/register/` - User registration page
- `/my-account/` - User account dashboard
- `/my-account/orders/` - Order history
- `/my-account/edit-account/` - Edit account
- `/track-order/` - Track order by order number
- Product detail pages via custom routing

---

## 🔐 Security Features

- Nonce verification for all forms
- SQL injection protection (prepared statements)
- XSS protection (data sanitization and escaping)
- CSRF protection
- User capability checks
- Session management for cart

---

## 📧 Email System

### Order Confirmation Email
- Sent automatically after order placement
- Includes:
  - Order number
  - Order date and status
  - Billing information
  - Order items with details
  - Total amount
  - Track order link

### Email Template
- HTML email template
- Responsive design
- Professional styling
- Includes all order details

---

## 🛠️ Technical Details

### Requirements
- WordPress 5.0+
- PHP 7.0+
- MySQL 5.6+
- jQuery (included with WordPress)

### Dependencies
- WordPress Core
- jQuery (WordPress bundled)
- No external dependencies

### Session Management
- Uses PHP sessions for cart
- Session ID generation for guests
- User ID linking for logged-in users

### AJAX Operations
- Add to cart
- Remove from cart
- Update cart
- Buy now
- URL validation
- Submit review
- Category/subcategory management (admin)

---

## 🎯 Use Cases

### Primary Use Case
Selling social media services like:
- Facebook Likes, Followers, Shares, Comments
- Instagram Followers, Likes, Views
- Twitter Followers, Retweets, Likes
- YouTube Views, Subscribers, Likes
- TikTok Followers, Likes, Views

### Product Structure Example

**Product:** Facebook
- **Main Category:** Buy Facebook Likes
  - **Category:** Facebook Reel
    - **Country:** Worldwide
      - **Quantity:** 50 Facebook Likes (2-3 days) - **Price:** $4.00
      - **Quantity:** 100 Facebook Likes (2-3 days) - **Price:** $7.00
      - **Quantity:** 500 Facebook Likes (2-3 days) - **Price:** $30.00

---

## 🔄 Workflow

### Customer Journey
1. Browse products → View product details
2. Select main category → Select category → Select country → Select quantity
3. Enter post URL → URL validation → Price updates
4. Add to cart or Buy now
5. Review cart → Proceed to checkout
6. Fill billing information (auto-filled if logged in)
7. Place order → Receive confirmation email
8. Track order using order number

### Admin Workflow
1. Create product → Add main categories → Add categories → Add countries → Add quantities with prices
2. Manage orders → Update status → View order details
3. Moderate reviews → Approve/Reject

---

## 📝 Recent Updates & Features

### Latest Features Added
1. **Post URL Validation System**
   - Platform-specific URL validation
   - Real-time validation with feedback
   - Post URL stored in cart and orders

2. **Billing Address Auto-fill**
   - Auto-populates for logged-in users
   - Uses profile data or previous orders

3. **Order History Page**
   - `/my-account/orders/` route
   - Displays all user orders
   - Links to track order page

4. **Post URL Display**
   - Shows in cart, checkout, order details, and track order pages
   - Clickable links that open in new tab

5. **Review Rating Display**
   - Dynamic star ratings on product pages
   - Average rating calculation
   - Review count display

---

## 🚀 Future Enhancement Possibilities

- Payment gateway integration (Stripe, PayPal, etc.)
- Inventory management
- Discount codes/coupons
- Bulk order processing
- API integration for service delivery
- Multi-currency support
- Advanced reporting and analytics
- Email marketing integration
- Affiliate system
- Subscription products

---

## 📞 Support & Documentation

- **Plugin Files:** All code is well-commented
- **Documentation Files:**
  - `README.md` - Basic overview
  - `QUICK_START.md` - Quick setup guide
  - `PLUGIN_DETAILS.md` - This comprehensive guide
  - `HOW_TO_ADD_OPTIONS.md` - Category setup guide
  - `HOW_TO_VIEW_PRODUCT_DETAILS.md` - Product display guide

---

## ⚙️ Configuration

### Auto-Created Pages
On activation, the plugin automatically creates:
- Products listing page
- Cart page
- Checkout page
- Track order page
- Login page
- Register page
- My Account page

### Settings
- Page IDs stored in WordPress options
- Rewrite rules for clean URLs
- Session management configuration

---

## 🔍 Debugging

### Debug Mode
Enable `WP_DEBUG` in `wp-config.php` to see:
- Cart item debug information
- Order query debug logs
- URL validation debug messages
- Variation data structure

### Error Logging
Errors are logged to WordPress debug log when `WP_DEBUG` is enabled.

---

## 📊 Performance

- Database queries optimized with indexes
- AJAX for dynamic operations (no page reloads)
- Session-based cart (no database queries for cart display)
- Cached category data in WordPress options

---

## 🎨 Customization

### Styling
- Customize `assets/css/frontend.css` for frontend
- Customize `assets/css/admin.css` for admin
- All classes prefixed with `cpm-`

### Templates
- Modify templates in `frontend/views/`
- All templates use WordPress coding standards
- Easy to customize and extend

### Functionality
- Extend classes for custom functionality
- Use WordPress hooks and filters
- AJAX handlers can be extended

---

## ✅ Best Practices Implemented

- WordPress coding standards
- Prepared SQL statements
- Data sanitization and escaping
- Nonce verification
- Capability checks
- Error handling
- Session management
- Clean URL structure
- Responsive design
- Accessibility considerations

---

## 📦 Installation & Setup

1. Upload plugin to `/wp-content/plugins/`
2. Activate plugin
3. Database tables created automatically
4. Pages created automatically
5. Start creating products!

---

## 🎓 Learning Resources

- Check `QUICK_START.md` for setup guide
- Review code comments for implementation details
- Examine database structure for data relationships
- Study AJAX handlers for frontend interactions

---

**End of Plugin Details Document**

