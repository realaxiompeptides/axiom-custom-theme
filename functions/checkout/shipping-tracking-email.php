<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Show tracking link inside customer WooCommerce emails.
 * Update the meta key below if your tracking number is stored under a different key.
 */
add_action('woocommerce_email_after_order_table', 'axiom_add_tracking_to_customer_emails', 10, 4);

function axiom_add_tracking_to_customer_emails($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin || !$order instanceof WC_Order) {
        return;
    }

    // Change this if your tracking number uses a different meta key.
    $tracking_number = get_post_meta($order->get_id(), '_tracking_number', true);

    if (empty($tracking_number)) {
        return;
    }

    $tracking_url = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . rawurlencode($tracking_number);

    if ($plain_text) {
        echo "\nTrack your order:\n" . esc_url($tracking_url) . "\n";
        return;
    }

    echo '<p style="margin:16px 0 0;">';
    echo '<strong>Track your order:</strong><br>';
    echo '<a href="' . esc_url($tracking_url) . '" target="_blank" rel="noopener">Click here to track your package</a>';
    echo '</p>';
}
