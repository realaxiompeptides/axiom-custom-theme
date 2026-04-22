<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hide only Ground Advantage when Free Shipping is available.
 * Keep Free Shipping and Priority Mail visible.
 */
add_filter('woocommerce_package_rates', 'axiom_hide_only_ground_advantage_when_free_exists', 9999, 2);

function axiom_hide_only_ground_advantage_when_free_exists($rates, $package) {
    $has_free_shipping = false;

    foreach ($rates as $rate_id => $rate) {
        if (!empty($rate->method_id) && $rate->method_id === 'free_shipping') {
            $has_free_shipping = true;
            break;
        }
    }

    if (!$has_free_shipping) {
        return $rates;
    }

    foreach ($rates as $rate_id => $rate) {
        $label = !empty($rate->label) ? strtolower(wp_strip_all_tags($rate->label)) : '';
        $method_id = !empty($rate->method_id) ? $rate->method_id : '';

        // Never remove free shipping itself
        if ($method_id === 'free_shipping') {
            continue;
        }

        // Remove only Ground Advantage
        $is_ground_advantage =
            strpos($label, 'ground advantage') !== false ||
            strpos($label, 'usps ground advantage') !== false;

        if ($is_ground_advantage) {
            unset($rates[$rate_id]);
        }
    }

    return $rates;
}
