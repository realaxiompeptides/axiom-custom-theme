<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_affiliate_program_assets() {
    $is_affiliate_program_page = (
        (function_exists('is_page_template') && is_page_template('affiliate-program/affiliate-program-template.php')) ||
        (function_exists('is_page') && is_page('affiliate-program'))
    );

    $is_affiliate_registration_page = (
        (function_exists('is_page_template') && is_page_template('affiliate-registration/affiliate-registration-template.php')) ||
        (function_exists('is_page') && is_page('affiliate-registration'))
    );

    if (!$is_affiliate_program_page && !$is_affiliate_registration_page) {
        return;
    }

    $css_path = get_template_directory() . '/assets/css/affiliate-program/affiliate-program.css';

    wp_enqueue_style(
        'axiom-affiliate-program',
        get_template_directory_uri() . '/assets/css/affiliate-program/affiliate-program.css',
        array('axiom-base'),
        file_exists($css_path) ? filemtime($css_path) : '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'axiom_affiliate_program_assets', 20);
