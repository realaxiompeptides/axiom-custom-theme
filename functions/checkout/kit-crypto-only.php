<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Kit checkout/payment restrictions disabled.
 *
 * Kit products now use:
 * - normal product page
 * - normal cart
 * - normal checkout
 * - normal payment methods
 *
 * Kit products can still be hidden from the COA page separately.
 */

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
