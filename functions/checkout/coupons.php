<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==========================================================
 * Axiom Kit Coupon Block
 * Discount codes cannot apply when cart contains kit products.
 * ==========================================================
 */

function axiom_coupon_cart_contains_kit_product() {
    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id   = !empty($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
        $variation_id = !empty($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : 0;

        if ($product_id && has_term(array('kits', 'kit'), 'product_cat', $product_id)) {
            return true;
        }

        if ($variation_id && has_term(array('kits', 'kit'), 'product_cat', $variation_id)) {
            return true;
        }
    }

    return false;
}

add_filter('woocommerce_coupon_is_valid', 'axiom_disable_coupons_for_kit_products', 20, 2);

function axiom_disable_coupons_for_kit_products($valid, $coupon) {
    if (axiom_coupon_cart_contains_kit_product()) {
        return false;
    }

    return $valid;
}

add_filter('woocommerce_coupon_error', 'axiom_kit_coupon_error_message', 30, 3);

function axiom_kit_coupon_error_message($message, $error_code, $coupon) {
    if (axiom_coupon_cart_contains_kit_product()) {
        return 'Discount codes cannot be applied to kit products.';
    }

    return $message;
}

/**
 * ==========================================================
 * Axiom Apply Coupon AJAX
 * Used by custom checkout/cart coupon UI.
 * ==========================================================
 */
function axiom_apply_coupon() {
    check_ajax_referer('axiom_apply_coupon', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array(
            'message' => 'Cart unavailable.',
        ));
    }

    if (axiom_coupon_cart_contains_kit_product()) {
        wp_send_json_error(array(
            'message' => 'Discount codes cannot be applied to kit products.',
        ));
    }

    $coupon_code = isset($_POST['coupon_code'])
        ? wc_format_coupon_code(wc_clean(wp_unslash($_POST['coupon_code'])))
        : '';

    if (!$coupon_code) {
        wp_send_json_error(array(
            'message' => 'Please enter a discount code.',
        ));
    }

    axiom_sync_live_checkout_email_to_customer();

    if (WC()->cart->has_discount($coupon_code)) {
        wp_send_json_success(array(
            'message'     => 'Discount already applied.',
            'coupon_code' => $coupon_code,
        ));
    }

    wc_clear_notices();

    $applied = WC()->cart->apply_coupon($coupon_code);

    if (is_wp_error($applied)) {
        wp_send_json_error(array(
            'message' => $applied->get_error_message(),
        ));
    }

    if (!$applied) {
        $notices = wc_get_notices('error');
        $message = 'Discount code not valid.';

        if (!empty($notices) && !empty($notices[0]['notice'])) {
            $message = wp_strip_all_tags($notices[0]['notice']);
        }

        wc_clear_notices();

        wp_send_json_error(array(
            'message' => $message,
        ));
    }

    WC()->cart->calculate_totals();

    $notices = wc_get_notices('success');
    $message = 'Discount applied.';

    if (!empty($notices) && !empty($notices[0]['notice'])) {
        $message = wp_strip_all_tags($notices[0]['notice']);
    }

    wc_clear_notices();

    wp_send_json_success(array(
        'message'     => $message,
        'coupon_code' => $coupon_code,
    ));
}
add_action('wp_ajax_axiom_apply_coupon', 'axiom_apply_coupon');
add_action('wp_ajax_nopriv_axiom_apply_coupon', 'axiom_apply_coupon');

/**
 * ==========================================================
 * Axiom Popup Coupon Email Validation Fix
 * ==========================================================
 */

function axiom_popup_coupon_code_is_generated_coupon($coupon_code) {
    $coupon_code = strtoupper(trim((string) $coupon_code));

    return (
        strpos($coupon_code, 'WELCOME10-') === 0 ||
        strpos($coupon_code, 'WELCOME15-') === 0
    );
}

function axiom_get_coupon_email_restriction($coupon) {
    if (!$coupon instanceof WC_Coupon) {
        return '';
    }

    $emails = $coupon->get_email_restrictions();

    if (empty($emails) || !is_array($emails)) {
        return '';
    }

    $email = sanitize_email($emails[0]);

    return is_email($email) ? strtolower($email) : '';
}

function axiom_get_live_checkout_email_for_coupon() {
    $email = '';

    if (!empty($_POST['axiom_billing_email'])) {
        $email = sanitize_email(wp_unslash($_POST['axiom_billing_email']));
    }

    if (!$email && !empty($_POST['billing_email'])) {
        $email = sanitize_email(wp_unslash($_POST['billing_email']));
    }

    if (!$email && !empty($_POST['post_data'])) {
        parse_str(wp_unslash($_POST['post_data']), $posted_data);

        if (!empty($posted_data['billing_email'])) {
            $email = sanitize_email($posted_data['billing_email']);
        }
    }

    if (!$email && function_exists('WC') && WC()->session) {
        $session_email = WC()->session->get('axiom_live_checkout_email');

        if ($session_email) {
            $email = sanitize_email($session_email);
        }
    }

    if (!$email && function_exists('WC') && WC()->customer) {
        $email = sanitize_email(WC()->customer->get_billing_email());
    }

    return is_email($email) ? strtolower($email) : '';
}

