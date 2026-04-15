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
