<?php
if (!defined('ABSPATH')) {
    exit;
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
            '1.0'
        );

        wp_enqueue_style(
            'axiom-account-login',
            $theme_uri . '/assets/css/account/login.css',
            array('axiom-account-base'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-account-dashboard',
            $theme_uri . '/assets/css/account/dashboard.css',
            array('axiom-account-base'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-account-orders',
            $theme_uri . '/assets/css/account/orders.css',
            array('axiom-account-base'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-account-forms',
            $theme_uri . '/assets/css/account/forms.css',
            array('axiom-account-base'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-account-mobile',
            $theme_uri . '/assets/css/account/mobile.css',
            array(
                'axiom-account-base',
                'axiom-account-login',
                'axiom-account-dashboard',
                'axiom-account-orders',
                'axiom-account-forms',
            ),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-account',
            $theme_uri . '/assets/css/account/account.css',
            array(
                'axiom-account-base',
                'axiom-account-login',
                'axiom-account-dashboard',
                'axiom-account-orders',
                'axiom-account-forms',
                'axiom-account-mobile',
            ),
            '1.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_account_assets', 20);
