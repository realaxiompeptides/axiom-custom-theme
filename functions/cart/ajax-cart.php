<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_cart_variation_text($cart_item) {
    if (empty($cart_item['variation']) || !is_array($cart_item['variation'])) {
        return '';
    }

    $parts = array();

    foreach ($cart_item['variation'] as $key => $value) {
        if (!$value) {
            continue;
        }

        $label = wc_attribute_label(str_replace('attribute_', '', $key));
        $parts[] = $label . ': ' . $value;
    }

    return implode(' • ', $parts);
}

function axiom_product_in_cart_by_product_or_parent($product_id) {
    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $cart_product_id   = isset($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
        $cart_variation_id = isset($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : 0;

        if ($cart_product_id === (int) $product_id || $cart_variation_id === (int) $product_id) {
            return true;
        }
    }

    return false;
}

function axiom_get_single_variation_for_upsell($product) {
    if (!$product || !$product->is_type('variable')) {
        return null;
    }

    $available_variations = $product->get_available_variations();

    if (empty($available_variations) || !is_array($available_variations)) {
        return null;
    }

    if (count($available_variations) !== 1) {
        return null;
    }

    $variation_data = reset($available_variations);
    if (empty($variation_data['variation_id'])) {
        return null;
    }

    $variation = wc_get_product($variation_data['variation_id']);
    if (
        !$variation ||
        !$variation->is_purchasable() ||
        (!$variation->is_in_stock() && !$variation->backorders_allowed())
    ) {
        return null;
    }

    return array(
        'variation_id' => (int) $variation->get_id(),
        'attributes'   => isset($variation_data['attributes']) && is_array($variation_data['attributes'])
            ? $variation_data['attributes']
            : array(),
    );
}

function axiom_find_bac_water_upsell_product() {
    $candidate_slugs = array(
        'bac-water-10ml',
        'bac-water-10mL',
        'bac-water',
    );

    foreach ($candidate_slugs as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'product');
        if (!$page) {
            continue;
        }

        $product = wc_get_product($page->ID);

        if (!$product || !$product->is_purchasable()) {
            continue;
        }

        if ($product->is_type('variable')) {
            $single_variation = axiom_get_single_variation_for_upsell($product);
            if ($single_variation) {
                return array(
                    'product'      => $product,
                    'variation_id' => $single_variation['variation_id'],
                    'attributes'   => $single_variation['attributes'],
                );
            }
        } elseif ($product->is_in_stock() || $product->backorders_allowed()) {
            return array(
                'product'      => $product,
                'variation_id' => 0,
                'attributes'   => array(),
            );
        }
    }

    $query = new WP_Query(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        's'              => 'BAC Water',
    ));

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());

            if (!$product || !$product->is_purchasable()) {
                continue;
            }

            if ($product->is_type('variable')) {
                $single_variation = axiom_get_single_variation_for_upsell($product);
                if ($single_variation) {
                    wp_reset_postdata();
                    return array(
                        'product'      => $product,
                        'variation_id' => $single_variation['variation_id'],
                        'attributes'   => $single_variation['attributes'],
                    );
                }
            } elseif ($product->is_in_stock() || $product->backorders_allowed()) {
                wp_reset_postdata();
                return array(
                    'product'      => $product,
                    'variation_id' => 0,
                    'attributes'   => array(),
                );
            }
        }

        wp_reset_postdata();
    }

    return null;
}

