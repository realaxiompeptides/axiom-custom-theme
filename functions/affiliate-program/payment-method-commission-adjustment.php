<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==========================================================
 * Axiom Affiliate Commission Adjustment
 *
 * Goal:
 * If a referred order is paid through Cash App, Crypto, or Zelle,
 * reduce the affiliate commission RATE by 5 percentage points.
 *
 * Examples:
 * 10% commission becomes 5%
 * 25% commission becomes 20%
 * 15% commission becomes 10%
 *
 * This is percentage-point based, not a flat dollar reduction.
 * ==========================================================
 */

if (!defined('AXIOM_AFFILIATE_PAYMENT_METHOD_RATE_REDUCTION')) {
    define('AXIOM_AFFILIATE_PAYMENT_METHOD_RATE_REDUCTION', 5);
}

/**
 * Payment methods that reduce affiliate commission.
 */
function axiom_affiliate_reduced_commission_payment_keywords() {
    return array(
        'cashapp',
        'cash app',
        'cash_app',
        'cash-app',
        'cash app bitcoin',
        'zelle',
        'crypto',
        'cryptocurrency',
        'bitcoin',
        'btc',
        'usdt',
        'ethereum',
        'eth',
        'manual crypto',
    );
}

/**
 * Check whether order payment method should reduce affiliate commission.
 */
function axiom_order_uses_reduced_affiliate_payment_method($order) {
    if (!$order instanceof WC_Order) {
        return false;
    }

    $method_id    = strtolower((string) $order->get_payment_method());
    $method_title = strtolower((string) $order->get_payment_method_title());

    $haystack = $method_id . ' ' . $method_title;

    foreach (axiom_affiliate_reduced_commission_payment_keywords() as $keyword) {
        if (strpos($haystack, strtolower($keyword)) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Get commissionable order subtotal.
 *
 * This excludes shipping and taxes.
 * It uses product subtotal BEFORE payment-method discount so:
 *
 * Product subtotal: $60
 * Zelle/CashApp discount: -$3
 * Shipping: $10.20
 *
 * Commission base = $60
 */
function axiom_get_affiliate_commissionable_subtotal($order) {
    if (!$order instanceof WC_Order) {
        return 0;
    }

    $subtotal = 0;

    foreach ($order->get_items('line_item') as $item) {
        if (!$item instanceof WC_Order_Item_Product) {
            continue;
        }

        /**
         * get_subtotal() = item price before coupons/order discounts.
         * get_total() = item price after coupons.
         *
         * For affiliate payment-method adjustment, we want to preserve
         * the original product subtotal and only reduce rate by 5 points.
         */
        $subtotal += (float) $item->get_subtotal();
    }

    return round($subtotal, 2);
}

/**
 * Main adjustment.
 */
function axiom_maybe_reduce_slicewp_commission_for_payment_method($order_id) {
    if (!$order_id || !function_exists('wc_get_order')) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order instanceof WC_Order) {
        return;
    }

    /**
     * Only adjust Cash App / Crypto / Zelle style payment methods.
     */
    if (!axiom_order_uses_reduced_affiliate_payment_method($order)) {
        return;
    }

    /**
     * Prevent double adjustment.
     */
    if ($order->get_meta('_axiom_affiliate_payment_method_commission_adjusted') === 'yes') {
        return;
    }

    $commissionable_subtotal = axiom_get_affiliate_commissionable_subtotal($order);

    if ($commissionable_subtotal <= 0) {
        return;
    }

    global $wpdb;

    $commissions_table = $wpdb->prefix . 'slicewp_commissions';

    /**
     * Make sure SliceWP commissions table exists.
     */
    $table_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $commissions_table
        )
    );

    if ($table_exists !== $commissions_table) {
        error_log('Axiom affiliate adjustment: SliceWP commissions table not found.');
        return;
    }

    /**
     * Get columns once so updates don't break if SliceWP schema differs.
     */
    $columns = $wpdb->get_col("DESC {$commissions_table}", 0);

    /**
     * SliceWP usually stores WooCommerce order ID as reference
     * and WooCommerce origin as "woo".
     */
    $commissions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$commissions_table}
             WHERE reference = %s
             AND origin = %s
             AND status IN ('pending', 'unpaid')",
            (string) $order_id,
            'woo'
        ),
        ARRAY_A
    );

    if (empty($commissions)) {
        return;
    }

    $rate_reduction = (float) AXIOM_AFFILIATE_PAYMENT_METHOD_RATE_REDUCTION;
    $adjusted_any   = false;

    foreach ($commissions as $commission) {
        if (empty($commission['id']) || !isset($commission['amount'])) {
            continue;
        }

        $commission_id   = absint($commission['id']);
        $original_amount = (float) $commission['amount'];

        if ($original_amount <= 0) {
            continue;
        }

        /**
         * Detect actual commission rate from original commission amount.
         *
         * Example:
         * Commission amount: $6
         * Commissionable subtotal: $60
         * Current rate = 10%
         */
        $current_rate = ($original_amount / $commissionable_subtotal) * 100;

        /**
         * Subtract 5 percentage points.
         *
         * Example:
         * 10% - 5% = 5%
         * 25% - 5% = 20%
         */
        $new_rate = max(0, $current_rate - $rate_reduction);

        /**
         * Recalculate commission from the new rate.
         */
        $new_amount = round($commissionable_subtotal * ($new_rate / 100), 2);

        /**
         * Safety: never increase commission.
         */
        if ($new_amount >= $original_amount) {
            continue;
        }

        $update_data = array(
            'amount' => $new_amount,
        );

        $update_format = array(
            '%f',
        );

        if (in_array('date_modified', $columns, true)) {
            $update_data['date_modified'] = current_time('mysql');
            $update_format[] = '%s';
        }

        if (in_array('notes', $columns, true)) {
            $existing_notes = isset($commission['notes']) ? (string) $commission['notes'] : '';

            $update_data['notes'] = trim(
                $existing_notes . "\n" .
                'Axiom adjustment: payment method discount used. Commission rate reduced by ' .
                wc_format_decimal($rate_reduction, 2) .
                ' percentage points from ' .
                wc_format_decimal($current_rate, 2) .
                '% to ' .
                wc_format_decimal($new_rate, 2) .
                '%. Commission changed from $' .
                wc_format_decimal($original_amount, 2) .
                ' to $' .
                wc_format_decimal($new_amount, 2) .
                '. Payment method: ' .
                $order->get_payment_method_title() .
                '.'
            );

            $update_format[] = '%s';
        }

        $updated = $wpdb->update(
            $commissions_table,
            $update_data,
            array('id' => $commission_id),
            $update_format,
            array('%d')
        );

        if ($updated !== false) {
            $adjusted_any = true;

            $order->add_order_note(
                sprintf(
                    'Affiliate commission adjusted for payment method discount. Commission #%d reduced by %s percentage points: %s%% → %s%%. Amount changed from $%s to $%s. Payment method: %s.',
                    $commission_id,
                    wc_format_decimal($rate_reduction, 2),
                    wc_format_decimal($current_rate, 2),
                    wc_format_decimal($new_rate, 2),
                    wc_format_decimal($original_amount, 2),
                    wc_format_decimal($new_amount, 2),
                    $order->get_payment_method_title()
                )
            );
        }
    }

    if ($adjusted_any) {
        $order->update_meta_data('_axiom_affiliate_payment_method_commission_adjusted', 'yes');
        $order->update_meta_data('_axiom_affiliate_payment_method_commission_adjusted_at', current_time('mysql'));
        $order->update_meta_data('_axiom_affiliate_payment_method_commission_reduction_points', $rate_reduction);
        $order->save();
    }
}

