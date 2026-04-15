<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_disable_default_catalog_bits() {
    if (!function_exists('is_shop')) {
        return;
    }

    if (is_shop() || is_product_category() || is_product_tag() || is_tax('product_cat') || is_tax('product_tag')) {
        remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
        remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
        remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    }
}
add_action('wp', 'axiom_disable_default_catalog_bits');
