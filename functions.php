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
        'script'
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'axiom-custom-theme'),
        'footer'  => __('Footer Menu', 'axiom-custom-theme'),
    ));
}
add_action('after_setup_theme', 'axiom_custom_theme_setup');

function axiom_custom_theme_assets() {
    $theme_uri = get_template_directory_uri();

    wp_enqueue_style('google-fonts-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap', array(), null);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2');

    wp_enqueue_style('axiom-style', $theme_uri . '/assets/css/style.css', array(), '1.0');
    wp_enqueue_style('axiom-menu', $theme_uri . '/assets/css/menu.css', array('axiom-style'), '1.0');
    wp_enqueue_style('axiom-cart', $theme_uri . '/assets/css/cart.css', array('axiom-style'), '1.0');
    wp_enqueue_style('axiom-footer', $theme_uri . '/assets/css/footer.css', array('axiom-style'), '1.0');
    wp_enqueue_style('axiom-home', $theme_uri . '/assets/css/home.css', array('axiom-style'), '1.0');
    wp_enqueue_style('axiom-homepage-collection', $theme_uri . '/assets/css/homepage-collection.css', array('axiom-style'), '1.0');
    wp_enqueue_style('axiom-trust-strip', $theme_uri . '/assets/css/trust-strip.css', array('axiom-style'), '1.0');
    wp_enqueue_style('axiom-faq', $theme_uri . '/assets/css/faq-section.css', array('axiom-style'), '1.0');
    wp_enqueue_style('axiom-age-gate', $theme_uri . '/assets/css/age-gate.css', array('axiom-style'), '1.0');
    wp_enqueue_style('axiom-main', $theme_uri . '/assets/css/main.css', array('axiom-style'), '1.0');

    wp_enqueue_script('supabase-js', 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2', array(), null, true);

    wp_enqueue_script('axiom-product-data', $theme_uri . '/assets/js/product-data.js', array(), '1.0', true);
    wp_enqueue_script('axiom-trust-strip-loader', $theme_uri . '/assets/js/trust-strip-loader.js', array(), '1.0', true);
    wp_enqueue_script('axiom-homepage-collection-data', $theme_uri . '/assets/js/homepage-collection-data.js', array(), '1.0', true);
    wp_enqueue_script('axiom-homepage-collection-loader', $theme_uri . '/assets/js/homepage-collection-loader.js', array(), '1.0', true);
    wp_enqueue_script('axiom-faq-loader', $theme_uri . '/assets/js/faq-section-loader.js', array(), '1.0', true);
    wp_enqueue_script('axiom-menu-js', $theme_uri . '/assets/js/menu.js', array(), '1.0', true);
    wp_enqueue_script('axiom-cart-js', $theme_uri . '/assets/js/cart.js', array(), '1.0', true);
    wp_enqueue_script('axiom-app-js', $theme_uri . '/assets/js/app.js', array(), '1.0', true);
    wp_enqueue_script('axiom-footer-loader', $theme_uri . '/assets/js/footer-loader.js', array(), '1.0', true);
    wp_enqueue_script('axiom-age-gate-js', $theme_uri . '/assets/js/age-gate.js', array(), '1.0', true);
}
add_action('wp_enqueue_scripts', 'axiom_custom_theme_assets');
