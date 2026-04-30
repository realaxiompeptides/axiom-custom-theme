<?php
if (!defined('ABSPATH')) {
    exit;
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

    $coupon_code = isset($_POST['coupon_code'])
        ? wc_format_coupon_code(wc_clean(wp_unslash($_POST['coupon_code'])))
        : '';

    if (!$coupon_code) {
        wp_send_json_error(array(
            'message' => 'Please enter a discount code.',
        ));
    }

    /**
     * IMPORTANT:
     * Before WooCommerce validates popup coupons, save the checkout
     * email into the WooCommerce customer/session.
     */
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
 *
 * Fixes WELCOME10 / WELCOME15 coupons failing even when the
 * checkout email visually matches.
 *
 * Reason:
 * Custom coupon AJAX can validate the coupon before WooCommerce
 * saves the checkout email into the customer session.
 * ==========================================================
 */

/**
 * Check whether coupon is one of the generated popup coupons.
 */
function axiom_popup_coupon_code_is_generated_coupon($coupon_code) {
    $coupon_code = strtoupper(trim((string) $coupon_code));

    return (
        strpos($coupon_code, 'WELCOME10-') === 0 ||
        strpos($coupon_code, 'WELCOME15-') === 0
    );
}

/**
 * Get the first email restriction from a coupon.
 */
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

/**
 * Get billing email from current checkout/cart request.
 */
function axiom_get_live_checkout_email_for_coupon() {
    $email = '';

    /**
     * Custom checkout coupon JS should send this:
     * axiom_billing_email
     */
    if (!empty($_POST['axiom_billing_email'])) {
        $email = sanitize_email(wp_unslash($_POST['axiom_billing_email']));
    }

    /**
     * Direct billing_email field from checkout request.
     */
    if (!$email && !empty($_POST['billing_email'])) {
        $email = sanitize_email(wp_unslash($_POST['billing_email']));
    }

    /**
     * WooCommerce checkout AJAX often sends serialized post_data.
     */
    if (!$email && !empty($_POST['post_data'])) {
        parse_str(wp_unslash($_POST['post_data']), $posted_data);

        if (!empty($posted_data['billing_email'])) {
            $email = sanitize_email($posted_data['billing_email']);
        }
    }

    /**
     * Session fallback.
     */
    if (!$email && function_exists('WC') && WC()->session) {
        $session_email = WC()->session->get('axiom_live_checkout_email');

        if ($session_email) {
            $email = sanitize_email($session_email);
        }
    }

    /**
     * WooCommerce customer fallback.
     */
    if (!$email && function_exists('WC') && WC()->customer) {
        $email = sanitize_email(WC()->customer->get_billing_email());
    }

    return is_email($email) ? strtolower($email) : '';
}

/**
 * Save checkout email into WC customer/session before coupon validation.
 */
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

/**
 * Early sync when coupon AJAX hits PHP.
 */
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

/**
 * Before totals calculate, sync live checkout email.
 */
add_action('woocommerce_before_calculate_totals', 'axiom_sync_checkout_email_before_coupon_validation', 1);

function axiom_sync_checkout_email_before_coupon_validation() {
    axiom_sync_live_checkout_email_to_customer();
}

/**
 * Save checkout email into customer session whenever checkout updates.
 */
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

/**
 * Make WELCOME10 / WELCOME15 coupons validate against the live checkout email.
 */
add_filter('woocommerce_coupon_is_valid_for_customer', 'axiom_validate_popup_coupon_with_live_checkout_email', 999, 3);

function axiom_validate_popup_coupon_with_live_checkout_email($valid, $coupon, $customer) {
    if (!$coupon instanceof WC_Coupon) {
        return $valid;
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

/**
 * On checkout, if billing email is empty but session has live email, preload it.
 */
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

/**
 * Cleaner popup coupon error message.
 */
add_filter('woocommerce_coupon_error', 'axiom_clean_popup_coupon_email_error', 20, 3);

function axiom_clean_popup_coupon_email_error($message, $error_code, $coupon) {
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
