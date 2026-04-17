<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load modular function files SAFELY
 */
$axiom_function_files = array(
    '/functions/core/setup.php',
    '/functions/core/assets.php',

    '/functions/shop/catalog.php',

    '/functions/cart/ajax-cart.php',
    '/functions/cart/cart-page.php',

    '/functions/checkout/fields.php',
    '/functions/checkout/shipping.php',
    '/functions/checkout/coupons.php',
    '/functions/checkout/payment-discounts.php',
    '/functions/checkout/card-payment-notice.php',
    '/functions/checkout/payment-instruction-emails.php',

    '/functions/thankyou/templates.php',
    '/functions/thankyou/header.php',
    '/functions/thankyou/verification.php',
    '/functions/thankyou/payment-countdown.php',

    '/functions/contact/contact-us.php',

    '/functions/affiliate-program/affiliate-program.php',

    '/functions/account/account.php',
    '/functions/account/default-endpoint.php',

    '/functions/coa/coa.php',
    '/functions/coa/coa-map.php',

    '/functions/calculator/peptide-calculator.php',
);

foreach ($axiom_function_files as $axiom_file) {
    $axiom_path = get_template_directory() . $axiom_file;

    if (file_exists($axiom_path)) {
        try {
            require_once $axiom_path;
        } catch (Throwable $e) {
            error_log('Axiom include error in: ' . $axiom_file . ' | ' . $e->getMessage());
        }
    } else {
        error_log('Axiom missing file: ' . $axiom_file);
    }
}
