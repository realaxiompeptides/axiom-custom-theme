<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add stock text to variation dropdown option labels on single product pages.
 * Example:
 * 5mg — In stock
 * 10mg — Sold out
 * 20mg — Backorder
 */
add_filter('woocommerce_dropdown_variation_attribute_options_args', 'axiom_variation_dropdown_stock_labels_args', 10, 1);

function axiom_variation_dropdown_stock_labels_args($args) {
    if (empty($args['product']) || !is_a($args['product'], 'WC_Product_Variable')) {
        return $args;
    }

    $product   = $args['product'];
    $attribute = $args['attribute'];

    if (empty($attribute)) {
        return $args;
    }

    $options = $args['options'];

    if (empty($options)) {
        $attributes = $product->get_variation_attributes();
        $options = isset($attributes[$attribute]) ? $attributes[$attribute] : array();
    }

    if (empty($options) || !is_array($options)) {
        return $args;
    }

    $children = $product->get_children();
    if (empty($children)) {
        return $args;
    }

    $label_map = array();

    foreach ($children as $variation_id) {
        $variation = wc_get_product($variation_id);

        if (!$variation || !$variation->exists()) {
            continue;
        }

        $variation_attributes = $variation->get_attributes();

        if (!isset($variation_attributes[$attribute])) {
            continue;
        }

        $option_value = (string) $variation_attributes[$attribute];

        if ($option_value === '') {
            continue;
        }

        if (!$variation->is_in_stock()) {
            $stock_text = 'Sold out';
        } elseif ($variation->is_on_backorder(1)) {
            $stock_text = 'Backorder';
        } else {
            $stock_text = 'In stock';
        }

        $label_map[$option_value] = $stock_text;
    }

    if (empty($label_map)) {
        return $args;
    }

    $new_options = array();

    foreach ($options as $option) {
        $option_key = (string) $option;
        $display_name = $option_key;

        if (taxonomy_exists($attribute)) {
            $term = get_term_by('slug', $option_key, $attribute);
            if ($term && !is_wp_error($term)) {
                $display_name = $term->name;
            }
        }

        if (isset($label_map[$option_key])) {
            $new_options[$option_key] = $display_name . ' — ' . $label_map[$option_key];
        } else {
            $new_options[$option_key] = $display_name;
        }
    }

    $args['options'] = $new_options;
    return $args;
}

/**
 * Render custom labels from the keyed options above.
 */
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'axiom_render_variation_dropdown_stock_labels', 20, 2);

function axiom_render_variation_dropdown_stock_labels($html, $args) {
    if (empty($args['product']) || !is_a($args['product'], 'WC_Product_Variable')) {
        return $html;
    }

    if (empty($args['options']) || !is_array($args['options'])) {
        return $html;
    }

    $attribute          = $args['attribute'];
    $product            = $args['product'];
    $name               = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
    $id                 = $args['id'] ? $args['id'] : sanitize_title($attribute);
    $class              = $args['class'];
    $show_option_none   = (bool) $args['show_option_none'];
    $option_none_text   = $args['show_option_none'] ? $args['show_option_none'] : __('Choose an option', 'woocommerce');

    $html  = '<select id="' . esc_attr($id) . '" class="' . esc_attr($class) . '" name="' . esc_attr($name) . '" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';
    $html .= '<option value="">' . esc_html($option_none_text) . '</option>';

    foreach ($args['options'] as $value => $label) {
        $selected = sanitize_title((string) $args['selected']) === sanitize_title((string) $value) ? 'selected="selected"' : '';
        $html .= '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }

    $html .= '</select>';

    return $html;
}
