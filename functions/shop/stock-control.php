<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Axiom Stock Control
 * Prevent unpaid/pending orders from reducing stock.
 * Stock only reduces after confirmed payment or manual Processing/Completed status.
 */

add_filter('woocommerce_can_reduce_order_stock', 'axiom_only_reduce_stock_for_confirmed_orders', 10, 2);

function axiom_only_reduce_stock_for_confirmed_orders($can_reduce, $order) {
    if (!$order instanceof WC_Order) {
        return $can_reduce;
    }

    $allowed_statuses = array(
        'processing',
        'completed',
    );

    return in_array($order->get_status(), $allowed_statuses, true);
}

add_action('woocommerce_payment_complete', 'axiom_reduce_stock_after_confirmed_payment', 20);

function axiom_reduce_stock_after_confirmed_payment($order_id) {
    axiom_reduce_stock_once($order_id);
}

add_action('woocommerce_order_status_processing', 'axiom_reduce_stock_once', 20);
add_action('woocommerce_order_status_completed', 'axiom_reduce_stock_once', 20);

function axiom_reduce_stock_once($order_id) {
    $order = wc_get_order($order_id);

    if (!$order instanceof WC_Order) {
        return;
    }

    if ($order->get_meta('_axiom_stock_reduced') === 'yes') {
        return;
    }

    wc_reduce_stock_levels($order_id);

    $order->update_meta_data('_axiom_stock_reduced', 'yes');
    $order->save();
}

add_action('woocommerce_order_status_cancelled', 'axiom_restore_stock_if_needed', 20);
add_action('woocommerce_order_status_refunded', 'axiom_restore_stock_if_needed', 20);
add_action('woocommerce_order_status_failed', 'axiom_restore_stock_if_needed', 20);

function axiom_restore_stock_if_needed($order_id) {
    $order = wc_get_order($order_id);

    if (!$order instanceof WC_Order) {
        return;
    }

    if ($order->get_meta('_axiom_stock_reduced') !== 'yes') {
        return;
    }

    wc_increase_stock_levels($order_id);

    $order->delete_meta_data('_axiom_stock_reduced');
    $order->update_meta_data('_axiom_stock_restored', 'yes');
    $order->save();
}
