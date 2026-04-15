<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_peptide_calculator_assets() {
    if (
        is_page('peptide-calculator') ||
        is_page('calculator') ||
        (function_exists('is_page_template') && is_page_template('peptide-calculator/peptide-calculator-template.php'))
    ) {
        $theme_uri = get_template_directory_uri();

        wp_enqueue_style(
            'axiom-peptide-calculator',
            $theme_uri . '/assets/css/calculator/peptide-calculator.css',
            array('axiom-base'),
            '1.0'
        );

        wp_enqueue_script(
            'axiom-peptide-calculator',
            $theme_uri . '/assets/js/calculator/peptide-calculator.js',
            array(),
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_peptide_calculator_assets', 20);