function axiom_sync_live_checkout_email_to_customer() {
    if (!function_exists('WC')) {
        return '';
    }

    $email = axiom_get_live_checkout_email_for_coupon();

    if (!$email) {
        return '';
    }

    if (WC()->customer) {
        WC()->customer->set_billing_email($email);
        WC()->customer->save();
    }

    if (WC()->session) {
        WC()->session->set('axiom_live_checkout_email', $email);
    }

    return $email;
}

add_action('init', function () {
    if (
        !empty($_POST['coupon_code']) ||
        !empty($_POST['coupon']) ||
        !empty($_POST['axiom_billing_email']) ||
        !empty($_POST['billing_email'])
    ) {
        axiom_sync_live_checkout_email_to_customer();
    }
}, 1);

add_action('woocommerce_before_calculate_totals', 'axiom_sync_checkout_email_before_coupon_validation', 1);

function axiom_sync_checkout_email_before_coupon_validation() {
    axiom_sync_live_checkout_email_to_customer();
}

add_action('woocommerce_checkout_update_order_review', 'axiom_save_checkout_email_on_order_review_update', 1);

function axiom_save_checkout_email_on_order_review_update($post_data) {
    if (!function_exists('WC') || !WC()->customer) {
        return;
    }

    parse_str($post_data, $data);

    if (empty($data['billing_email'])) {
        return;
    }

    $email = sanitize_email($data['billing_email']);

    if (!is_email($email)) {
        return;
    }

    WC()->customer->set_billing_email(strtolower($email));
    WC()->customer->save();

    if (WC()->session) {
        WC()->session->set('axiom_live_checkout_email', strtolower($email));
    }
}

add_filter('woocommerce_coupon_is_valid_for_customer', 'axiom_validate_popup_coupon_with_live_checkout_email', 999, 3);

function axiom_validate_popup_coupon_with_live_checkout_email($valid, $coupon, $customer) {
    if (!$coupon instanceof WC_Coupon) {
        return $valid;
    }

    if (axiom_coupon_cart_contains_kit_product()) {
        return false;
    }

    $coupon_code = $coupon->get_code();

    if (!axiom_popup_coupon_code_is_generated_coupon($coupon_code)) {
        return $valid;
    }

    $restricted_email = axiom_get_coupon_email_restriction($coupon);

    if (!$restricted_email) {
        return $valid;
    }

    $checkout_email = axiom_sync_live_checkout_email_to_customer();

    if (!$checkout_email && function_exists('WC') && WC()->session) {
        $checkout_email = WC()->session->get('axiom_live_checkout_email');
        $checkout_email = $checkout_email ? strtolower(sanitize_email($checkout_email)) : '';
    }

    if (!$checkout_email && function_exists('WC') && WC()->customer) {
        $checkout_email = WC()->customer->get_billing_email();
        $checkout_email = $checkout_email ? strtolower(sanitize_email($checkout_email)) : '';
    }

    if (!$checkout_email) {
        return false;
    }

    return strtolower($checkout_email) === strtolower($restricted_email);
}

add_filter('woocommerce_checkout_get_value', 'axiom_prefill_checkout_email_from_popup_coupon', 20, 2);

function axiom_prefill_checkout_email_from_popup_coupon($value, $input) {
    if ($input !== 'billing_email') {
        return $value;
    }

    if (!empty($value)) {
        return $value;
    }

    if (!function_exists('WC') || !WC()->session) {
        return $value;
    }

    $session_email = WC()->session->get('axiom_live_checkout_email');

    if ($session_email && is_email($session_email)) {
        return sanitize_email($session_email);
    }

    return $value;
}

add_filter('woocommerce_coupon_error', 'axiom_clean_popup_coupon_email_error', 20, 3);

function axiom_clean_popup_coupon_email_error($message, $error_code, $coupon) {
    if (axiom_coupon_cart_contains_kit_product()) {
        return 'Discount codes cannot be applied to kit products.';
    }

    if (!$coupon instanceof WC_Coupon) {
        return $message;
    }

    $coupon_code = $coupon->get_code();

    if (!axiom_popup_coupon_code_is_generated_coupon($coupon_code)) {
        return $message;
    }

    if (
        strpos(strtolower($message), 'valid email') !== false ||
        strpos(strtolower($message), 'email') !== false
    ) {
        return 'This discount code is tied to the email used when you claimed it. Make sure the checkout email matches exactly.';
    }

    return $message;
}
