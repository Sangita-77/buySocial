<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cpm-statistics-wrapper">
    <div class="cpm-statistics-container cpm-statistics-<?php echo esc_attr($atts['layout']); ?>" style="display: grid; grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr); gap: 30px; padding: 40px 20px;">
        
        <div class="cpm-stat-item">
            <div class="cpm-stat-icon">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9C9.5 7.62 10.62 6.5 12 6.5C13.38 6.5 14.5 7.62 14.5 9C14.5 10.38 13.38 11.5 12 11.5Z" fill="#0073aa"/>
                </svg>
            </div>
            <div class="cpm-stat-number" data-count="<?php echo esc_attr($countries_count); ?>">0</div>
            <div class="cpm-stat-label"><?php _e('Countries Served', 'custom-product-manager'); ?></div>
        </div>
        
        <div class="cpm-stat-item">
            <div class="cpm-stat-icon">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 6H16L14 4H10L8 6H4C2.9 6 2 6.9 2 8V19C2 20.1 2.9 21 4 21H20C21.1 21 22 20.1 22 19V8C22 6.9 21.1 6 20 6ZM12 17C9.24 17 7 14.76 7 12C7 9.24 9.24 7 12 7C14.76 7 17 9.24 17 12C17 14.76 14.76 17 12 17ZM12 9C10.34 9 9 10.34 9 12C9 13.66 10.34 15 12 15C13.66 15 15 13.66 15 12C15 10.34 13.66 9 12 9Z" fill="#0073aa"/>
                </svg>
            </div>
            <div class="cpm-stat-number" data-count="<?php echo esc_attr($orders_delivered); ?>">0</div>
            <div class="cpm-stat-label"><?php _e('Orders Delivered', 'custom-product-manager'); ?></div>
        </div>
        
        <div class="cpm-stat-item">
            <div class="cpm-stat-icon">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="#0073aa"/>
                </svg>
            </div>
            <div class="cpm-stat-number" data-count="<?php echo esc_attr($happy_customers); ?>">0</div>
            <div class="cpm-stat-label"><?php _e('Happy Customers', 'custom-product-manager'); ?></div>
        </div>
        
        <div class="cpm-stat-item">
            <div class="cpm-stat-icon">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="#0073aa"/>
                </svg>
            </div>
            <div class="cpm-stat-number" data-count="<?php echo esc_attr($in_business_years); ?>">0</div>
            <div class="cpm-stat-label"><?php _e('Years In Business', 'custom-product-manager'); ?></div>
        </div>
        
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Animate numbers on scroll
    function animateCounter($element) {
        var $this = $element;
        var countTo = parseInt($this.attr('data-count'));
        var duration = 2000; // 2 seconds
        
        if (!$this.hasClass('animated')) {
            $this.addClass('animated');
            
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: duration,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(countTo);
                }
            });
        }
    }
    
    // Check if element is in viewport
    function isInViewport($element) {
        var elementTop = $element.offset().top;
        var elementBottom = elementTop + $element.outerHeight();
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();
        
        return elementBottom > viewportTop && elementTop < viewportBottom;
    }
    
    // Animate on scroll
    $(window).on('scroll', function() {
        $('.cpm-stat-number').each(function() {
            if (isInViewport($(this))) {
                animateCounter($(this));
            }
        });
    });
    
    // Also check on page load
    $('.cpm-stat-number').each(function() {
        if (isInViewport($(this))) {
            animateCounter($(this));
        }
    });
});
</script>

