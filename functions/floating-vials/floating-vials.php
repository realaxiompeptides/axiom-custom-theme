<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
    if (!is_page_template('floating-vials.php')) {
        return;
    }

    $theme_uri  = get_template_directory_uri();
    $theme_path = get_template_directory();

    wp_enqueue_style(
        'axiom-floating-vials-test',
        $theme_uri . '/assets/css/floating-vials-test.css',
        array(),
        file_exists($theme_path . '/assets/css/floating-vials-test.css') ? filemtime($theme_path . '/assets/css/floating-vials-test.css') : time()
    );

    wp_enqueue_script(
        'axiom-floating-vials-test',
        $theme_uri . '/assets/js/floating-vials-test.js',
        array(),
        file_exists($theme_path . '/assets/js/floating-vials-test.js') ? filemtime($theme_path . '/assets/js/floating-vials-test.js') : time(),
        true
    );
}, 99);
