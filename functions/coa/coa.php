<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_coa_assets() {
    if (
        (function_exists('is_page_template') && is_page_template('coa-page/coa-template.php')) ||
        is_page('coa') ||
        is_page('coas')
    ) {
        $theme_uri = get_template_directory_uri();

        wp_enqueue_style(
            'axiom-coa',
            $theme_uri . '/assets/css/coa/coa.css',
            array('axiom-base'),
            '1.0'
        );

        wp_enqueue_script(
            'axiom-coa',
            $theme_uri . '/assets/js/coa/coa.js',
            array(),
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_coa_assets', 20);

function axiom_get_product_coa_data($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return array(
            'status' => 'not_ready',
            'label'  => 'Janoshik Tested',
            'image'  => '',
            'pdf'    => '',
        );
    }

    return array(
        'status' => get_post_meta($product->get_id(), '_axiom_coa_status', true) ?: 'not_ready',
        'label'  => get_post_meta($product->get_id(), '_axiom_coa_label', true) ?: 'Janoshik Tested',
        'image'  => get_post_meta($product->get_id(), '_axiom_coa_image', true) ?: '',
        'pdf'    => get_post_meta($product->get_id(), '_axiom_coa_pdf', true) ?: '',
    );
}

function axiom_get_variation_coa_data($variation_id) {
    $variation_id = absint($variation_id);

    return array(
        'status' => get_post_meta($variation_id, '_axiom_variation_coa_status', true) ?: 'not_ready',
        'label'  => get_post_meta($variation_id, '_axiom_variation_coa_label', true) ?: 'Janoshik Tested',
        'image'  => get_post_meta($variation_id, '_axiom_variation_coa_image', true) ?: '',
        'pdf'    => get_post_meta($variation_id, '_axiom_variation_coa_pdf', true) ?: '',
    );
}

function axiom_get_variation_display_label($variation) {
    if (!$variation || !is_a($variation, 'WC_Product_Variation')) {
        return '';
    }

    $attributes = $variation->get_attributes();

    if (empty($attributes) || !is_array($attributes)) {
        return '';
    }

    $parts = array();

    foreach ($attributes as $taxonomy => $term_slug) {
        if (empty($term_slug)) {
            continue;
        }

        $taxonomy_name = str_replace('attribute_', '', $taxonomy);
        $label = wc_attribute_label($taxonomy_name);

        if (taxonomy_exists($taxonomy_name)) {
            $term = get_term_by('slug', $term_slug, $taxonomy_name);
            $value = ($term && !is_wp_error($term)) ? $term->name : $term_slug;
        } else {
            $value = ucwords(str_replace(array('-', '_'), ' ', $term_slug));
        }

        $parts[] = $label . ': ' . $value;
    }

    return implode(' • ', $parts);
}