/**
 * Run when order is created.
 */
add_action('woocommerce_checkout_order_processed', function($order_id) {
    axiom_maybe_reduce_slicewp_commission_for_payment_method($order_id);
}, 99);

/**
 * Run when order status changes.
 */
add_action('woocommerce_order_status_pending', 'axiom_maybe_reduce_slicewp_commission_for_payment_method', 99);
add_action('woocommerce_order_status_processing', 'axiom_maybe_reduce_slicewp_commission_for_payment_method', 99);
add_action('woocommerce_order_status_completed', 'axiom_maybe_reduce_slicewp_commission_for_payment_method', 99);
add_action('woocommerce_order_status_on-hold', 'axiom_maybe_reduce_slicewp_commission_for_payment_method', 99);

/**
 * Retry 2 minutes after checkout because SliceWP may create
 * the commission slightly after WooCommerce creates the order.
 */
add_action('woocommerce_checkout_order_processed', function($order_id) {
    if (!wp_next_scheduled('axiom_retry_affiliate_payment_method_commission_adjustment', array($order_id))) {
        wp_schedule_single_event(
            time() + 120,
            'axiom_retry_affiliate_payment_method_commission_adjustment',
            array($order_id)
        );
    }
}, 100);

add_action('axiom_retry_affiliate_payment_method_commission_adjustment', function($order_id) {
    axiom_maybe_reduce_slicewp_commission_for_payment_method($order_id);
});

/**
 * Admin manual tool:
 * Adds a button on WooCommerce order admin page so you can manually rerun adjustment.
 */
add_action('woocommerce_admin_order_data_after_order_details', function($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    if (!axiom_order_uses_reduced_affiliate_payment_method($order)) {
        return;
    }

    $url = wp_nonce_url(
        admin_url('admin-post.php?action=axiom_adjust_affiliate_commission_for_order&order_id=' . $order->get_id()),
        'axiom_adjust_affiliate_commission_for_order_' . $order->get_id()
    );

    echo '<p style="margin-top:12px;">
        <strong>Axiom Affiliate Adjustment:</strong><br>
        <a class="button" href="' . esc_url($url) . '">Run Payment Method Commission Adjustment</a>
    </p>';
});

/**
 * Manual adjustment handler.
 */
add_action('admin_post_axiom_adjust_affiliate_commission_for_order', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Unauthorized');
    }

    $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;

    if (!$order_id) {
        wp_die('Missing order ID.');
    }

    check_admin_referer('axiom_adjust_affiliate_commission_for_order_' . $order_id);

    $order = wc_get_order($order_id);

    if ($order instanceof WC_Order) {
        /**
         * Allow manual rerun by clearing adjustment meta.
         */
        $order->delete_meta_data('_axiom_affiliate_payment_method_commission_adjusted');
        $order->save();
    }

    axiom_maybe_reduce_slicewp_commission_for_payment_method($order_id);

    wp_safe_redirect(
        admin_url('post.php?post=' . $order_id . '&action=edit')
    );
    exit;
});
