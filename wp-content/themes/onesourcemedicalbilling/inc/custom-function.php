<?php
/**
 * Implement an optional custom function for One Source Medical Billing
 *
 * See https://codex.wordpress.org/Custom_Function
 *
 * @package WordPress
 * @subpackage One Source Medical Billing
 * @since One Source Medical Billing 1.0
 */
?>
<?php

add_image_size( 'service-icon', 95, 95, true );
//add_image_size( 'innerportfolio-thumb', 379, 475, true );
//add_image_size( 'team-thumb', 114, 114, true );








if( ! function_exists( 'website_slug_render_title' ) ) {
    function website_slug_render_title(){
?>
        <title><?php wp_title( '|', true, 'right' ); ?></title>
<?php
    }
    add_action( 'wp_head', 'website_slug_render_title' );
}


// ADD Script
function theme_scripts() {
	wp_enqueue_style( 'font-awesome-style','https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '', false );
	wp_enqueue_script('jquery');
    wp_enqueue_script( 'popper-script', get_template_directory_uri() . '/js/popper.min.js', array(), '', true );
	wp_enqueue_script( 'bootstrap-script', get_template_directory_uri() . '/js/bootstrap.min.js', array(), '', true );
	wp_enqueue_script( 'site-script',get_template_directory_uri() . '/js/custom-script.js?cb='.time(), array(), '', true );

}

add_action( 'wp_enqueue_scripts', 'theme_scripts' );



function replace_core_jquery_version() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', "https://code.jquery.com/jquery-1.11.3.min.js", array(), '1.11.3' );
}
add_action( 'wp_enqueue_scripts', 'replace_core_jquery_version' );


// ADD CSS
function load_css_files() {
	wp_deregister_style( 'One Source Medical Billing-style' );
	wp_deregister_style( 'cnss_font_awesome_v4_shims' );
	wp_enqueue_style( 'normalize', get_template_directory_uri() . '/css/bootstrap.min.css', array(), '', false );
    wp_register_style( 'One Source Medical Billing', get_stylesheet_uri(), array( 'normalize' ));
    wp_enqueue_style( 'One Source Medical Billing' );
	wp_enqueue_style( 'gfont-style','https://fonts.googleapis.com/css?family=Mulish:wght@200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&family=Oswald:wght@200;300;400;500;600;700&family=Roboto+Condensed:wght@300;400;700&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', array(), '', false );
	wp_enqueue_style( 'custom-style', get_template_directory_uri() . '/custom-style.css?cb='.time(), array(), '', false );
}

add_action( 'wp_enqueue_scripts', 'load_css_files' );



function font_admin_init() {
   wp_enqueue_style('font-awesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'); 
}
add_action('admin_init', 'font_admin_init');
function custom_post_css() {
   echo "<style type='text/css' media='screen'>
            #adminmenu .menu-icon-our_servicess div.wp-menu-image:before {
                font-family:  FontAwesome !important;
                content: '\\f085'; // this is where you enter the fontaweseom font code
            }
            #adminmenu .menu-icon-One Source Medical Billing_portfolio div.wp-menu-image:before {
                font-family:  FontAwesome !important;
                content: '\\f03e'; // this is where you enter the fontaweseom font code
            }
            #adminmenu .menu-icon-testimoni div.wp-menu-image:before {
                font-family:  FontAwesome !important;
                content: '\\f10e'; // this is where you enter the fontaweseom font code
            }
            #adminmenu .menu-icon-osmb_citi div.wp-menu-image:before {
                font-family:  FontAwesome !important;
                content: '\\f00b'; // this is where you enter the fontaweseom font code
            }
            #adminmenu .menu-icon-osmb_brand div.wp-menu-image:before {
                font-family:  FontAwesome !important;
                content: '\\f03e'; // this is where you enter the fontaweseom font code
            }
     </style>";
}
add_action('admin_head', 'custom_post_css');







// End of the file
function site_rewrite_rules() {

    flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'site_rewrite_rules' );

































