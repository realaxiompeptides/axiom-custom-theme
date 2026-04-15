<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contact Us page assets
 */
function axiom_contact_us_assets() {
    if (!function_exists('is_page_template')) {
        return;
    }

    if (is_page_template('contact-us/contact-us-template.php')) {
        wp_enqueue_style(
            'axiom-contact-us',
            get_template_directory_uri() . '/assets/css/contact-us/contact-us.css',
            array('axiom-base'),
            '1.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_contact_us_assets', 20);