function axiom_get_cart_drawer_payload() {
    $items = array();

    if (!function_exists('WC') || !WC()->cart) {
        return array(
            'count'                => 0,
            'subtotal'             => '$0.00',
            'shippingLabel'        => 'Calculated at checkout',
            'items'                => array(),
            'upsell'               => null,
            'freeShippingGoalHtml' => function_exists('axiom_get_cart_drawer_free_shipping_goal_html')
                ? axiom_get_cart_drawer_free_shipping_goal_html()
                : '',
        );
    }

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = isset($cart_item['data']) ? $cart_item['data'] : null;

        if (!$product || !is_a($product, 'WC_Product')) {
            continue;
        }

        $display_product_id = isset($cart_item['product_id']) ? (int) $cart_item['product_id'] : $product->get_id();
        $image              = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail');
        $quantity           = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
        $line_subtotal      = WC()->cart->get_product_subtotal($product, $quantity);
        $variant            = axiom_cart_variation_text($cart_item);

        $items[] = array(
            'key'       => $cart_item_key,
            'productId' => $display_product_id,
            'name'      => $product->get_name(),
            'image'     => $image ? $image : wc_placeholder_img_src(),
            'quantity'  => $quantity,
            'subtotal'  => $line_subtotal,
            'priceHtml' => $product->get_price_html(),
            'variant'   => $variant,
            'link'      => get_permalink($display_product_id),
        );
    }

    $upsell_data = null;
    $upsell_wrap = axiom_find_bac_water_upsell_product();

    if ($upsell_wrap && !empty($upsell_wrap['product']) && is_a($upsell_wrap['product'], 'WC_Product')) {
        $upsell       = $upsell_wrap['product'];
        $variation_id = !empty($upsell_wrap['variation_id']) ? (int) $upsell_wrap['variation_id'] : 0;
        $attributes   = !empty($upsell_wrap['attributes']) && is_array($upsell_wrap['attributes'])
            ? $upsell_wrap['attributes']
            : array();

        if (
            !axiom_product_in_cart_by_product_or_parent($upsell->get_id()) &&
            (!$variation_id || !axiom_product_in_cart_by_product_or_parent($variation_id))
        ) {
            $upsell_image = wp_get_attachment_image_url($upsell->get_image_id(), 'woocommerce_thumbnail');
            $upsell_price_product = $variation_id ? wc_get_product($variation_id) : $upsell;

            $upsell_data = array(
                'productId'   => $upsell->get_id(),
                'variationId' => $variation_id,
                'attributes'  => $attributes,
                'name'        => $upsell->get_name(),
                'image'       => $upsell_image ? $upsell_image : wc_placeholder_img_src(),
                'priceHtml'   => $upsell_price_product ? $upsell_price_product->get_price_html() : $upsell->get_price_html(),
                'link'        => get_permalink($upsell->get_id()),
            );
        }
    }

    return array(
        'count'                => WC()->cart->get_cart_contents_count(),
        'subtotal'             => WC()->cart->get_cart_subtotal(),
        'shippingLabel'        => 'Calculated at checkout',
        'items'                => $items,
        'upsell'               => $upsell_data,
        'freeShippingGoalHtml' => function_exists('axiom_get_cart_drawer_free_shipping_goal_html')
            ? axiom_get_cart_drawer_free_shipping_goal_html()
            : '',
    );
}

function axiom_get_cart_drawer_data() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    wp_send_json_success(axiom_get_cart_drawer_payload());
}
add_action('wp_ajax_axiom_get_cart_drawer', 'axiom_get_cart_drawer_data');
add_action('wp_ajax_nopriv_axiom_get_cart_drawer', 'axiom_get_cart_drawer_data');

function axiom_update_cart_item_quantity() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $cart_key = isset($_POST['cart_key']) ? wc_clean(wp_unslash($_POST['cart_key'])) : '';
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;

    if (!$cart_key) {
        wp_send_json_error(array('message' => 'Missing cart key.'));
    }

    if ($quantity <= 0) {
        WC()->cart->remove_cart_item($cart_key);
    } else {
        WC()->cart->set_quantity($cart_key, $quantity, true);
    }

    WC()->cart->calculate_totals();

    wp_send_json_success(axiom_get_cart_drawer_payload());
}
add_action('wp_ajax_axiom_update_cart_item_quantity', 'axiom_update_cart_item_quantity');
add_action('wp_ajax_nopriv_axiom_update_cart_item_quantity', 'axiom_update_cart_item_quantity');

function axiom_remove_cart_item() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $cart_key = isset($_POST['cart_key']) ? wc_clean(wp_unslash($_POST['cart_key'])) : '';

    if (!$cart_key) {
        wp_send_json_error(array('message' => 'Missing cart key.'));
    }

    WC()->cart->remove_cart_item($cart_key);
    WC()->cart->calculate_totals();

    wp_send_json_success(axiom_get_cart_drawer_payload());
}
add_action('wp_ajax_axiom_remove_cart_item', 'axiom_remove_cart_item');
add_action('wp_ajax_nopriv_axiom_remove_cart_item', 'axiom_remove_cart_item');

function axiom_add_simple_product_to_cart() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $product_id   = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(array('message' => 'Missing product ID.'));
    }

    $product = wc_get_product($product_id);

    if (!$product || !$product->is_purchasable()) {
        wp_send_json_error(array('message' => 'Product unavailable.'));
    }

    $added = false;

    if ($variation_id) {
        $variation = wc_get_product($variation_id);

        if (!$variation || !$variation->is_purchasable()) {
            wp_send_json_error(array('message' => 'Variation unavailable.'));
        }

        if (!$variation->is_in_stock() && !$variation->backorders_allowed()) {
            wp_send_json_error(array('message' => 'Variation unavailable.'));
        }

        $variation_data = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'attribute_') === 0) {
                $variation_data[wc_clean(wp_unslash($key))] = wc_clean(wp_unslash($value));
            }
        }

        $added = WC()->cart->add_to_cart($product_id, 1, $variation_id, $variation_data);
    } else {
        if ($product->is_type('variable')) {
            wp_send_json_error(array('message' => 'Variable product requires options.'));
        }

        if (!$product->is_in_stock() && !$product->backorders_allowed()) {
            wp_send_json_error(array('message' => 'Product unavailable.'));
        }

        $added = WC()->cart->add_to_cart($product_id, 1);
    }

    if (!$added) {
        wp_send_json_error(array('message' => 'Could not add product.'));
    }

    WC()->cart->calculate_totals();

    wp_send_json_success(axiom_get_cart_drawer_payload());
}
add_action('wp_ajax_axiom_add_simple_product_to_cart', 'axiom_add_simple_product_to_cart');
add_action('wp_ajax_nopriv_axiom_add_simple_product_to_cart', 'axiom_add_simple_product_to_cart');

