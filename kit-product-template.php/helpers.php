<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_kit_template_get_competitor_fields() {
    return array(
        'neuro' => 'Neuro Labs',
        'onyx'  => 'Onyx Research',
        'core'  => 'Core Peptides',
        'limitless' => 'Limitless Biotech',
    );
}

function axiom_is_kit_product($product = null) {
    if (!$product && function_exists('wc_get_product') && is_singular('product')) {
        global $post;
        $product = $post ? wc_get_product($post->ID) : null;
    }

    if (!$product instanceof WC_Product) {
        return false;
    }

    $product_id = $product->get_id();
    $forced     = get_post_meta($product_id, '_axiom_force_kit_template', true);

    if ($forced === 'yes') {
        return true;
    }

    return has_term('kits', 'product_cat', $product_id);
}

function axiom_get_kit_template_data($product) {
    if (!$product instanceof WC_Product || !axiom_is_kit_product($product)) {
        return array();
    }

    $product_id     = $product->get_id();
    $product_name   = $product->get_name();
    $kit_price      = (float) $product->get_price();
    $kit_price_html = $product->get_price_html();

    $vial_count = (int) get_post_meta($product_id, '_axiom_kit_vial_count', true);
    if ($vial_count < 1) {
        $vial_count = 10;
    }

    $single_product_id = (int) get_post_meta($product_id, '_axiom_kit_single_product_id', true);
    $single_product    = $single_product_id ? wc_get_product($single_product_id) : null;

    $single_price = 0.0;
    $single_title = '';
    if ($single_product instanceof WC_Product) {
        $single_price = (float) $single_product->get_price();
        $single_title = $single_product->get_name();
    }

    $full_single_total = $single_price > 0 ? ($single_price * $vial_count) : 0.0;
    $save_vs_singles   = ($full_single_total > $kit_price) ? ($full_single_total - $kit_price) : 0.0;
    $save_percent      = ($full_single_total > 0 && $save_vs_singles > 0)
        ? round(($save_vs_singles / $full_single_total) * 100)
        : 0;

    $per_vial_price = ($kit_price > 0 && $vial_count > 0) ? ($kit_price / $vial_count) : 0.0;

    $microcopy = trim((string) get_post_meta($product_id, '_axiom_kit_microcopy', true));
    if ($microcopy === '') {
        $microcopy = sprintf(
            'Get %1$d lab-tested vials in one bundle with a lower per-vial price built for repeat research ordering.',
            $vial_count
        );
    }

    $comparison_rows = array();
    foreach (axiom_kit_template_get_competitor_fields() as $key => $label) {
        $competitor_price = (float) get_post_meta($product_id, '_axiom_competitor_' . $key . '_price', true);
        if ($competitor_price <= 0) {
            continue;
        }

        $difference = $competitor_price - $kit_price;
        $comparison_rows[] = array(
            'name'            => $label,
            'price'           => $competitor_price,
            'price_html'      => wc_price($competitor_price),
            'difference'      => $difference,
            'difference_html' => $difference > 0 ? wc_price($difference) : wc_price(0),
            'is_better'       => $difference > 0,
        );
    }

    return array(
        'product_id'             => $product_id,
        'product_name'           => $product_name,
        'kit_price'              => $kit_price,
        'kit_price_html'         => $kit_price_html,
        'vial_count'             => $vial_count,
        'per_vial_price'         => $per_vial_price,
        'per_vial_price_html'    => wc_price($per_vial_price),
        'single_product_id'      => $single_product_id,
        'single_product_name'    => $single_title,
        'full_single_total'      => $full_single_total,
        'full_single_total_html' => $full_single_total > 0 ? wc_price($full_single_total) : '',
        'save_vs_singles'        => $save_vs_singles,
        'save_vs_singles_html'   => $save_vs_singles > 0 ? wc_price($save_vs_singles) : '',
        'save_percent'           => $save_percent,
        'microcopy'              => $microcopy,
        'comparison_rows'        => $comparison_rows,
        'has_competitor_rows'    => !empty($comparison_rows),
    );
}

function axiom_render_kit_product_template($product = null) {
    if (!$product && function_exists('wc_get_product') && is_singular('product')) {
        global $post;
        $product = $post ? wc_get_product($post->ID) : null;
    }

    if (!$product instanceof WC_Product) {
        return;
    }

    $kit_data = axiom_get_kit_template_data($product);
    if (empty($kit_data)) {
        return;
    }

    $template_file = get_template_directory() . '/kit-product-template/template.php';
    if (file_exists($template_file)) {
        include $template_file;
    }
}
