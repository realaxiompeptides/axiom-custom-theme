<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enable product images in emails
 */
add_filter('woocommerce_email_order_items_args', function($args) {
    $args['show_image'] = true;
    $args['image_size'] = array(90, 90);
    return $args;
});

/**
 * Custom email styles (brand styling)
 */
add_filter('woocommerce_email_styles', function($css) {
    $css .= '
        h1, h2, h3 { font-weight: 900 !important; }
        a { color: #3B6FE0; }
        .td, .th, table { border-color: #24385f !important; }
    ';
    return $css;
});

/**
 * Detect if order has any backordered items
 */
function axiom_order_has_backorder($order) {
    if (!$order instanceof WC_Order) return false;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        if ($product && $product->is_on_backorder($item->get_quantity())) {
            return true;
        }
    }

    return false;
}

/**
 * Inject custom messaging into emails (TOP of email)
 */
add_action('woocommerce_email_before_order_table', function($order, $sent_to_admin, $plain_text, $email) {

    // Only affect customer emails
    if ($sent_to_admin) return;

    // Target key emails
    $target_emails = array(
        'customer_processing_order',
        'customer_completed_order',
        'customer_on_hold_order'
    );

    if (!in_array($email->id, $target_emails)) {
        return;
    }

    $has_backorder = axiom_order_has_backorder($order);

    echo '<div style="margin-bottom:20px;">';

    // 🔥 MAIN HEADER (FIXED MESSAGE)
    echo '
    <div style="background:#0f172a;padding:20px;border-radius:12px;color:white;text-align:center;">
        <h2 style="margin:0 0 10px;">Order Confirmed</h2>
        <p style="margin:0;font-size:14px;opacity:0.9;">
            Your order has been successfully received and is now being processed.
        </p>
    </div>
    ';

    // ⚠️ BACKORDER LOGIC
    if ($has_backorder) {

        echo '
        <div style="margin-top:15px;padding:15px;border-radius:10px;background:#fff7ed;border:1px solid #f59e0b;">
            <p style="margin:0;color:#b45309;font-weight:600;">
                ⚠️ Some items in your order are currently on backorder
            </p>
            <p style="margin:8px 0 0;font-size:14px;color:#7c2d12;">
                Your order is confirmed and reserved. All items will ship together once inventory is ready.
            </p>
        </div>
        ';

    } else {

        echo '
        <p style="margin-top:15px;font-size:14px;">
            Your order is currently being prepared for shipment.
        </p>
        ';

    }

    // 🔥 SUPPORT SECTION (increases trust)
    echo '
    <p style="margin-top:15px;font-size:14px;">
        Need help or have questions?<br>
        Contact us at <strong>support@axiomresearch.shop</strong>
    </p>
    ';

    echo '</div>';

}, 10, 4);

/**
 * OPTIONAL: Change email subject line
 */
add_filter('woocommerce_email_subject_customer_completed_order', function($subject, $order) {
    return 'Your Axiom Peptides order has been confirmed';
}, 10, 2);

add_filter('woocommerce_email_subject_customer_processing_order', function($subject, $order) {
    return 'Your Axiom Peptides order is confirmed and being prepared';
}, 10, 2);
