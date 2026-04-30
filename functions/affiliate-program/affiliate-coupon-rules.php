<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==========================================================
 * Axiom Affiliate Coupon Rules
 *
 * Goal:
 * If a visitor/order is affiliate-tracked, block Axiom popup
 * welcome coupons so affiliate orders do not stack:
 *
 * - Affiliate commission
 * - Affiliate discount/code
 * - Welcome popup discount
 * - Cash App/Zelle/Crypto discount
 *
 * This only blocks generated popup/welcome coupons:
 * WELCOME10-XXXXXX
 * WELCOME15-XXXXXX
 * AXIOM15-XXXXXX
 *
 * It does NOT block normal seasonal coupons unless you add them.
 * ==========================================================
 */


/**
 * Coupons/prefixes that should NOT be allowed on affiliate traffic.
 *
 * These are your email/SMS popup coupons.
 */
function axiom_affiliate_blocked_coupon_prefixes() {
    return array(
        'WELCOME10-',
        'WELCOME15-',
        'AXIOM15-',
    );
}


/**
 * Check if a coupon should be blocked on affiliate traffic.
 */
function axiom_is_blocked_affiliate_coupon($coupon_code) {
    $coupon_code = strtoupper(trim((string) $coupon_code));

    if ($coupon_code === '') {
        return false;
    }

    foreach (axiom_affiliate_blocked_coupon_prefixes() as $prefix) {
        $prefix = strtoupper(trim((string) $prefix));

        if ($prefix !== '' && strpos($coupon_code, $prefix) === 0) {
            return true;
        }
    }

    return false;
}


/**
 * Detect if this visitor/session is affiliate traffic.
 *
 * This checks:
 * - Common affiliate URL params
 * - SliceWP-ish cookies
 * - Axiom local/session cookie fallback if we create one
 */
function axiom_is_affiliate_tracked_session() {
    /**
     * 1. URL parameters.
     */
    $affiliate_params = array(
        'ref',
        'aff',
        'affiliate',
        'affiliate_id',
        'referral',
        'slicewp_ref',
        'swp_ref',
        'slicewp_affiliate',
        'affiliate_code',
    );

    foreach ($affiliate_params as $param) {
        if (!empty($_GET[$param])) {
            return true;
        }
    }

    /**
     * 2. Cookie detection.
     * SliceWP cookie names can vary by setup/version, so this checks broadly.
     */
    if (!empty($_COOKIE)) {
        foreach ($_COOKIE as $cookie_name => $cookie_value) {
            $cookie_name_lower = strtolower((string) $cookie_name);

            if (
                strpos($cookie_name_lower, 'slicewp') !== false ||
                strpos($cookie_name_lower, 'slice_wp') !== false ||
                strpos($cookie_name_lower, 'affiliate') !== false ||
                strpos($cookie_name_lower, 'referral') !== false ||
                strpos($cookie_name_lower, 'axiom_affiliate') !== false
            ) {
                return true;
            }
        }
    }

    /**
     * 3. WooCommerce session fallback.
     */
    if (function_exists('WC') && WC()->session) {
        $session_flag = WC()->session->get('axiom_affiliate_tracked_session');

        if ($session_flag === 'yes') {
            return true;
        }
    }

    return false;
}


/**
 * Store affiliate session flag when visitor lands with an affiliate parameter.
 *
 * This helps keep coupon blocking active as they browse the site.
 */
function axiom_store_affiliate_session_flag() {
    $affiliate_params = array(
        'ref',
        'aff',
        'affiliate',
        'affiliate_id',
        'referral',
        'slicewp_ref',
        'swp_ref',
        'slicewp_affiliate',
        'affiliate_code',
    );

    $has_affiliate_param = false;

    foreach ($affiliate_params as $param) {
        if (!empty($_GET[$param])) {
            $has_affiliate_param = true;
            break;
        }
    }

    if (!$has_affiliate_param) {
        return;
    }

    /**
     * Browser cookie fallback.
     */
    if (!headers_sent()) {
        setcookie(
            'axiom_affiliate_tracked_session',
            'yes',
            time() + (30 * DAY_IN_SECONDS),
            COOKIEPATH ? COOKIEPATH : '/',
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }

    /**
     * WooCommerce session fallback.
     */
    if (function_exists('WC') && WC()->session) {
        WC()->session->set('axiom_affiliate_tracked_session', 'yes');
    }
}
add_action('init', 'axiom_store_affiliate_session_flag', 1);


/**
 * Block welcome/popup coupons before WooCommerce applies them.
 */
add_filter('woocommerce_coupon_is_valid', 'axiom_block_welcome_coupons_on_affiliate_traffic', 20, 2);

function axiom_block_welcome_coupons_on_affiliate_traffic($valid, $coupon) {
    if (!$coupon instanceof WC_Coupon) {
        return $valid;
    }

    $coupon_code = $coupon->get_code();

    if (!axiom_is_blocked_affiliate_coupon($coupon_code)) {
        return $valid;
    }

    if (!axiom_is_affiliate_tracked_session()) {
        return $valid;
    }

    return false;
}


/**
 * Cleaner error message when blocked.
 */
add_filter('woocommerce_coupon_error', 'axiom_blocked_affiliate_coupon_error_message', 30, 3);

function axiom_blocked_affiliate_coupon_error_message($message, $error_code, $coupon) {
    if (!$coupon instanceof WC_Coupon) {
        return $message;
    }

    $coupon_code = $coupon->get_code();

    if (!axiom_is_blocked_affiliate_coupon($coupon_code)) {
        return $message;
    }

    if (!axiom_is_affiliate_tracked_session()) {
        return $message;
    }

    return 'Welcome discount codes cannot be combined with affiliate links or affiliate offers.';
}


/**
 * Extra safety:
 * If a blocked welcome coupon somehow gets added to the cart during
 * an affiliate session, remove it before totals calculate.
 */
add_action('woocommerce_before_calculate_totals', 'axiom_remove_blocked_welcome_coupons_from_affiliate_cart', 5);

function axiom_remove_blocked_welcome_coupons_from_affiliate_cart() {
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }

    if (!axiom_is_affiliate_tracked_session()) {
        return;
    }

    $applied_coupons = WC()->cart->get_applied_coupons();

    if (empty($applied_coupons)) {
        return;
    }

    foreach ($applied_coupons as $coupon_code) {
        if (axiom_is_blocked_affiliate_coupon($coupon_code)) {
            WC()->cart->remove_coupon($coupon_code);

            wc_add_notice(
                'Welcome discount codes cannot be combined with affiliate links or affiliate offers.',
                'error'
            );
        }
    }
}
