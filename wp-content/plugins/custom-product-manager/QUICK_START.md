# Quick Start Guide

## Installation Steps

1. **Activate the Plugin**
   - Go to WordPress Admin > Plugins
   - Find "Custom Product Manager" and click "Activate"
   - The plugin will automatically create database tables and necessary pages

2. **Create Your First Product**
   - Go to **Custom Products > Add Product**
   - Enter product name (e.g., "Facebook")
   - Enter description
   - Click "Save Product"

3. **Set Up Categories and Variations**
   - After saving, click **"Manage Categories, Sub-Categories & Variations"**
   - Click **"Add Category"** (e.g., "Buy Facebook Likes")
   - Add Sub-Categories:
     - "Select Facebook Like Type" (Type: Dropdown)
       - Options: Real Likes, Fast Likes, Premium Likes
     - "Select Target Country" (Type: Dropdown)
       - Options: USA, UK, Canada, etc.
     - "Select Quantity" (Type: Number)
   - Add Variations:
     - Click "Add Variation/Price"
     - Select values for each sub-category
     - Enter price
     - Repeat for all combinations

4. **View Your Product**
   - Go to the "Products" page (automatically created)
   - Or use shortcode: `[cpm_products]`

## Example Setup

### Product: Facebook

#### Category 1: Buy Facebook Likes
- **Sub-Category 1**: Select Facebook Like Type (Dropdown)
  - Options: Real Likes, Fast Likes
- **Sub-Category 2**: Select Target Country (Dropdown)
  - Options: USA, UK, Canada
- **Sub-Category 3**: Select Quantity (Number)

- **Variations**:
  - Real Likes + USA + 1000 = $10.00
  - Real Likes + USA + 5000 = $45.00
  - Fast Likes + UK + 1000 = $8.00
  - etc.

#### Category 2: Buy Facebook Followers
- Similar structure with different variations

## Frontend Flow

1. Customer visits product page
2. Selects a category (e.g., "Buy Facebook Likes")
3. Selects sub-category values (Like Type, Country, Quantity)
4. Price updates automatically
5. Clicks "Add to Cart"
6. Proceeds to checkout
7. Fills billing information
8. Places order
9. Receives email confirmation

## Important Notes

- Payment methods are configured but need actual gateway integration for production
- Cart uses session-based storage (stored in database)
- Orders are stored and can be managed from Admin > Custom Products > Orders
- Email notifications are sent automatically after order placement

## Troubleshooting

- **Products not showing**: Check if product status is "Active"
- **Price not updating**: Ensure all sub-categories have values selected
- **Cart not working**: Check if session is enabled in PHP settings
- **Email not sending**: Configure WordPress email settings

## Next Steps

- Customize CSS in `/assets/css/frontend.css`
- Integrate payment gateways in checkout class
- Add more product features as needed




