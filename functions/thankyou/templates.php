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
