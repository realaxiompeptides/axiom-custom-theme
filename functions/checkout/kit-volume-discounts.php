<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_before_calculate_totals', 'axiom_apply_kit_volume_discounts', 20);

function axiom_apply_kit_volume_discounts($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (!$cart || !is_a($cart, 'WC_Cart')) {
        return;
    }

    $eligible_kit_subtotal = 0.0;

    foreach ($cart->get_cart() as $cart_item) {
        if (empty($cart_item['data']) || !is_a($cart_item['data'], 'WC_Product')) {
            continue;
        }

        $cart_product = $cart_item['data'];
        $lookup_id    = !empty($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : (int) $cart_product->get_id();
        $base_product = wc_get_product($lookup_id);

        if (!$base_product) {
            continue;
        }

        if (!has_term('kits', 'product_cat', $base_product->get_id())) {
            continue;
        }

        $base_price = (float) $base_product->get_price();
        $qty        = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0;

        $eligible_kit_subtotal += ($base_price * $qty);
    }

    $discount_percent = 0;

    if ($eligible_kit_subtotal >= 1000) {
        $discount_percent = 20;
    } elseif ($eligible_kit_subtotal >= 500) {
        $discount_percent = 10;
    } elseif ($eligible_kit_subtotal >= 250) {
        $discount_percent = 5;
    }

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (empty($cart_item['data']) || !is_a($cart_item['data'], 'WC_Product')) {
            continue;
        }

        $cart_product = $cart_item['data'];
        $lookup_id    = !empty($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : (int) $cart_product->get_id();
        $base_product = wc_get_product($lookup_id);

        if (!$base_product) {
            continue;
        }

        if (!has_term('kits', 'product_cat', $base_product->get_id())) {
            continue;
        }

        $base_price = (float) $base_product->get_price();

        if ($discount_percent > 0) {
            $discounted_price = $base_price * (1 - ($discount_percent / 100));
            $cart_item['data']->set_price($discounted_price);
        } else {
            $cart_item['data']->set_price($base_price);
        }
    }
}
