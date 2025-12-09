<?php
function mytheme_enqueue_styles() {
    wp_enqueue_style('style', get_stylesheet_uri());
}

add_action('wp_enqueue_scripts', 'mytheme_enqueue_styles');

function mytheme_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');
    register_nav_menus([
        'primary' => __('Primary Menu', 'osmb'),
    ]);
}

add_action('after_setup_theme', 'mytheme_theme_setup');


/**
* Custom functions that act independently of the theme templates.
*/
require_once get_template_directory() . '/inc/custom-function.php';