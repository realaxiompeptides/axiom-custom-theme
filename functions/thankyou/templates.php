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
 * Disable WooCommerce's built-in order email verification gate.
 * We are handling verification ourselves inside the custom thank you template.
 */
add_filter('woocommerce_order_email_verification_required', '__return_false', 9999);
add_filter('woocommerce_order_received_verify_known_shoppers', '__return_false', 9999);

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

    /*
     * Mark this order as verified for this browser/session.
     */
    if (function_exists('WC') && WC()->session) {
        WC()->session->set('axiom_verified_order_' . $order_id, true);
    }

    $redirect_url = $order->get_checkout_order_received_url();

    if (!$redirect_url) {
        $redirect_url = wc_get_endpoint_url('view-order', $order_id, wc_get_page_permalink('myaccount'));
    }

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('template_redirect', 'axiom_handle_order_verification_submission', 1);
