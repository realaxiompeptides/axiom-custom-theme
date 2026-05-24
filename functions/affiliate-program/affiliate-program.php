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
        array(),
        file_exists($css_path) ? filemtime($css_path) : '2.0.0'
    );

    if ($is_affiliate_program_page) {
        $js_path = get_template_directory() . '/assets/js/affiliate-program/affiliate-program.js';

        wp_enqueue_script(
            'axiom-affiliate-program',
            get_template_directory_uri() . '/assets/js/affiliate-program/affiliate-program.js',
            array(),
            file_exists($js_path) ? filemtime($js_path) : '2.0.0',
            true
        );
    }

    if ($is_affiliate_registration_page) {
        $registration_css_path = get_template_directory() . '/assets/css/affiliate-program/affiliate-registration-fields.css';
        $registration_js_path  = get_template_directory() . '/assets/js/affiliate-program/affiliate-registration-fields.js';

        wp_enqueue_style(
            'axiom-affiliate-registration-fields',
            get_template_directory_uri() . '/assets/css/affiliate-program/affiliate-registration-fields.css',
            array('axiom-affiliate-program'),
            file_exists($registration_css_path) ? filemtime($registration_css_path) : '1.0.0'
        );

        wp_enqueue_script(
            'axiom-affiliate-registration-fields',
            get_template_directory_uri() . '/assets/js/affiliate-program/affiliate-registration-fields.js',
            array(),
            file_exists($registration_js_path) ? filemtime($registration_js_path) : '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_affiliate_program_assets', 20);
