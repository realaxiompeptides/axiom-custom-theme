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

    if ($order->get_user_id()) {
        wc_set_customer_auth_cookie($order->get_user_id());
    }

    $redirect_url = $order->get_view_order_url();
    if (!$redirect_url) {
        $redirect_url = wc_get_endpoint_url('view-order', $order_id, wc_get_page_permalink('myaccount'));
    }

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('template_redirect', 'axiom_handle_order_verification_submission', 1);

/**
 * Render custom verification screen when needed.
 */
function axiom_maybe_render_custom_order_verification() {
    if (is_admin()) {
        return;
    }

    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('view-order')) {
        return;
    }

    global $wp;
    $order_id = isset($wp->query_vars['view-order']) ? absint($wp->query_vars['view-order']) : 0;

    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order instanceof WC_Order) {
        return;
    }

    if (current_user_can('edit_shop_orders')) {
        return;
    }

    $requires_verification = false;

    if (!is_user_logged_in()) {
        $requires_verification = true;
    } else {
        $current_user  = wp_get_current_user();
        $billing_email = (string) $order->get_billing_email();

        if (
            !$current_user ||
            empty($current_user->user_email) ||
            strtolower((string) $current_user->user_email) !== strtolower($billing_email)
        ) {
            $requires_verification = true;
        }
    }

    if (!$requires_verification) {
        return;
    }

    $verification_file = get_template_directory() . '/functions/thankyou/verification.php';

    if (!file_exists($verification_file)) {
        return;
    }

    status_header(200);

    get_header();

    echo '<main class="site-main axiom-order-verification-page">';
    wc_print_notices();
    include $verification_file;
    echo '</main>';

    get_footer();
    exit;
}
add_action('template_redirect', 'axiom_maybe_render_custom_order_verification', 5);
