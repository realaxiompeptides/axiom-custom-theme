<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_apply_coupon() {
    check_ajax_referer('axiom_apply_coupon', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array(
            'message' => 'Cart unavailable.',
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

    if (WC()->cart->has_discount($coupon_code)) {
        wp_send_json_success(array(
            'message' => 'Discount already applied.',
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
        'message' => $message,
        'coupon_code' => $coupon_code,
    ));
}
add_action('wp_ajax_axiom_apply_coupon', 'axiom_apply_coupon');
add_action('wp_ajax_nopriv_axiom_apply_coupon', 'axiom_apply_coupon');

/**
 * ==========================================================
 * Axiom Popup Coupon Checkout Email Fix
 *
 * Fixes WELCOME10 / WELCOME15 coupons being rejected even when
 * the customer already typed the correct email at checkout.
 *
 * Why:
 * WooCommerce sometimes validates coupon email restrictions
 * before the checkout email is saved to the WC customer session.
 * ==========================================================
 */

/**
 * Get billing email from current checkout/cart request.
 */
function axiom_get_live_checkout_email_for_coupon() {
    $email = '';

    // Direct billing_email field from checkout request
    if (isset($_POST['billing_email'])) {
        $email = sanitize_email(wp_unslash($_POST['billing_email']));
    }

    // WooCommerce checkout AJAX often sends serialized post_data
    if (!$email && isset($_POST['post_data'])) {
        parse_str(wp_unslash($_POST['post_data']), $posted_data);

        if (!empty($posted_data['billing_email'])) {
            $email = sanitize_email($posted_data['billing_email']);
        }
    }

    // WooCommerce apply coupon AJAX may send security + coupon only,
    // so fall back to current customer object.
    if (!$email && function_exists('WC') && WC()->customer) {
        $email = sanitize_email(WC()->customer->get_billing_email());
    }

    return is_email($email) ? $email : '';
}


/**
 * Before coupon validation, force the checkout email into WC session.
 */
add_action('woocommerce_before_calculate_totals', 'axiom_sync_checkout_email_before_coupon_validation', 1);

function axiom_sync_checkout_email_before_coupon_validation() {
    if (!function_exists('WC') || !WC()->customer) {
        return;
    }

    $email = axiom_get_live_checkout_email_for_coupon();

    if (!$email) {
        return;
    }

    WC()->customer->set_billing_email($email);
    WC()->customer->save();
}


/**
 * Make WELCOME10 / WELCOME15 coupons validate against the email typed
 * on checkout, even before WooCommerce saves checkout fields.
 */
add_filter('woocommerce_coupon_is_valid_for_customer', 'axiom_validate_popup_coupon_with_live_checkout_email', 20, 3);

function axiom_validate_popup_coupon_with_live_checkout_email($valid, $coupon, $customer) {
    if (!$coupon instanceof WC_Coupon) {
        return $valid;
    }

    $coupon_code = strtoupper($coupon->get_code());

    if (
        strpos($coupon_code, 'WELCOME10-') !== 0 &&
        strpos($coupon_code, 'WELCOME15-') !== 0
    ) {
        return $valid;
    }

    $email_restrictions = $coupon->get_email_restrictions();

    if (empty($email_restrictions) || !is_array($email_restrictions)) {
        return $valid;
    }

    $typed_email = axiom_get_live_checkout_email_for_coupon();

    if (!$typed_email) {
        return false;
    }

    foreach ($email_restrictions as $restricted_email) {
        $restricted_email = sanitize_email($restricted_email);

        if (strtolower($typed_email) === strtolower($restricted_email)) {
            if (function_exists('WC') && WC()->customer) {
                WC()->customer->set_billing_email($typed_email);
                WC()->customer->save();
            }

            return true;
        }
    }

    return false;
}


/**
 * Save checkout email into customer session whenever checkout updates.
 */
add_action('woocommerce_checkout_update_order_review', 'axiom_save_checkout_email_on_order_review_update', 5);

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

    WC()->customer->set_billing_email($email);
    WC()->customer->save();
}


/**
 * Cleaner popup coupon error message.
 */
add_filter('woocommerce_coupon_error', 'axiom_clean_popup_coupon_email_error', 20, 3);

function axiom_clean_popup_coupon_email_error($message, $error_code, $coupon) {
    if (!$coupon instanceof WC_Coupon) {
        return $message;
    }

    $coupon_code = strtoupper($coupon->get_code());

    if (
        strpos($coupon_code, 'WELCOME10-') !== 0 &&
        strpos($coupon_code, 'WELCOME15-') !== 0
    ) {
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
