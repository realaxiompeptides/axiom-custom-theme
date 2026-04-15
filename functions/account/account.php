<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_account_assets() {
    if (
        (function_exists('is_account_page') && is_account_page()) ||
        (function_exists('is_page_template') && is_page_template('my-account/my-account-template.php')) ||
        is_page('my-account')
    ) {
        wp_enqueue_style(
            'axiom-account',
            get_template_directory_uri() . '/assets/css/account/account.css',
            array('axiom-base'),
            '1.1'
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_account_assets', 20);
