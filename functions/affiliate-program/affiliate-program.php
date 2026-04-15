<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_affiliate_program_assets() {
    if (
        (function_exists('is_page_template') && is_page_template('affiliate-program/affiliate-program-template.php')) ||
        is_page('affiliate-program')
    ) {
        wp_enqueue_style(
            'axiom-affiliate-program',
            get_template_directory_uri() . '/assets/css/affiliate-program/affiliate-program.css',
            array('axiom-base'),
            '1.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_affiliate_program_assets', 20);
