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
        if ($product && $product->is_purchasable() && $product->is_in_stock()) {
            return $product;
        }
    }

    $query = new WP_Query(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        's'              => 'BAC Water',
    ));

    if ($query->have_posts()) {
        $query->the_post();
        $product = wc_get_product(get_the_ID());
        wp_reset_postdata();

        if ($product && $product->is_purchasable() && $product->is_in_stock()) {
            return $product;
        }
    }

    return null;
}

function axiom_get_cart_drawer_payload() {
    $items = array();

    if (!function_exists('WC') || !WC()->cart) {
        return array(
            'count'    => 0,
            'subtotal' => '$0.00',
            'items'    => array(),
            'upsell'   => null,
        );
    }

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = isset($cart_item['data']) ? $cart_item['data'] : null;

        if (!$product || !is_a($product, 'WC_Product')) {
            continue;
        }

        $product_id    = $product->get_id();
        $image         = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail');
        $name          = $product->get_name();
        $quantity      = (int) $cart_item['quantity'];
        $line_subtotal = WC()->cart->get_product_subtotal($product, $quantity);
        $price_html    = $product->get_price_html();
        $variant       = axiom_cart_variation_text($cart_item);

        $items[] = array(
            'key'       => $cart_item_key,
            'productId' => $product_id,
            'name'      => $name,
            'image'     => $image ? $image : wc_placeholder_img_src(),
            'quantity'  => $quantity,
            'subtotal'  => $line_subtotal,
            'priceHtml' => $price_html,
            'variant'   => $variant,
            'link'      => get_permalink($product_id),
        );
    }

    $upsell_data = null;
    $upsell = axiom_find_bac_water_upsell_product();

    if ($upsell) {
        $upsell_image = wp_get_attachment_image_url($upsell->get_image_id(), 'woocommerce_thumbnail');

        $upsell_data = array(
            'productId' => $upsell->get_id(),
            'name'      => $upsell->get_name(),
            'image'     => $upsell_image ? $upsell_image : wc_placeholder_img_src(),
            'priceHtml' => $upsell->get_price_html(),
            'link'      => get_permalink($upsell->get_id()),
        );
    }

    return array(
        'count'    => WC()->cart->get_cart_contents_count(),
        'subtotal' => WC()->cart->get_cart_subtotal(),
        'items'    => $items,
        'upsell'   => $upsell_data,
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

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(array('message' => 'Missing product ID.'));
    }

    $product = wc_get_product($product_id);

    if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
        wp_send_json_error(array('message' => 'Product unavailable.'));
    }

    if ($product->is_type('variable')) {
        wp_send_json_error(array('message' => 'Variable product requires options.'));
    }

    $added = WC()->cart->add_to_cart($product_id, 1);

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
