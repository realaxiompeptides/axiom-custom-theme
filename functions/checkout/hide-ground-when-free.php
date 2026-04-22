<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hide Ground Advantage whenever Free Shipping is available.
 * Keep Free Shipping and Priority Mail visible.
 */
add_filter('woocommerce_package_rates', 'axiom_hide_ground_when_free_shipping_exists', 100, 2);

function axiom_hide_ground_when_free_shipping_exists($rates, $package) {
    $has_free_shipping = false;

    foreach ($rates as $rate_id => $rate) {
        if (isset($rate->method_id) && $rate->method_id === 'free_shipping') {
            $has_free_shipping = true;
            break;
        }
    }

    if (!$has_free_shipping) {
        return $rates;
    }

    foreach ($rates as $rate_id => $rate) {
        $label = isset($rate->label) ? strtolower(wp_strip_all_tags($rate->label)) : '';
        $method_id = isset($rate->method_id) ? $rate->method_id : '';

        $is_ground_advantage =
            strpos($label, 'ground advantage') !== false ||
            strpos($label, 'usps ground advantage') !== false;

        if ($method_id !== 'free_shipping' && $is_ground_advantage) {
            unset($rates[$rate_id]);
        }
    }

    return $rates;
}
