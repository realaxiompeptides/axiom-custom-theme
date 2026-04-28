<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_get_enhanced_product_data($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return false;
    }

    $slug = $product->get_slug();

    $data_file = get_template_directory() . '/product-page/data/' . $slug . '.php';

    if (!file_exists($data_file)) {
        return false;
    }

    $data = include $data_file;

    return is_array($data) ? $data : false;
}
