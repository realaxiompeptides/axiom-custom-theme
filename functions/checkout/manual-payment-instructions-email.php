<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Send custom payment instruction email based on payment method
 */
add_action('woocommerce_checkout_order_processed', 'axiom_send_payment_instruction_email', 20, 1);

function axiom_send_payment_instruction_email($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $payment_method = $order->get_payment_method();
    $email          = $order->get_billing_email();
    $name           = $order->get_billing_first_name();
    $order_number   = $order->get_order_number();

    if (!$email) return;

    $subject = '';
    $message = '';

    // 🔵 VENMO
    if (strpos($payment_method, 'venmo') !== false) {
        $subject = 'Complete your Axiom order with Venmo';

        $message = "
        Hi {$name},

        Your order #{$order_number} has been received.

        To complete your order, please send your payment via Venmo.

        🔹 Username: @thomas-harris-axiom
        🔹 Link: https://venmo.com/thomas-harris-axiom

        IMPORTANT:
        - Use ONLY your order number: {$order_number}
        - Do NOT include product names

        Your order will not be processed until payment is received.

        Axiom Peptides
        ";
    }

    // 🟣 ZELLE
    if (strpos($payment_method, 'zelle') !== false) {
        $subject = 'Complete your Axiom order with Zelle';

        $message = "
        Hi {$name},

        Your order #{$order_number} has been received.

        To complete your order, send payment via Zelle.

        🔹 Phone: 916-233-5312
        🔹 Email: jaxferone@gmail.com

        IMPORTANT:
        - Use ONLY your order number: {$order_number}
        - Do NOT include product names

        Axiom Peptides
        ";
    }

    // 🟢 CASH APP
    if (strpos($payment_method, 'cashapp') !== false || strpos($payment_method, 'cash-app') !== false) {
        $subject = 'Complete your Axiom order with Cash App';

        $message = "
        Hi {$name},

        Your order #{$order_number} has been received.

        To complete your order, send your Cash App payment.

        Then send confirmation with your order number.

        IMPORTANT:
        - Use ONLY your order number: {$order_number}
        - Do NOT include product names

        Axiom Peptides
        ";
    }

    // Send email if we matched a method
    if ($subject && $message) {
        wp_mail($email, $subject, $message);
    }
}
