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

    $order_number      = $order->get_order_number();
    $order_total       = (float) $order->get_total();
    $order_subtotal    = (float) $order->get_subtotal();
    $order_shipping    = (float) $order->get_shipping_total();
    $order_tax         = (float) $order->get_total_tax();
    $payment_method    = (string) $order->get_payment_method_title();
    $payment_method_id = (string) $order->get_payment_method();
    $order_status_slug = (string) $order->get_status();
    $order_status      = wc_get_order_status_name($order_status_slug);
    $shipping_methods  = $order->get_shipping_methods();
    $shipping_label    = '';

    if (!empty($shipping_methods)) {
        $first_shipping = reset($shipping_methods);
        $shipping_label = $first_shipping ? $first_shipping->get_name() : '';
    }

    $payment_method_id_lower = strtolower($payment_method_id);
    $payment_method_lower    = strtolower($payment_method);

    $is_zelle   = (false !== strpos($payment_method_id_lower, 'zelle') || false !== strpos($payment_method_lower, 'zelle'));
    $is_venmo   = (false !== strpos($payment_method_id_lower, 'venmo') || false !== strpos($payment_method_lower, 'venmo'));
    $is_cashapp = (false !== strpos($payment_method_id_lower, 'cashapp') || false !== strpos($payment_method_id_lower, 'cash-app') || false !== strpos($payment_method_lower, 'cash app') || false !== strpos($payment_method_lower, 'cashapp'));

    $la_timezone = new DateTimeZone('America/Los_Angeles');

    if ($order->get_date_created()) {
        $created_dt = $order->get_date_created();
        $ship_dt = new DateTime('@' . $created_dt->getTimestamp());
        $ship_dt->setTimezone($la_timezone);
    } else {
        $ship_dt = new DateTime('now', $la_timezone);
    }

    $day_num = (int) $ship_dt->format('N');
    $hour    = (int) $ship_dt->format('G');

    if ($day_num === 6) {
        $ship_dt->modify('next monday');
    } elseif ($day_num === 7) {
        $ship_dt->modify('next monday');
    } else {
        if ($hour >= 14) {
            if ($day_num === 5) {
                $ship_dt->modify('next monday');
            } else {
                $ship_dt->modify('+1 day');
            }
        }
    }

    $estimated_ship_date = $ship_dt->format('l, F j');

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

    $hero_title = 'Thank you for your order';
    $hero_copy  = 'You can review your order details and shipping timeline below.';

    if (in_array($order_status_slug, array('processing', 'completed'), true)) {
        $hero_copy = 'Your order has been received and payment has been confirmed. You can review the order details below.';
    } elseif (in_array($order_status_slug, array('pending', 'on-hold'), true)) {
        $hero_copy = 'We’ve received your order. Please review the details below.';
    } elseif (in_array($order_status_slug, array('cancelled', 'failed'), true)) {
        $hero_copy = 'You can review the order details and current status below. If you need help, please contact us.';
    }

    echo '<section class="axiom-payment-confirmation-hero">';
    echo '  <h1>' . esc_html($hero_title) . '</h1>';
    echo '  <p class="axiom-payment-confirmation-copy">' . esc_html($hero_copy) . '</p>';
    echo '</section>';

    if (function_exists('axiom_render_payment_countdown')) {
        axiom_render_payment_countdown($order);
    }

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

    if ($is_zelle) {
        echo '  <div class="axiom-payment-alert-card">';
        echo '      <div class="axiom-payment-alert-title">Send Payment Now</div>';
        echo '      <p>Use <strong>ORDER NUMBER ONLY</strong> as the payment note: <strong>#' . esc_html($order_number) . '</strong></p>';
        echo '      <p>Do not mention product names or order contents.</p>';
        echo '  </div>';

        echo '  <div class="axiom-payment-instructions-card">';
        echo '      <div class="axiom-payment-instructions-header"><h3>Zelle Payment Instructions</h3></div>';
        echo '      <div class="axiom-payment-instructions-body">';
        echo '          <p>Please complete your payment through Zelle after placing your order.</p>';

        echo '          <div class="axiom-payment-copy-field">';
        echo '              <span>Zelle phone</span>';
        echo '              <div class="axiom-payment-copy-row">';
        echo '                  <strong>916-233-5312</strong>';
        echo '                  <button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'916-233-5312\')">Copy</button>';
        echo '              </div>';
        echo '          </div>';

        echo '          <div class="axiom-payment-copy-field">';
        echo '              <span>Zelle email</span>';
        echo '              <div class="axiom-payment-copy-row">';
        echo '                  <strong>jaxferone@gmail.com</strong>';
        echo '                  <button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'jaxferone@gmail.com\')">Copy</button>';
        echo '              </div>';
        echo '          </div>';

        echo '          <div class="axiom-payment-copy-field">';
        echo '              <span>Payment note</span>';
        echo '              <div class="axiom-payment-copy-row">';
        echo '                  <strong>#' . esc_html($order_number) . '</strong>';
        echo '                  <button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'#' . esc_js($order_number) . '\')">Copy</button>';
        echo '              </div>';
        echo '          </div>';
        echo '      </div>';
        echo '  </div>';
    }

    if ($is_venmo) {
        echo '  <div class="axiom-payment-alert-card">';
        echo '      <div class="axiom-payment-alert-title">Send Payment Now</div>';
        echo '      <p>Use <strong>ORDER NUMBER ONLY</strong> as the payment note: <strong>#' . esc_html($order_number) . '</strong></p>';
        echo '      <p>Do not mention product names or order contents.</p>';
        echo '  </div>';

        echo '  <div class="axiom-payment-instructions-card">';
        echo '      <div class="axiom-payment-instructions-header"><h3>Venmo Payment Instructions</h3></div>';
        echo '      <div class="axiom-payment-instructions-body">';
        echo '          <p>Please send your payment after placing your order.</p>';

        echo '          <div class="axiom-payment-copy-field">';
        echo '              <span>Venmo username</span>';
        echo '              <div class="axiom-payment-copy-row">';
        echo '                  <strong>@thomas-harris-axiom</strong>';
        echo '                  <button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'@thomas-harris-axiom\')">Copy</button>';
        echo '              </div>';
        echo '          </div>';

        echo '          <div class="axiom-payment-copy-field">';
        echo '              <span>Venmo link</span>';
        echo '              <div class="axiom-payment-copy-row">';
        echo '                  <strong><a href="https://venmo.com/thomas-harris-axiom" target="_blank" rel="noopener noreferrer">venmo.com/thomas-harris-axiom</a></strong>';
        echo '                  <button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'https://venmo.com/thomas-harris-axiom\')">Copy</button>';
        echo '              </div>';
        echo '          </div>';

        echo '          <div class="axiom-payment-copy-field">';
        echo '              <span>Payment note</span>';
        echo '              <div class="axiom-payment-copy-row">';
        echo '                  <strong>#' . esc_html($order_number) . '</strong>';
        echo '                  <button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'#' . esc_js($order_number) . '\')">Copy</button>';
        echo '              </div>';
        echo '          </div>';
        echo '      </div>';
        echo '  </div>';
    }

    if ($is_cashapp) {
        echo '  <div class="axiom-payment-alert-card">';
        echo '      <div class="axiom-payment-alert-title">Complete Cash App Bitcoin Payment</div>';
        echo '      <p>Your order includes the <strong>5% Cash App discount</strong>.</p>';
        echo '      <p>Use <strong>ORDER NUMBER ONLY</strong> when contacting us about payment: <strong>#' . esc_html($order_number) . '</strong></p>';
        echo '  </div>';

        echo '  <div class="axiom-payment-instructions-card">';
        echo '      <div class="axiom-payment-instructions-header"><h3>Cash App Bitcoin Instructions</h3></div>';
        echo '      <div class="axiom-payment-instructions-body">';
        echo '          <p>Follow these steps to complete payment with Cash App Bitcoin.</p>';

        echo '          <ol style="margin:0 0 18px 18px; padding:0; color:#64748b; line-height:1.7;">';
        echo '              <li>Open <strong>Cash App</strong> on your phone.</li>';
        echo '              <li>Tap the <strong>Bitcoin</strong> tab inside Cash App.</li>';
        echo '              <li>If you do not already have Bitcoin, tap <strong>Buy</strong> and purchase enough BTC to cover your order total.</li>';
        echo '              <li>Tap <strong>Send</strong> on the Bitcoin screen.</li>';
        echo '              <li>Paste our exact Bitcoin address shown below into the recipient field.</li>';
        echo '              <li>Double-check the address before sending to make sure it matches exactly.</li>';
        echo '              <li>After sending, save your transaction confirmation and send us your order number and payment confirmation.</li>';
        echo '          </ol>';

        echo '          <div class="axiom-payment-copy-field">';
        echo '              <span>Bitcoin (BTC) address</span>';
        echo '              <div class="axiom-payment-copy-row">';
        echo '                  <strong style="font-size:14px; word-break:break-all;">bc1qa2c4nfzakewrxf9jcj3m8ql3n436jhzn0spgfr</strong>';
        echo '                  <button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'bc1qa2c4nfzakewrxf9jcj3m8ql3n436jhzn0spgfr\')">Copy</button>';
        echo '              </div>';
        echo '          </div>';

        echo '          <div class="axiom-payment-copy-field">';
        echo '              <span>Order number</span>';
        echo '              <div class="axiom-payment-copy-row">';
        echo '                  <strong>#' . esc_html($order_number) . '</strong>';
        echo '                  <button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'#' . esc_js($order_number) . '\')">Copy</button>';
        echo '              </div>';
        echo '          </div>';

        echo '          <div style="margin-top:18px; padding:16px; border:1px solid #dbe7f3; border-radius:18px; background:#ffffff;">';
        echo '              <strong style="display:block; margin-bottom:12px; color:#0f172a; font-size:18px; font-weight:900;">Send your transaction ID and order screenshot to us</strong>';
        echo '              <p style="margin:0 0 12px; color:#64748b; line-height:1.7;">After payment, message us your transaction ID and a screenshot of your order so we can confirm it faster.</p>';
        echo '              <div style="display:grid; gap:12px;">';
        echo '                  <a href="https://wa.me/15307019349" target="_blank" rel="noopener noreferrer" class="axiom-copy-button" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">WhatsApp: 530-701-9349</a>';
        echo '                  <a href="https://t.me/axiompeptides" target="_blank" rel="noopener noreferrer" class="axiom-copy-button" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">Telegram: @axiompeptides</a>';
        echo '              </div>';
        echo '          </div>';

        echo '          <div style="margin-top:18px; padding:16px; border:1px solid #dbe7f3; border-radius:18px; background:#f8fbff;">';
        echo '              <strong style="display:block; margin-bottom:10px; color:#0f172a;">Good to know</strong>';
        echo '              <ul style="margin:0; padding-left:18px; color:#64748b; line-height:1.7;">';
        echo '                  <li>You get <strong>5% off</strong> because crypto payments save us processing fees.</li>';
        echo '                  <li>Cash App Bitcoin is usually one of the easiest ways to pay with crypto.</li>';
        echo '                  <li>If you need to buy BTC first in Cash App, it usually only takes a moment before you can send it.</li>';
        echo '                  <li>Confirmation usually takes a short time after sending. Message us with your order number and transaction ID so we can confirm it faster.</li>';
        echo '              </ul>';
        echo '          </div>';

        echo '      </div>';
        echo '  </div>';
    }

    echo '  <div class="axiom-payment-next-steps">';
    echo '      <h3>What happens next?</h3>';

    echo '      <div class="axiom-payment-next-step">';
    echo '          <div class="axiom-payment-next-step-number">1</div>';
    echo '          <div class="axiom-payment-next-step-copy">';
    echo '              <strong>Order Processing</strong>';
    echo '              <p>Orders placed before 2:00 PM Pacific Time, Monday through Friday, usually ship the same business day.</p>';
    echo '          </div>';
    echo '      </div>';

    echo '      <div class="axiom-payment-next-step">';
    echo '          <div class="axiom-payment-next-step-number">2</div>';
    echo '          <div class="axiom-payment-next-step-copy">';
    echo '              <strong>Shipping Confirmation</strong>';
    echo '              <p>You will receive tracking information once your order ships.</p>';
    echo '          </div>';
    echo '      </div>';

    echo '      <div class="axiom-payment-next-step">';
    echo '          <div class="axiom-payment-next-step-number">3</div>';
    echo '          <div class="axiom-payment-next-step-copy">';
    echo '              <strong>Delivery</strong>';
    echo '              <p>Estimated ship date is <strong>' . esc_html($estimated_ship_date) . '</strong> and estimated delivery is <strong>' . esc_html($estimated_delivery_date) . '</strong>.</p>';
    echo '          </div>';
    echo '      </div>';

    echo '  </div>';
    echo '</section>';

    echo '<script>
    function axiomCopyValue(button, value) {
        if (!navigator.clipboard) {
            return;
        }

        navigator.clipboard.writeText(value).then(function() {
            var originalText = button.innerText;
            button.innerText = "Copied!";
            button.classList.add("is-copied");

            setTimeout(function() {
                button.innerText = originalText;
                button.classList.remove("is-copied");
            }, 1400);
        });
    }
    </script>';
}
