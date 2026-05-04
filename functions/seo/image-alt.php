<?php
/**
 * Axiom Image Alt SEO
 *
 * Improves missing image alt attributes safely.
 * Does not overwrite manually written alt text.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clean readable alt text.
 */
function axiom_clean_alt_text($text) {
    $text = wp_strip_all_tags($text);
    $text = preg_replace('/[-_]+/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    return $text;
}

/**
 * Generate contextual alt text.
 */
function axiom_generate_image_alt_text($attachment_id = 0) {
    $brand = 'Axiom Research';

    if (function_exists('is_product') && is_product()) {
        $title = get_the_title();

        if ($title) {
            return axiom_clean_alt_text($title . ' research-use-only compound from ' . $brand);
        }
    }

    if (function_exists('is_product_category') && is_product_category()) {
        $term = get_queried_object();

        if ($term && !is_wp_error($term) && !empty($term->name)) {
            return axiom_clean_alt_text($term->name . ' research compounds from ' . $brand);
        }
    }

    if ($attachment_id) {
        $attachment_title = get_the_title($attachment_id);

        if ($attachment_title) {
            return axiom_clean_alt_text($attachment_title . ' from ' . $brand);
        }
    }

    if (is_singular()) {
        $title = get_the_title();

        if ($title) {
            return axiom_clean_alt_text($title . ' - ' . $brand);
        }
    }

    return $brand . ' research-use-only products';
}

/**
 * Add missing alt attributes when images render.
 */
function axiom_filter_attachment_image_attributes($attr, $attachment, $size) {
    if (is_admin()) {
        return $attr;
    }

    if (!empty($attr['alt'])) {
        return $attr;
    }

    $attachment_id = 0;

    if (is_object($attachment) && !empty($attachment->ID)) {
        $attachment_id = absint($attachment->ID);
    }

    $manual_alt = $attachment_id ? get_post_meta($attachment_id, '_wp_attachment_image_alt', true) : '';

    if (!empty($manual_alt)) {
        $attr['alt'] = axiom_clean_alt_text($manual_alt);
        return $attr;
    }

    $attr['alt'] = axiom_generate_image_alt_text($attachment_id);

    return $attr;
}

add_filter('wp_get_attachment_image_attributes', 'axiom_filter_attachment_image_attributes', 10, 3);

/**
 * Add default alt text when a new image is uploaded, only if empty.
 */
function axiom_set_default_attachment_alt_on_upload($metadata, $attachment_id) {
    if (!$attachment_id) {
        return $metadata;
    }

    $existing_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

    if (!empty($existing_alt)) {
        return $metadata;
    }

    $mime = get_post_mime_type($attachment_id);

    if (strpos((string) $mime, 'image/') !== 0) {
        return $metadata;
    }

    $title = get_the_title($attachment_id);
    $alt   = $title ? axiom_clean_alt_text($title . ' from Axiom Research') : 'Axiom Research product image';

    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);

    return $metadata;
}

add_filter('wp_generate_attachment_metadata', 'axiom_set_default_attachment_alt_on_upload', 10, 2);

/**
 * Add title attributes to missing WooCommerce product images where possible.
 */
function axiom_product_image_title_attribute($attr, $attachment, $size) {
    if (is_admin()) {
        return $attr;
    }

    if (!empty($attr['title'])) {
        return $attr;
    }

    if (function_exists('is_product') && is_product()) {
        $attr['title'] = get_the_title();
    }

    return $attr;
}

add_filter('wp_get_attachment_image_attributes', 'axiom_product_image_title_attribute', 11, 3);
