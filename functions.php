<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load modular function files.
 */
$axiom_function_files = array(
    '/functions/core/setup.php',
    '/functions/core/assets.php',

    '/functions/shop/catalog.php',

    '/functions/cart/ajax-cart.php',

    '/functions/checkout/fields.php',
    '/functions/checkout/shipping.php',
    '/functions/checkout/coupons.php',

    '/functions/thankyou/templates.php',
    '/functions/thankyou/header.php',

    '/functions/contact/contact-us.php',

    '/functions/affiliate-program/affiliate-program.php',

    '/functions/account/account.php',
    '/functions/account/default-endpoint.php',

    '/functions/coa/coa.php',
);

foreach ($axiom_function_files as $axiom_file) {
    $axiom_path = get_template_directory() . $axiom_file;

    if (file_exists($axiom_path)) {
        require_once $axiom_path;
    }
}
