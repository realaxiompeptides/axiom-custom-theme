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

function axiom_force_custom_thankyou_sections() {
    if (is_admin()) {
        return;
    }

    remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
    add_action('woocommerce_thankyou', 'axiom_render_custom_thankyou_sections', 10);
}
add_action('wp', 'axiom_force_custom_thankyou_sections', 20);

function axiom_render_custom_thankyou_sections($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    axiom_render_custom_thankyou_header($order_id);

    $order_details_template = get_stylesheet_directory() . '/woocommerce/order/order-details.php';
    $customer_details_template = get_stylesheet_directory() . '/woocommerce/order/order-details-customer.php';

    if (file_exists($order_details_template)) {
        include $order_details_template;
    }

    if (file_exists($customer_details_template)) {
        include $customer_details_template;
    }
}
