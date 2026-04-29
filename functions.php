<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load modular function files safely.
 */
$axiom_function_files = array(
    '/functions/core/setup.php',
    '/functions/core/assets.php',

    '/functions/shop/catalog.php',
    '/functions/shop/variation-stock-labels.php',
    '/functions/shop/stock-control.php',

    '/functions/cart/ajax-cart.php',
    '/functions/cart/cart-page.php',
    '/functions/cart/free-shipping-goal.php',

    '/functions/checkout/fields.php',
    '/functions/checkout/shipping.php',
    '/functions/checkout/shipping-tracking-email.php',
    '/functions/checkout/card-3ds-popup.php',
    '/functions/checkout/hide-ground-when-free.php',
    '/functions/checkout/coupons.php',
    '/functions/checkout/payment-discounts.php',
    '/functions/checkout/card-payment-notice.php',

    // Shows Venmo/Zelle fallback on thank-you page for failed/pending card orders
    '/functions/checkout/card-fallback-thankyou.php',

    '/functions/checkout/manual-payment-instructions-email.php',
    '/functions/checkout/kit-crypto-only.php',
    '/functions/checkout/checkout-addons.php',

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

    '/functions/emails/axiom-email-system.php',

    // 🔥 NEW: Abandoned Cart System
    '/functions/email/abandoned-cart-email.php',
    '/functions/abandoned-cart/abandoned-cart-core.php',

    // Floating vials homepage/test page assets
    '/functions/floating-vials/floating-vials.php',

    // Enhanced product research data system
    '/product-page/helpers/enhanced-product-data-loader.php',
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

/**
 * TEMP TEST HELPER:
 * Shows an "Open thank-you page" link inside WooCommerce order admin.
 */
add_action('woocommerce_admin_order_data_after_order_details', 'axiom_show_admin_thankyou_test_link');

function axiom_show_admin_thankyou_test_link($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    echo '<p style="margin-top:12px;">
        <strong>Thank You Page:</strong> 
        <a target="_blank" rel="noopener noreferrer" href="' . esc_url($order->get_checkout_order_received_url()) . '">
            Open thank-you page
        </a>
    </p>';
}

/**
 * Enqueue Reviews page stylesheet.
 */
add_action('wp_enqueue_scripts', 'axiom_enqueue_reviews_page_assets', 20);

function axiom_enqueue_reviews_page_assets() {
    if (!function_exists('is_page_template') || !is_page_template('page-reviews.php')) {
        return;
    }

    $reviews_css_path = get_template_directory() . '/assets/css/reviews-page.css';

    wp_enqueue_style(
        'axiom-reviews-page',
        get_template_directory_uri() . '/assets/css/reviews-page.css',
        array(),
        file_exists($reviews_css_path) ? filemtime($reviews_css_path) : '1.0.0'
    );
}
