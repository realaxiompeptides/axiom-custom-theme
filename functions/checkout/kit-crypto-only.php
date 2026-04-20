<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check whether a product belongs to the kits category.
 */
function axiom_is_kit_product($product_id) {
    $product_id = (int) $product_id;
    if (!$product_id) {
        return false;
    }

    return has_term('kits', 'product_cat', $product_id);
}

/**
 * Check whether the cart currently contains kit products.
 */
function axiom_cart_contains_kits() {
    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = !empty($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
        if ($product_id && axiom_is_kit_product($product_id)) {
            return true;
        }
    }

    return false;
}

/**
 * Check whether the cart currently contains non-kit products.
 */
function axiom_cart_contains_non_kits() {
    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = !empty($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
        if ($product_id && !axiom_is_kit_product($product_id)) {
            return true;
        }
    }

    return false;
}

/**
 * Prevent mixing kits and standard products in the same cart.
 */
add_filter('woocommerce_add_to_cart_validation', 'axiom_prevent_mixing_kits_and_regular_products', 10, 3);

function axiom_prevent_mixing_kits_and_regular_products($passed, $product_id, $quantity) {
    $product_id = (int) $product_id;
    $is_kit     = axiom_is_kit_product($product_id);

    if ($is_kit && axiom_cart_contains_non_kits()) {
        wc_add_notice(
            'Kit products ship separately from our international warehouse and cannot be combined with standard products in the same cart. Please place a separate order for kit items.',
            'error'
        );
        return false;
    }

    if (!$is_kit && axiom_cart_contains_kits()) {
        wc_add_notice(
            'Standard products cannot be added to a cart containing kit items. Kits ship separately from our international warehouse, so please place a separate order.',
            'error'
        );
        return false;
    }

    return $passed;
}

/**
 * If a kit is in the cart, allow only the crypto gateway.
 *
 * Replace "crypto_gateway_id" with your real gateway ID.
 */
add_filter('woocommerce_available_payment_gateways', 'axiom_limit_kit_orders_to_crypto', 999);

function axiom_limit_kit_orders_to_crypto($available_gateways) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return $available_gateways;
    }

    if (empty($available_gateways) || !function_exists('WC') || !WC()->cart) {
        return $available_gateways;
    }

    if (!axiom_cart_contains_kits()) {
        return $available_gateways;
    }

    $allowed_gateway_ids = array(
        'crypto_gateway_id', // replace this with your real crypto gateway ID
    );

    foreach ($available_gateways as $gateway_id => $gateway) {
        if (!in_array($gateway_id, $allowed_gateway_ids, true)) {
            unset($available_gateways[$gateway_id]);
        }
    }

    return $available_gateways;
}

/**
 * Show checkout notice for kit orders.
 */
add_action('woocommerce_before_checkout_form', 'axiom_kit_checkout_notice', 8);

function axiom_kit_checkout_notice() {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }

    if (!axiom_cart_contains_kits()) {
        return;
    }

    wc_print_notice(
        'Kit orders are fulfilled separately from our international warehouse, usually take about 7-10 business days, and currently accept cryptocurrency payment only.',
        'notice'
    );
}
