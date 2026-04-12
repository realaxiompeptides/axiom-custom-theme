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
}
add_action('after_setup_theme', 'axiom_custom_theme_setup');

function axiom_custom_theme_assets() {
    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'axiom-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'axiom-fa',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
        array(),
        '6.5.2'
    );

    wp_enqueue_style('axiom-base', $theme_uri . '/assets/css/style.css', array(), '1.2');
    wp_enqueue_style('axiom-menu', $theme_uri . '/assets/css/menu.css', array('axiom-base'), '1.2');
    wp_enqueue_style('axiom-cart', $theme_uri . '/assets/css/cart.css', array('axiom-base'), '1.2');
    wp_enqueue_style('axiom-footer', $theme_uri . '/assets/css/footer.css', array('axiom-base'), '1.2');
    wp_enqueue_style('axiom-home', $theme_uri . '/assets/css/home.css', array('axiom-base'), '1.2');
    wp_enqueue_style('axiom-collection', $theme_uri . '/assets/css/homepage-collection.css', array('axiom-base'), '1.2');
    wp_enqueue_style('axiom-trust', $theme_uri . '/assets/css/trust-strip.css', array('axiom-base'), '1.2');
    wp_enqueue_style('axiom-faq', $theme_uri . '/assets/css/faq-section.css', array('axiom-base'), '1.2');
    wp_enqueue_style('axiom-age-gate', $theme_uri . '/assets/css/age-gate.css', array('axiom-base'), '1.2');

    wp_enqueue_script(
        'axiom-main',
        $theme_uri . '/assets/js/main.js',
        array(),
        '1.2',
        true
    );

    wp_localize_script('axiom-main', 'AXIOM_THEME', array(
        'themeUrl' => $theme_uri,
        'homeUrl'  => home_url('/'),
        'shopUrl'  => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'),
        'cartUrl'  => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/'),
        'checkoutUrl' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/'),
        'accountUrl'  => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'),
    ));
}
add_action('wp_enqueue_scripts', 'axiom_custom_theme_assets');
