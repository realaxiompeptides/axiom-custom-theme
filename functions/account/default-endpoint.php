<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_force_my_account_dashboard_endpoint() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    if (!function_exists('is_account_page') || !is_account_page()) {
        return;
    }

    global $wp;

    $current_request = isset($wp->request) ? trim((string) $wp->request, '/') : '';
    $account_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('myaccount') : 0;
    $account_slug    = $account_page_id > 0 ? trim(get_page_uri($account_page_id), '/') : 'my-account';

    /*
     * Do nothing on the base My Account page.
     * WooCommerce will load the dashboard there by default.
     */
    if ($current_request === $account_slug || $current_request === '') {
        return;
    }
}
add_action('template_redirect', 'axiom_force_my_account_dashboard_endpoint', 1);
