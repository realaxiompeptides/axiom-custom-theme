<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_render_custom_thankyou_header($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $order_number       = $order->get_order_number();
    $order_total        = (float) $order->get_total();
    $order_subtotal     = (float) $order->get_subtotal();
    $order_shipping     = (float) $order->get_shipping_total();
    $order_tax          = (float) $order->get_total_tax();
    $payment_method     = $order->get_payment_method_title();
    $payment_method_id  = $order->get_payment_method();
    $order_status_slug  = $order->get_status();
    $order_status       = wc_get_order_status_name($order_status_slug);
    $shipping_methods   = $order->get_shipping_methods();
    $shipping_label     = '';
    $needs_payment      = $order->needs_payment();

    if (!empty($shipping_methods)) {
        $first_shipping = reset($shipping_methods);
        $shipping_label = $first_shipping ? $first_shipping->get_name() : '';
    }

    /*
     * Shipping logic
     * Timezone: America/Los_Angeles
     * Mon-Fri before 2:00 PM PT = same day
     * Mon-Fri at/after 2:00 PM PT = next business day
     * Sat/Sun = next Monday
     */
    $la_timezone = new DateTimeZone('America/Los_Angeles');

    if ($order->get_date_created()) {
        $created_dt = $order->get_date_created();
        $order_timestamp = $created_dt->getTimestamp();
        $ship_dt = new DateTime('@' . $order_timestamp);
        $ship_dt->setTimezone($la_timezone);
    } else {
        $ship_dt = new DateTime('now', $la_timezone);
    }

    $day_num = (int) $ship_dt->format('N'); // 1=Mon, 7=Sun
    $hour    = (int) $ship_dt->format('G');
    $minute  = (int) $ship_dt->format('i');

    if ($day_num === 6) {
        $ship_dt->modify('next monday');
    } elseif ($day_num === 7) {
        $ship_dt->modify('next monday');
    } else {
        if ($hour > 14 || ($hour === 14 && $minute >= 0)) {
            if ($day_num === 5) {
                $ship_dt->modify('next monday');
            } else {
                $ship_dt->modify('+1 day');
            }
        }
    }

    $estimated_ship_date = $ship_dt->format('l, F j');

    /*
     * Delivery estimate
     */
    $delivery_days = 5;

    if ($shipping_label) {
        $shipping_label_lower = strtolower($shipping_label);

        if (false !== strpos($shipping_label_lower, 'ground')) {
            $delivery_days = 6;
        } elseif (false !== strpos($shipping_label_lower, 'priority')) {
            $delivery_days = 3;
        } elseif (false !== strpos($shipping_label_lower, 'express')) {
            $delivery_days = 2;
        }
    }

    $delivery_dt = clone $ship_dt;
    $delivery_dt->modify('+' . absint($delivery_days) . ' days');
    $estimated_delivery_date = $delivery_dt->format('l, F j');

    /*
     * Universal thank-you copy
     */
    $hero_title = 'Thank you for your order';
    $hero_copy  = 'We’ve received your order and you can review the full details below.';

    if (in_array($order_status_slug, array('pending', 'on-hold'), true)) {
        $hero_copy = 'We’ve received your order. Please review your order details below and complete any remaining payment steps if needed.';
    } elseif (in_array($order_status_slug, array('processing', 'completed'), true)) {
        $hero_copy = 'Your order has been received and payment has been confirmed. You can review the full order details below.';
    } elseif (in_array($order_status_slug, array('cancelled', 'failed'), true)) {
        $hero_copy = 'You can review the order details and current status below. If you need help, please contact us.';
    }

    echo '<section class="axiom-payment-confirmation-hero">';
    echo '  <h1>' . esc_html($hero_title) . '</h1>';
    echo '  <p class="axiom-payment-confirmation-copy">' . esc_html($hero_copy) . '</p>';
    echo '</section>';

    echo '<section class="axiom-payment-status-card">';
    echo '  <div class="axiom-payment-status-top">';
    echo '      <div class="axiom-payment-status-icon-wrap">';
    echo '          <div class="axiom-payment-status-icon"><i class="fa-solid fa-check"></i></div>';
    echo '      </div>';
    echo '      <div class="axiom-payment-status-heading">';
    echo '          <span>Order Number</span>';
    echo '          <h2>#' . esc_html($order_number) . '</h2>';
    echo '      </div>';
    echo '  </div>';

    echo '  <div class="axiom-payment-status-rows">';
    echo '      <div class="axiom-payment-status-row"><span>Status</span><strong>' . esc_html($order_status) . '</strong></div>';
    echo '      <div class="axiom-payment-status-row"><span>Subtotal</span><strong>' . wp_kses_post(wc_price($order_subtotal)) . '</strong></div>';
    echo '      <div class="axiom-payment-status-row"><span>Shipping</span><strong>' . wp_kses_post(wc_price($order_shipping)) . '</strong></div>';

    if ($order_tax > 0) {
        echo '      <div class="axiom-payment-status-row"><span>Tax</span><strong>' . wp_kses_post(wc_price($order_tax)) . '</strong></div>';
    }

    echo '      <div class="axiom-payment-status-row axiom-payment-status-row--total"><span>Total</span><strong>' . wp_kses_post(wc_price($order_total)) . '</strong></div>';
    echo '      <div class="axiom-payment-status-row"><span>Payment method</span><strong>' . esc_html($payment_method) . '</strong></div>';
    echo '  </div>';

    echo '  <div class="axiom-payment-estimates">';
    echo '      <div class="axiom-payment-estimate-card">';
    echo '          <span>Estimated Ship Date</span>';
    echo '          <strong>' . esc_html($estimated_ship_date) . '</strong>';
    echo '          <p>Orders placed before 2:00 PM Pacific Time, Monday through Friday, usually ship the same day. Orders placed after cutoff or on weekends ship the next business day.</p>';
    echo '      </div>';

    echo '      <div class="axiom-payment-estimate-card">';
    echo '          <span>Estimated Delivery</span>';
    echo '          <strong>' . esc_html($estimated_delivery_date) . '</strong>';
    echo '          <p>' . esc_html($shipping_label ? $shipping_label : 'Selected shipping method') . '</p>';
    echo '      </div>';
    echo '  </div>';

    /*
     * Zelle instructions
     */
    if ($payment_method_id === 'zelle' || stripos($payment_method, 'zelle') !== false) {
        echo '  <div class="axiom-payment-instructions-card">';
        echo '      <div class="axiom-payment-instructions-header">';
        echo '          <h3>Zelle Payment Instructions</h3>';
        echo '      </div>';
        echo '      <div class="axiom-payment-instructions-body">';
        echo '          <p>Please complete your payment through Zelle after placing your order.</p>';
        echo '          <div class="axiom-payment-instruction-row"><span>Zelle phone</span><strong>916-233-5312</strong></div>';
        echo '          <div class="axiom-payment-instruction-row"><span>Zelle email</span><strong>jaxferone@gmail.com</strong></div>';
        echo '          <ul class="axiom-payment-instruction-list">';
        echo '              <li>Use your order number only in the payment note.</li>';
        echo '              <li>Do not include product names or order contents.</li>';
        echo '              <li>Your order will be processed after payment is received and confirmed.</li>';
        echo '          </ul>';
        echo '      </div>';
        echo '  </div>';
    }

    /*
     * Venmo instructions
     */
    if ($payment_method_id === 'venmo' || stripos($payment_method, 'venmo') !== false) {
        echo '  <div class="axiom-payment-instructions-card">';
        echo '      <div class="axiom-payment-instructions-header">';
        echo '          <h3>Venmo Payment Instructions</h3>';
        echo '      </div>';
        echo '      <div class="axiom-payment-instructions-body">';
        echo '          <p>Please send your payment after placing your order.</p>';
        echo '          <div class="axiom-payment-instruction-row"><span>Venmo username</span><strong>@thomas-harris-axiom</strong></div>';
        echo '          <div class="axiom-payment-instruction-row"><span>Venmo link</span><strong><a href="https://venmo.com/thomas-harris-axiom" target="_blank" rel="noopener noreferrer">venmo.com/thomas-harris-axiom</a></strong></div>';
        echo '          <ul class="axiom-payment-instruction-list">';
        echo '              <li>Use your order number only in the payment note.</li>';
        echo '              <li>Do not include product names or order contents.</li>';
        echo '              <li>Your order will be processed after payment is received and confirmed.</li>';
        echo '          </ul>';
        echo '      </div>';
        echo '  </div>';
    }

    if ($needs_payment) {
        echo '  <div class="axiom-thankyou-pay-actions">';
        echo '      <a class="button alt axiom-thankyou-pay-button" href="' . esc_url($order->get_checkout_payment_url()) . '">Pay now</a>';
        echo '  </div>';
    }

    echo '</section>';
}
