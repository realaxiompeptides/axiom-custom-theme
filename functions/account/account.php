<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_account_assets() {
    if (!function_exists('is_account_page')) {
        return;
    }

    if (is_account_page()) {
        wp_enqueue_style(
            'axiom-account',
            get_template_directory_uri() . '/assets/css/account/account.css',
            array('axiom-base'),
            '1.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_account_assets', 20);