function axiom_add_product_from_product_page() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $product_id   = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $quantity     = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

    if (!$product_id || $quantity < 1) {
        wp_send_json_error(array('message' => 'Invalid product data.'));
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => 'Product not found.'));
    }

    $added = false;

    if ($product->is_type('variable')) {
        if (!$variation_id) {
            wp_send_json_error(array('message' => 'Please select a variation.'));
        }

        $variation = wc_get_product($variation_id);
        if (!$variation || !$variation->is_purchasable()) {
            wp_send_json_error(array('message' => 'Variation unavailable.'));
        }

        if (!$variation->is_in_stock() && !$variation->backorders_allowed()) {
            wp_send_json_error(array('message' => 'This variation is out of stock.'));
        }

        if ($variation->managing_stock() && !$variation->backorders_allowed()) {
            $stock_qty = (int) $variation->get_stock_quantity();

            if ($stock_qty < 1) {
                wp_send_json_error(array('message' => 'This variation is out of stock.'));
            }

            if ($quantity > $stock_qty) {
                wp_send_json_error(array(
                    'message' => sprintf('You can only add up to %d of this variation.', $stock_qty),
                ));
            }
        }

        $variation_data = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'attribute_') === 0) {
                $variation_data[wc_clean(wp_unslash($key))] = wc_clean(wp_unslash($value));
            }
        }

        $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data);
    } else {
        if (!$product->is_purchasable()) {
            wp_send_json_error(array('message' => 'Product unavailable.'));
        }

        if (!$product->is_in_stock() && !$product->backorders_allowed()) {
            wp_send_json_error(array('message' => 'This product is out of stock.'));
        }

        if ($product->managing_stock() && !$product->backorders_allowed()) {
            $stock_qty = (int) $product->get_stock_quantity();

            if ($stock_qty < 1) {
                wp_send_json_error(array('message' => 'This product is out of stock.'));
            }

            if ($quantity > $stock_qty) {
                wp_send_json_error(array(
                    'message' => sprintf('You can only add up to %d of this product.', $stock_qty),
                ));
            }
        }

        $added = WC()->cart->add_to_cart($product_id, $quantity);
    }

    if (!$added) {
        wp_send_json_error(array('message' => 'Could not add product to cart.'));
    }

    WC()->cart->calculate_totals();

    wp_send_json_success(array(
        'message' => 'Added to cart.',
        'cart'    => axiom_get_cart_drawer_payload(),
    ));
}
add_action('wp_ajax_axiom_add_product_from_product_page', 'axiom_add_product_from_product_page');
add_action('wp_ajax_nopriv_axiom_add_product_from_product_page', 'axiom_add_product_from_product_page');

function axiom_apply_cart_coupon() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $coupon_code = isset($_POST['coupon_code'])
        ? wc_format_coupon_code(wc_clean(wp_unslash($_POST['coupon_code'])))
        : '';

    if (!$coupon_code) {
        wp_send_json_error(array('message' => 'Please enter a discount code.'));
    }

    if (WC()->cart->has_discount($coupon_code)) {
        wp_send_json_success(array(
            'message' => 'Discount already applied.',
            'cart'    => axiom_get_cart_drawer_payload(),
        ));
    }

    $coupon = new WC_Coupon($coupon_code);

    if (!$coupon || !$coupon->get_id()) {
        wp_send_json_error(array('message' => 'Invalid discount code.'));
    }

    $applied = WC()->cart->apply_coupon($coupon_code);

    if (!$applied) {
        $notices = wc_get_notices('error');
        wc_clear_notices();

        $message = 'Discount code could not be applied.';

        if (!empty($notices[0]['notice'])) {
            $message = wp_strip_all_tags($notices[0]['notice']);
        }

        wp_send_json_error(array('message' => $message));
    }

    WC()->cart->calculate_totals();

    wc_clear_notices();

    wp_send_json_success(array(
        'message' => 'Discount applied.',
        'cart'    => axiom_get_cart_drawer_payload(),
    ));
}
add_action('wp_ajax_axiom_apply_cart_coupon', 'axiom_apply_cart_coupon');
add_action('wp_ajax_nopriv_axiom_apply_cart_coupon', 'axiom_apply_cart_coupon');
