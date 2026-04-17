<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_force_woocommerce_templates($template, $template_name, $template_path) {
    $theme_template = '';

    switch ($template_name) {
        case 'checkout/thankyou.php':
            $theme_template = get_stylesheet_directory() . '/woocommerce/checkout/thankyou.php';
            break;

        case 'order/order-details.php':
            $theme_template = get_stylesheet_directory() . '/woocommerce/order/order-details.php';
            break;

        case 'order/order-details-customer.php':
            $theme_template = get_stylesheet_directory() . '/woocommerce/order/order-details-customer.php';
            break;
    }

    if ($theme_template && file_exists($theme_template)) {
        return $theme_template;
    }

    return $template;
}
add_filter('woocommerce_locate_template', 'axiom_force_woocommerce_templates', 20, 3);

/**
 * Disable WooCommerce built-in order verification gate.
 * We are handling verification ourselves in the custom thank you flow.
 */
add_filter('woocommerce_order_email_verification_required', '__return_false', 9999);
add_filter('woocommerce_order_received_verify_known_shoppers', '__return_false', 9999);

/**
 * Build verification cookie name.
 */
function axiom_get_order_verification_cookie_name($order_id) {
    return 'axiom_verified_order_' . absint($order_id);
}

/**
 * Set order verification cookie.
 */
function axiom_set_order_verification_cookie($order_id) {
    $cookie_name  = axiom_get_order_verification_cookie_name($order_id);
    $cookie_value = wp_hash('verified_' . absint($order_id));

    $expire   = time() + DAY_IN_SECONDS;
    $secure   = is_ssl();
    $httponly = true;
    $path     = COOKIEPATH ? COOKIEPATH : '/';
    $domain   = COOKIE_DOMAIN ? COOKIE_DOMAIN : '';

    setcookie($cookie_name, $cookie_value, $expire, $path, $domain, $secure, $httponly);
    $_COOKIE[$cookie_name] = $cookie_value;
}

/**
 * Check whether this order has been verified in current browser.
 */
function axiom_is_order_verified($order_id) {
    $order_id    = absint($order_id);
    $cookie_name = axiom_get_order_verification_cookie_name($order_id);
    $cookie_good = false;
    $session_good = false;

    if (!empty($_COOKIE[$cookie_name])) {
        $expected = wp_hash('verified_' . $order_id);
        $actual   = (string) $_COOKIE[$cookie_name];

        if (hash_equals($expected, $actual)) {
            $cookie_good = true;
        }
    }

    if (function_exists('WC') && WC()->session) {
        $session_good = (bool) WC()->session->get('axiom_verified_order_' . $order_id);
    }

    return ($cookie_good || $session_good);
}

/**
 * Handle custom order verification form submission.
 */
function axiom_handle_order_verification_submission() {
    if (
        empty($_POST['axiom_verify_order']) ||
        empty($_POST['order_id']) ||
        empty($_POST['order_key']) ||
        empty($_POST['order_email'])
    ) {
        return;
    }

    $order_id    = absint($_POST['order_id']);
    $order_key   = wc_clean(wp_unslash($_POST['order_key']));
    $order_email = sanitize_email(wp_unslash($_POST['order_email']));

    $order = wc_get_order($order_id);

    if (!$order instanceof WC_Order) {
        wc_add_notice('Order not found.', 'error');
        return;
    }

    if ((string) $order->get_order_key() !== (string) $order_key) {
        wc_add_notice('Order verification failed.', 'error');
        return;
    }

    if (strtolower((string) $order->get_billing_email()) !== strtolower((string) $order_email)) {
        wc_add_notice('The email address does not match this order.', 'error');
        return;
    }

    /**
     * Mark this order as verified in WooCommerce session.
     */
    if (function_exists('WC') && WC()->session) {
        WC()->session->set('axiom_verified_order_' . $order_id, true);
    }

    /**
     * Also set a browser cookie for Safari / strict browsers.
     */
    axiom_set_order_verification_cookie($order_id);

    /**
     * Redirect back to order received page.
     */
    $redirect_url = $order->get_checkout_order_received_url();

    if (!$redirect_url) {
        $redirect_url = wc_get_endpoint_url('view-order', $order_id, wc_get_page_permalink('myaccount'));
    }

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('template_redirect', 'axiom_handle_order_verification_submission', 1);
