<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_account_asset_version($relative_path) {
    $full_path = get_template_directory() . $relative_path;

    return file_exists($full_path) ? filemtime($full_path) : time();
}

function axiom_account_assets() {
    if (
        (function_exists('is_account_page') && is_account_page()) ||
        (function_exists('is_page_template') && is_page_template('my-account-template.php')) ||
        is_page('my-account')
    ) {
        $theme_uri = get_template_directory_uri();

        wp_enqueue_style(
            'axiom-account-base',
            $theme_uri . '/assets/css/account/base.css',
            array('axiom-base'),
            axiom_account_asset_version('/assets/css/account/base.css')
        );

        wp_enqueue_style(
            'axiom-account-login',
            $theme_uri . '/assets/css/account/login.css',
            array('axiom-account-base'),
            axiom_account_asset_version('/assets/css/account/login.css')
        );

        wp_enqueue_style(
            'axiom-account-dashboard',
            $theme_uri . '/assets/css/account/dashboard.css',
            array('axiom-account-base'),
            axiom_account_asset_version('/assets/css/account/dashboard.css')
        );

        wp_enqueue_style(
            'axiom-account-forms',
            $theme_uri . '/assets/css/account/forms.css',
            array('axiom-account-base'),
            axiom_account_asset_version('/assets/css/account/forms.css')
        );

        wp_enqueue_style(
            'axiom-account-mobile',
            $theme_uri . '/assets/css/account/mobile.css',
            array(
                'axiom-account-base',
                'axiom-account-login',
                'axiom-account-dashboard',
                'axiom-account-forms',
            ),
            axiom_account_asset_version('/assets/css/account/mobile.css')
        );

        wp_enqueue_style(
            'axiom-account',
            $theme_uri . '/assets/css/account/account.css',
            array(
                'axiom-account-base',
                'axiom-account-login',
                'axiom-account-dashboard',
                'axiom-account-forms',
                'axiom-account-mobile',
            ),
            axiom_account_asset_version('/assets/css/account/account.css')
        );

        /*
         * Load orders.css LAST so view-order/account order styles override everything.
         */
        wp_enqueue_style(
            'axiom-account-orders-final',
            $theme_uri . '/assets/css/account/orders.css',
            array('axiom-account'),
            axiom_account_asset_version('/assets/css/account/orders.css')
        );

        $account_js = '/assets/js/account/account.js';

        if (file_exists(get_template_directory() . $account_js)) {
            wp_enqueue_script(
                'axiom-account-js',
                $theme_uri . $account_js,
                array(),
                axiom_account_asset_version($account_js),
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'axiom_account_assets', 20);

/**
 * Force custom view-order template.
 */
remove_action('woocommerce_account_view-order_endpoint', 'woocommerce_account_view_order');

add_action('woocommerce_account_view-order_endpoint', 'axiom_force_custom_view_order_template', 1);

function axiom_force_custom_view_order_template($order_id) {
    $template = get_template_directory() . '/woocommerce/myaccount/view-order.php';

    if (file_exists($template)) {
        include $template;
        return;
    }

    wc_get_template('myaccount/view-order.php', array(
        'order_id' => $order_id,
    ));
}
