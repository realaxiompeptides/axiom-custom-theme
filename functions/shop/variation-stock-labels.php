<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Append stock status text to WooCommerce variation dropdown option labels.
 */
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'axiom_add_stock_to_variation_dropdown', 10, 2);

function axiom_add_stock_to_variation_dropdown($html, $args) {
    if (empty($args['product']) || !is_a($args['product'], 'WC_Product_Variable')) {
        return $html;
    }

    $product   = $args['product'];
    $attribute = $args['attribute'];

    $available_variations = $product->get_available_variations();
    if (empty($available_variations)) {
        return $html;
    }

    $stock_map = array();

    foreach ($available_variations as $variation_data) {
        if (empty($variation_data['attributes'])) {
            continue;
        }

        $variation_id = $variation_data['variation_id'];
        $variation    = wc_get_product($variation_id);

        if (!$variation) {
            continue;
        }

        $attribute_key = 'attribute_' . $attribute;
        if (empty($variation_data['attributes'][$attribute_key])) {
            continue;
        }

        $term_slug = $variation_data['attributes'][$attribute_key];

        if (!$variation->is_in_stock()) {
            $stock_text = 'Out of stock';
        } elseif ($variation->managing_stock() && $variation->is_on_backorder(1)) {
            $stock_text = 'Backorder';
        } else {
            $stock_text = 'In stock';
        }

        $stock_map[$term_slug] = $stock_text;
    }

    if (empty($stock_map)) {
        return $html;
    }

    foreach ($stock_map as $term_slug => $stock_text) {
        $term = get_term_by('slug', $term_slug, str_replace('pa_', '', $attribute));

        if ($term && !is_wp_error($term)) {
            $original = '>' . esc_html($term->name) . '<';
            $updated  = '>' . esc_html($term->name . ' — ' . $stock_text) . '<';
            $html = str_replace($original, $updated, $html);
        } else {
            $original = '>' . esc_html($term_slug) . '<';
            $updated  = '>' . esc_html($term_slug . ' — ' . $stock_text) . '<';
            $html = str_replace($original, $updated, $html);
        }
    }

    return $html;
}
