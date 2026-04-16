<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue custom cart page assets only on the custom cart page template.
 */
function axiom_enqueue_cart_page_assets() {
    if (!is_page()) {
        return;
    }

    $page_template = get_page_template_slug();

    if ($page_template !== 'cart/page-cart-custom.php') {
        return;
    }

    $css_path = get_template_directory() . '/assets/css/cart-page.css';
    $js_path  = get_template_directory() . '/assets/js/cart-page.js';

    if (file_exists($css_path)) {
        wp_enqueue_style(
            'axiom-cart-page',
            get_template_directory_uri() . '/assets/css/cart-page.css',
            array(),
            filemtime($css_path)
        );
    }

    if (file_exists($js_path)) {
        wp_enqueue_script(
            'axiom-cart-page',
            get_template_directory_uri() . '/assets/js/cart-page.js',
            array(),
            filemtime($js_path),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_enqueue_cart_page_assets');
