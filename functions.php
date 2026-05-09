<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load modular function files safely.
 */
$axiom_function_files = array(

    // CORE
    '/functions/core/setup.php',
    '/functions/core/assets.php',

    // SEO AUTHORITY SYSTEM - NON-VISIBLE ONLY
    // These do NOT add visible SEO blocks to the front end.
    '/functions/seo/meta-tags.php',
    '/functions/seo/schema.php',
    '/functions/seo/image-alt.php',
    '/functions/seo/robots.php',
    '/functions/seo/sitemap-helper.php',

    // SHOP
    '/functions/shop/catalog.php',
    '/functions/shop/variation-stock-labels.php',
    '/functions/shop/stock-control.php',

    // CART
    '/functions/cart/ajax-cart.php',
    '/functions/cart/cart-page.php',
    '/functions/cart/free-shipping-goal.php',

    // CHECKOUT
    '/functions/checkout/fields.php',
    '/functions/checkout/shipping.php',
    '/functions/checkout/shipping-tracking-email.php',
    '/functions/checkout/card-3ds-popup.php',
    '/functions/checkout/hide-ground-when-free.php',
    '/functions/checkout/coupons.php',
    '/functions/checkout/payment-discounts.php',
    '/functions/checkout/card-payment-notice.php',
    '/functions/checkout/card-fallback-thankyou.php',
    '/functions/checkout/manual-payment-instructions-email.php',
    '/functions/checkout/kit-crypto-only.php',
    '/functions/checkout/checkout-addons.php',

    // THANK YOU
    '/functions/thankyou/templates.php',
    '/functions/thankyou/header.php',
    '/functions/thankyou/verification.php',
    '/functions/thankyou/payment-countdown.php',

    // CONTACT
    '/functions/contact/contact-us.php',

    // AFFILIATE
    '/functions/affiliate-program/affiliate-program.php',

    // Blocks WELCOME10 / WELCOME15 / AXIOM15 popup coupons on affiliate traffic
    '/functions/affiliate-program/affiliate-coupon-rules.php',

    // Reduces affiliate commission by 5 percentage points for Cash App / Crypto / Zelle orders
    '/functions/affiliate-program/payment-method-commission-adjustment.php',

    // ACCOUNT
    '/functions/account/account.php',
    '/functions/account/default-endpoint.php',

    // COA
    '/functions/coa/coa.php',
    '/functions/coa/coa-map.php',

    // TOOLS
    '/functions/calculator/peptide-calculator.php',

    // EMAIL SYSTEM
    '/functions/emails/axiom-email-system.php',

    // ABANDONED CART
    '/functions/emails/abandoned-cart-email.php',
    '/functions/abandoned-cart/abandoned-cart-core.php',

    // LEADS + STORAGE
    '/functions/marketing/leads-system.php',

    // POPUP SYSTEM
    '/functions/marketing/popup-capture.php',

    // SMS COUNTRY DATA FOR POPUP
    '/functions/marketing/sms-capture.php',

    // RESTOCK ALERTS - TEST MODE ONLY
    '/functions/marketing/restock-alerts.php',

    // UI / VISUAL
    '/functions/floating-vials/floating-vials.php',

    // PRODUCT SYSTEM
    '/product-page/helpers/enhanced-product-data-loader.php',
);

/**
 * Load all modules safely.
 */
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
 * ADMIN HELPER — Open thank-you page.
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
 * REVIEWS PAGE CSS.
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

/**
 * OPTIONAL: GLOBAL BRAND COLORS.
 */
add_action('wp_head', 'axiom_output_global_brand_colors');

function axiom_output_global_brand_colors() {
    echo '<style>
        :root {
            --axiom-blue: #3B6FE0;
            --axiom-blue-light: #5A8CFF;
            --axiom-dark: #0c1220;
        }
    </style>';
}
