<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hide shipping rates until customer enters ZIP/postcode.
 */
add_filter('woocommerce_package_rates', 'axiom_hide_shipping_until_postcode', 100, 2);

function axiom_hide_shipping_until_postcode($rates, $package) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return $rates;
    }

    $postcode = isset($package['destination']['postcode'])
        ? trim((string) $package['destination']['postcode'])
        : '';

    if ($postcode === '') {
        return array();
    }

    return $rates;
}

/**
 * Checkout notice under shipping section.
 */
add_action('woocommerce_review_order_before_shipping', 'axiom_shipping_postcode_notice');

function axiom_shipping_postcode_notice() {
    if (!function_exists('is_checkout') || !is_checkout()) {
        return;
    }

    echo '<tr class="axiom-shipping-zip-notice">
        <td colspan="2" style="padding:12px 0;color:#475569;font-size:14px;line-height:1.5;">
            Enter your shipping ZIP code to view accurate USPS shipping rates, including Ground Advantage.
        </td>
    </tr>';
}
