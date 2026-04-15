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

/**
 * Product-level COA fields:
 * - _axiom_coa_status
 * - _axiom_coa_label
 * - _axiom_coa_image
 * - _axiom_coa_pdf
 *
 * Variation-level COA fields:
 * - _axiom_variation_coa_status
 * - _axiom_variation_coa_label
 * - _axiom_variation_coa_image
 * - _axiom_variation_coa_pdf
 */

function axiom_get_product_coa_data($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return null;
    }

    return array(
        'status' => get_post_meta($product->get_id(), '_axiom_coa_status', true),
        'label'  => get_post_meta($product->get_id(), '_axiom_coa_label', true),
        'image'  => get_post_meta($product->get_id(), '_axiom_coa_image', true),
        'pdf'    => get_post_meta($product->get_id(), '_axiom_coa_pdf', true),
    );
}

function axiom_get_variation_coa_data($variation_id) {
    return array(
        'status' => get_post_meta($variation_id, '_axiom_variation_coa_status', true),
        'label'  => get_post_meta($variation_id, '_axiom_variation_coa_label', true),
        'image'  => get_post_meta($variation_id, '_axiom_variation_coa_image', true),
        'pdf'    => get_post_meta($variation_id, '_axiom_variation_coa_pdf', true),
    );
}

function axiom_get_variation_display_label($variation) {
    if (!$variation || !is_a($variation, 'WC_Product_Variation')) {
        return '';
    }

    $attributes = $variation->get_attributes();
    if (empty($attributes)) {
        return '';
    }

    $parts = array();

    foreach ($attributes as $taxonomy => $term_slug) {
        if (!$term_slug) {
            continue;
        }

        $label = wc_attribute_label($taxonomy);
        $value = $term_slug;

        if (taxonomy_exists($taxonomy)) {
            $term = get_term_by('slug', $term_slug, $taxonomy);
            if ($term && !is_wp_error($term)) {
                $value = $term->name;
            }
        } else {
            $value = ucwords(str_replace(array('-', '_'), ' ', $term_slug));
        }

        $parts[] = $label . ': ' . $value;
    }

    return implode(' • ', $parts);
}
