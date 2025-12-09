<?php
/**
 * Email class
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPM_Email {
    
    /**
     * Send order confirmation email
     */
    public function send_order_confirmation($order_id) {
        global $wpdb;
        
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . CPM_Database::get_table('orders') . " WHERE id = %d",
            $order_id
        ));
        
        if (!$order) {
            error_log('CPM Email: Order not found for ID: ' . $order_id);
            return false;
        }
        
        $order_items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . CPM_Database::get_table('order_items') . " WHERE order_id = %d",
            $order_id
        ));
        
        $to = $order->billing_email;
        $subject = sprintf(__('Order Confirmation - %s', 'custom-product-manager'), $order->order_number);
        
        $message = $this->get_email_template($order, $order_items);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Log email attempt
        error_log('CPM Email: Attempting to send email to: ' . $to);
        error_log('CPM Email: Subject: ' . $subject);
        
        // For local development, also save email to option for viewing
        if (defined('WP_DEBUG') && WP_DEBUG) {
            update_option('cpm_last_email_' . $order_id, array(
                'to' => $to,
                'subject' => $subject,
                'message' => $message,
                'sent_at' => current_time('mysql')
            ));
        }
        
        $result = wp_mail($to, $subject, $message, $headers);
        
        if ($result) {
            error_log('CPM Email: Email sent successfully to: ' . $to);
        } else {
            error_log('CPM Email: Failed to send email to: ' . $to);
        }
        
        return $result;
    }
    
    /**
     * Get email template
     */
    private function get_email_template($order, $order_items) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: #fff; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .order-details { background: #fff; padding: 15px; margin: 15px 0; border: 1px solid #ddd; }
                .order-item { padding: 10px; border-bottom: 1px solid #eee; }
                .order-item:last-child { border-bottom: none; }
                .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 15px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
                th { background: #f5f5f5; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php _e('Order Confirmation', 'custom-product-manager'); ?></h1>
                </div>
                
                <div class="content">
                    <p><?php _e('Thank you for your order!', 'custom-product-manager'); ?></p>
                    
                    <div class="order-details">
                        <h2><?php _e('Order Details', 'custom-product-manager'); ?></h2>
                        <p><strong><?php _e('Order Number:', 'custom-product-manager'); ?></strong> <?php echo esc_html($order->order_number); ?></p>
                        <p><strong><?php _e('Order Date:', 'custom-product-manager'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->order_date))); ?></p>
                        <p><strong><?php _e('Order Status:', 'custom-product-manager'); ?></strong> <?php echo esc_html(ucfirst($order->order_status)); ?></p>
                    </div>
                    
                    <div class="order-details">
                        <h2><?php _e('Billing Information', 'custom-product-manager'); ?></h2>
                        <p>
                            <?php echo esc_html($order->billing_first_name . ' ' . $order->billing_last_name); ?><br>
                            <?php echo esc_html($order->billing_email); ?><br>
                            <?php if ($order->billing_phone) : ?>
                                <?php echo esc_html($order->billing_phone); ?><br>
                            <?php endif; ?>
                            <?php echo esc_html($order->billing_address_1); ?><br>
                            <?php if ($order->billing_address_2) : ?>
                                <?php echo esc_html($order->billing_address_2); ?><br>
                            <?php endif; ?>
                            <?php echo esc_html($order->billing_city . ', ' . $order->billing_state . ' ' . $order->billing_postcode); ?><br>
                            <?php echo esc_html($order->billing_country); ?>
                        </p>
                    </div>
                    
                    <div class="order-details">
                        <h2><?php _e('Order Items', 'custom-product-manager'); ?></h2>
                        <table>
                            <thead>
                                <tr>
                                    <th><?php _e('Product', 'custom-product-manager'); ?></th>
                                    <th><?php _e('Main Category', 'custom-product-manager'); ?></th>
                                    <th><?php _e('Category', 'custom-product-manager'); ?></th>
                                    <th><?php _e('Country', 'custom-product-manager'); ?></th>
                                    <th><?php _e('Quantity', 'custom-product-manager'); ?></th>
                                    <th><?php _e('Price', 'custom-product-manager'); ?></th>
                                    <th><?php _e('Subtotal', 'custom-product-manager'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item) : ?>
                                    <?php
                                    $variation_data = json_decode($item->variation_data, true);
                                    
                                    // Extract details from new structure
                                    $product_name = isset($variation_data['product_name']) ? $variation_data['product_name'] : $item->product_name;
                                    $main_category = isset($variation_data['main_category']) ? $variation_data['main_category'] : '';
                                    $category = isset($variation_data['category']) ? $variation_data['category'] : '';
                                    $country = isset($variation_data['country']) ? $variation_data['country'] : '';
                                    $quantity_text = isset($variation_data['quantity']) ? $variation_data['quantity'] : '';
                                    ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($product_name); ?></strong></td>
                                        <td><?php echo esc_html($main_category ? $main_category : '-'); ?></td>
                                        <td><?php echo esc_html($category ? $category : '-'); ?></td>
                                        <td><?php echo esc_html($country ? $country : '-'); ?></td>
                                        <td><?php echo esc_html($quantity_text ? $quantity_text : $item->quantity); ?></td>
                                        <td><?php echo cpm_format_price($item->price); ?></td>
                                        <td><?php echo cpm_format_price($item->subtotal); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="total">
                            <strong><?php _e('Total:', 'custom-product-manager'); ?> <?php echo cpm_format_price($order->order_total); ?></strong>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <p><strong><?php _e('Payment Method:', 'custom-product-manager'); ?></strong> <?php echo esc_html(ucfirst(str_replace('_', ' ', $order->payment_method))); ?></p>
                        <p><strong><?php _e('Payment Status:', 'custom-product-manager'); ?></strong> <?php echo esc_html(ucfirst($order->payment_status)); ?></p>
                    </div>
                    
                    <div class="order-details" style="background: #e8f4f8; border-left: 4px solid #0073aa; margin-top: 20px;">
                        <h3 style="margin-top: 0;"><?php _e('Track Your Order', 'custom-product-manager'); ?></h3>
                        <p><?php _e('You can track your order status anytime using your order number:', 'custom-product-manager'); ?> <strong><?php echo esc_html($order->order_number); ?></strong></p>
                        <?php
                        $track_order_page_id = get_option('cpm_track_order_page_id', 0);
                        if ($track_order_page_id) {
                            $track_order_url = add_query_arg('order_number', $order->order_number, get_permalink($track_order_page_id));
                        } else {
                            $track_order_url = home_url('/track-order/?order_number=' . urlencode($order->order_number));
                        }
                        echo '<p><a href="' . esc_url($track_order_url) . '" style="display: inline-block; padding: 10px 20px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 4px; margin-top: 10px;">' . __('Track Order', 'custom-product-manager') . '</a></p>';
                        ?>
                    </div>
                </div>
                
                <div class="footer">
                    <p><?php echo get_bloginfo('name'); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}


