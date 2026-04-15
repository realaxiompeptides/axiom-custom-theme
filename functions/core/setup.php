<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_custom_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    add_image_size('axiom_product_card', 700, 700, true);
}
add_action('after_setup_theme', 'axiom_custom_theme_setup');

function axiom_get_logo_url($filename = 'axiom-logo.PNG') {
    return get_template_directory_uri() . '/assets/images/' . $filename;
}
