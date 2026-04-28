<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {

    /*
     * Load floating vial CSS/JS on:
     * 1. Homepage
     * 2. Floating vials test template
     */
    if (
        !is_front_page() &&
        !is_page_template('floating-vials.php')
    ) {
        return;
    }

    $theme_uri  = get_template_directory_uri();
    $theme_path = get_template_directory();

    $css_path = $theme_path . '/assets/css/floating-vials-test.css';
    $js_path  = $theme_path . '/assets/js/floating-vials-test.js';

    wp_enqueue_style(
        'axiom-floating-vials-test',
        $theme_uri . '/assets/css/floating-vials-test.css',
        array(),
        file_exists($css_path) ? filemtime($css_path) : time()
    );

    wp_enqueue_script(
        'axiom-floating-vials-test',
        $theme_uri . '/assets/js/floating-vials-test.js',
        array(),
        file_exists($js_path) ? filemtime($js_path) : time(),
        true
    );

}, 99);
