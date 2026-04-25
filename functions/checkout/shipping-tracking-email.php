<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Axiom - Shipping Tracking Email Automation
 * 1. Adds tracking info inside normal WooCommerce customer emails.
 * 2. Automatically sends a separate tracking email when tracking is added to an order.
 */

add_action('woocommerce_email_after_order_table', 'axiom_add_tracking_to_customer_emails', 10, 4);
add_action('woocommerce_update_order', 'axiom_maybe_send_tracking_email_when_added', 20, 1);
add_action('woocommerce_order_status_completed', 'axiom_maybe_send_tracking_email_when_added', 20, 1);

function axiom_add_tracking_to_customer_emails($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin || !$order instanceof WC_Order) {
        return;
    }

    $tracking = axiom_get_order_tracking_details($order);

    if (empty($tracking['number'])) {
        return;
    }

    if ($plain_text) {
        echo "\nUSPS Tracking Number: " . esc_html($tracking['number']) . "\n";
        echo "Track your package: " . esc_url($tracking['url']) . "\n";
        return;
    }

    echo axiom_get_tracking_email_block($order, $tracking);
}

function axiom_maybe_send_tracking_email_when_added($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    if ($order->get_meta('_axiom_tracking_email_sent') === 'yes') {
        return;
    }

    $tracking = axiom_get_order_tracking_details($order);

    if (empty($tracking['number'])) {
        return;
    }

    axiom_send_tracking_email($order, $tracking);

    $order->update_meta_data('_axiom_tracking_email_sent', 'yes');
    $order->update_meta_data('_axiom_tracking_email_sent_at', current_time('mysql'));
    $order->save();
}

function axiom_get_order_tracking_details($order) {
    $tracking_number = '';
    $tracking_url    = '';
    $carrier         = 'USPS';

    $shipment_items = $order->get_meta('_wc_shipment_tracking_items');

    if (!empty($shipment_items) && is_array($shipment_items)) {
        $first_item = reset($shipment_items);

        if (!empty($first_item['tracking_number'])) {
            $tracking_number = trim($first_item['tracking_number']);
        }

        if (!empty($first_item['tracking_provider'])) {
            $carrier = trim($first_item['tracking_provider']);
        }

        if (!empty($first_item['custom_tracking_link'])) {
            $tracking_url = trim($first_item['custom_tracking_link']);
        }
    }

    $tracking_keys = array(
        '_tracking_number',
        'tracking_number',
        '_usps_tracking_number',
        'usps_tracking_number',
        '_wcshipping_tracking_number',
        '_shipping_tracking_number',
        '_shipment_tracking_number',
        '_wc_shipment_tracking_number',
    );

    foreach ($tracking_keys as $key) {
        if (!empty($tracking_number)) {
            break;
        }

        $value = $order->get_meta($key);

        if (!empty($value) && is_string($value)) {
            $tracking_number = trim($value);
        }
    }

    $tracking_url_keys = array(
        '_tracking_url',
        'tracking_url',
        '_usps_tracking_url',
        'usps_tracking_url',
        '_wcshipping_tracking_url',
        '_shipping_tracking_url',
        '_shipment_tracking_url',
    );

    foreach ($tracking_url_keys as $key) {
        if (!empty($tracking_url)) {
            break;
        }

        $value = $order->get_meta($key);

        if (!empty($value) && is_string($value)) {
            $tracking_url = trim($value);
        }
    }

    if (empty($tracking_url) && !empty($tracking_number)) {
        $tracking_url = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . rawurlencode($tracking_number);
    }

    return array(
        'number'  => $tracking_number,
        'url'     => $tracking_url,
        'carrier' => $carrier ?: 'USPS',
    );
}

function axiom_send_tracking_email($order, $tracking) {
    $to = $order->get_billing_email();

    if (empty($to)) {
        return;
    }

    $first_name = $order->get_billing_first_name();
    $order_num  = $order->get_order_number();

    $subject = 'Your Axiom order has shipped';

    $heading = 'Your order is on the way';

    $message  = '<p>Hi ' . esc_html($first_name ?: 'there') . ',</p>';
    $message .= '<p>Your Axiom order <strong>#' . esc_html($order_num) . '</strong> has shipped.</p>';
    $message .= '<p>Your USPS tracking information is below.</p>';
    $message .= axiom_get_tracking_email_block($order, $tracking);
    $message .= '<p>Please allow USPS a little time to update the first scan after the label is created.</p>';
    $message .= '<p>Thank you for ordering from Axiom.</p>';

    $mailer  = WC()->mailer();
    $wrapped = $mailer->wrap_message($heading, $message);

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
    );

    $mailer->send($to, $subject, $wrapped, $headers);
}

function axiom_get_tracking_email_block($order, $tracking) {
    ob_start();
    ?>
    <div style="margin:24px 0;padding:22px;border:1px solid #d8e4ff;background:#eef4ff;border-radius:16px;text-align:center;">
        <p style="margin:0 0 8px;font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:#3557a3;font-weight:800;">
            USPS Tracking Number
        </p>

        <p style="margin:0 0 18px;font-size:24px;line-height:1.3;color:#0f172a;font-weight:900;">
            <?php echo esc_html($tracking['number']); ?>
        </p>

        <a href="<?php echo esc_url($tracking['url']); ?>" target="_blank" rel="noopener" style="background:#3B6FE0;color:#ffffff;text-decoration:none;padding:14px 24px;border-radius:999px;font-weight:800;display:inline-block;">
            Track My Package
        </a>
    </div>
    <?php
    return ob_get_clean();
}
