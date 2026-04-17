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

    static $axiom_thankyou_header_assets_printed = false;

    $order_number      = $order->get_order_number();
    $payment_method    = (string) $order->get_payment_method_title();
    $payment_method_id = (string) $order->get_payment_method();
    $order_status_slug = (string) $order->get_status();
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
    $is_cashapp = (
        false !== strpos($payment_method_id_lower, 'cashapp') ||
        false !== strpos($payment_method_id_lower, 'cash-app') ||
        false !== strpos($payment_method_lower, 'cash app') ||
        false !== strpos($payment_method_lower, 'cashapp')
    );

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

    if (!$axiom_thankyou_header_assets_printed) {
        $axiom_thankyou_header_assets_printed = true;

        echo '<style>
        .axiom-payment-confirmation-hero{
            margin:0 0 28px;
            padding:28px 24px;
            border:1px solid #dbe7f3;
            border-radius:28px;
            background:#ffffff;
            text-align:center;
        }
        .axiom-payment-confirmation-hero h1{
            margin:0 0 14px;
            color:#0f172a;
            font-size:clamp(34px, 6vw, 58px);
            line-height:1.02;
            font-weight:900;
            letter-spacing:-0.04em;
        }
        .axiom-payment-confirmation-copy{
            max-width:820px;
            margin:0 auto;
            color:#66748b;
            font-size:18px;
            line-height:1.75;
        }

        .axiom-payment-alert-card{
            margin:0 0 18px;
            padding:22px 22px;
            border:1px solid #d15a4e;
            border-radius:26px;
            background:#fff4f2;
        }
        .axiom-payment-alert-title{
            margin:0 0 10px;
            color:#c94035;
            font-size:22px;
            line-height:1.2;
            font-weight:900;
        }
        .axiom-payment-alert-card p{
            margin:0 0 10px;
            color:#7b2b25;
            font-size:17px;
            line-height:1.6;
            font-weight:700;
        }
        .axiom-payment-alert-card p:last-child{
            margin-bottom:0;
        }

        .axiom-payment-instructions-card{
            margin:0 0 24px;
            padding:24px;
            border:1px solid #dbe7f3;
            border-radius:28px;
            background:#f8fbff;
        }
        .axiom-payment-instructions-header{
            margin:0 0 14px;
        }
        .axiom-payment-instructions-header h3{
            margin:0;
            color:#0f172a;
            font-size:clamp(26px, 4vw, 42px);
            line-height:1.05;
            font-weight:900;
            letter-spacing:-0.03em;
        }
        .axiom-payment-instructions-body > p{
            margin:0 0 18px;
            color:#64748b;
            font-size:18px;
            line-height:1.7;
        }
        .axiom-payment-instructions-body ol{
            margin:0 0 18px 22px !important;
            padding:0 !important;
            color:#64748b !important;
            line-height:1.8 !important;
            font-size:17px;
        }
        .axiom-payment-instructions-body li{
            margin-bottom:10px;
        }

        .axiom-payment-copy-field{
            margin:0 0 18px;
        }
        .axiom-payment-copy-field > span{
            display:block;
            margin:0 0 10px;
            color:#6b778c;
            font-size:15px;
            line-height:1.4;
            font-weight:900;
            letter-spacing:0.04em;
            text-transform:uppercase;
        }
        .axiom-payment-copy-row{
            display:flex;
            align-items:center;
            gap:14px;
            flex-wrap:wrap;
        }
        .axiom-payment-copy-row strong{
            flex:1 1 auto;
            min-height:60px;
            display:flex;
            align-items:center;
            padding:0 20px;
            border:1px solid #dbe7f3;
            border-radius:20px;
            background:#ffffff;
            color:#0f172a;
            font-size:18px;
            line-height:1.45;
            font-weight:900;
            box-sizing:border-box;
        }
        .axiom-payment-copy-row strong a{
            color:#0f172a;
            text-decoration:none;
            word-break:break-word;
        }

        .axiom-copy-button{
            min-width:170px;
            min-height:60px;
            padding:0 24px;
            border:0;
            border-radius:20px;
            background:linear-gradient(135deg,#5ca8e3 0%,#3a88c5 100%);
            color:#ffffff;
            font-size:18px;
            line-height:1;
            font-weight:900;
            cursor:pointer;
            box-shadow:0 12px 24px rgba(58,136,197,0.14);
        }
        .axiom-copy-button.is-copied{
            opacity:0.88;
        }

        .axiom-payment-contact-box{
            margin-top:18px;
            padding:18px;
            border:1px solid #dbe7f3;
            border-radius:20px;
            background:#ffffff;
        }
        .axiom-payment-contact-box strong.contact-title{
            display:block;
            margin:0 0 12px;
            color:#0f172a;
            font-size:18px;
            line-height:1.5;
            font-weight:900;
        }
        .axiom-payment-contact-links{
            display:grid;
            gap:10px;
        }
        .axiom-payment-contact-links a{
            color:#2c57b7;
            text-decoration:none;
            font-size:17px;
            line-height:1.65;
            font-weight:700;
            word-break:break-word;
        }
        .axiom-payment-contact-links a strong{
            color:#0f172a;
            font-weight:900;
        }

        .axiom-payment-note-box{
            margin-top:18px;
            padding:18px;
            border:1px solid #dbe7f3;
            border-radius:20px;
            background:#f8fbff;
        }
        .axiom-payment-note-box strong.note-title{
            display:block;
            margin:0 0 12px;
            color:#0f172a;
            font-size:18px;
            line-height:1.4;
            font-weight:900;
        }
        .axiom-payment-note-box ul{
            margin:0;
            padding-left:20px;
            color:#64748b;
            font-size:17px;
            line-height:1.75;
        }
        .axiom-payment-note-box li{
            margin-bottom:10px;
        }
        .axiom-payment-note-box li:last-child{
            margin-bottom:0;
        }

        .axiom-payment-next-steps{
            margin:26px 0 0;
            padding:26px 24px;
            border:1px solid #dbe7f3;
            border-radius:28px;
            background:#ffffff;
        }
        .axiom-payment-next-steps h3{
            margin:0 0 18px;
            color:#0f172a;
            font-size:32px;
            line-height:1.08;
            font-weight:900;
            letter-spacing:-0.03em;
        }
        .axiom-payment-next-step{
            display:grid;
            grid-template-columns:56px minmax(0,1fr);
            gap:16px;
            align-items:start;
            padding:16px 0;
            border-top:1px solid #e6eef6;
        }
        .axiom-payment-next-step:first-of-type{
            border-top:0;
            padding-top:0;
        }
        .axiom-payment-next-step-number{
            width:56px;
            height:56px;
            border-radius:999px;
            background:#edf7ff;
            color:#5aa8df;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:22px;
            font-weight:900;
            line-height:1;
        }
        .axiom-payment-next-step-copy strong{
            display:block;
            margin:0 0 8px;
            color:#0f172a;
            font-size:20px;
            line-height:1.25;
            font-weight:900;
        }
        .axiom-payment-next-step-copy p{
            margin:0;
            color:#64748b;
            font-size:17px;
            line-height:1.7;
        }

        @media (max-width: 767px){
            .axiom-payment-confirmation-hero{
                margin-bottom:22px;
                padding:24px 18px;
                border-radius:24px;
            }
            .axiom-payment-confirmation-copy{
                font-size:16px;
                line-height:1.7;
            }

            .axiom-payment-alert-card{
                padding:18px;
                border-radius:22px;
            }
            .axiom-payment-alert-title{
                font-size:18px;
            }
            .axiom-payment-alert-card p{
                font-size:15px;
            }

            .axiom-payment-instructions-card{
                padding:18px;
                border-radius:24px;
                margin-bottom:20px;
            }
            .axiom-payment-instructions-body > p,
            .axiom-payment-instructions-body ol,
            .axiom-payment-note-box ul,
            .axiom-payment-next-step-copy p{
                font-size:15px;
                line-height:1.7;
            }

            .axiom-payment-copy-row{
                gap:12px;
            }
            .axiom-payment-copy-row strong{
                width:100%;
                min-height:56px;
                padding:0 16px;
                font-size:16px;
                border-radius:18px;
            }
            .axiom-copy-button{
                width:100%;
                min-width:0;
                min-height:56px;
                font-size:16px;
                border-radius:18px;
            }

            .axiom-payment-contact-box,
            .axiom-payment-note-box{
                padding:16px;
                border-radius:18px;
            }
            .axiom-payment-contact-box strong.contact-title,
            .axiom-payment-note-box strong.note-title{
                font-size:16px;
            }
            .axiom-payment-contact-links a{
                font-size:15px;
                line-height:1.6;
            }

            .axiom-payment-next-steps{
                padding:22px 18px;
                border-radius:24px;
            }
            .axiom-payment-next-steps h3{
                font-size:26px;
            }
            .axiom-payment-next-step{
                grid-template-columns:46px minmax(0,1fr);
                gap:12px;
                padding:14px 0;
            }
            .axiom-payment-next-step-number{
                width:46px;
                height:46px;
                font-size:18px;
            }
            .axiom-payment-next-step-copy strong{
                font-size:17px;
            }
        }
        </style>';

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

    echo '<section class="axiom-payment-confirmation-hero">';
    echo '  <h1>' . esc_html($hero_title) . '</h1>';
    echo '  <p class="axiom-payment-confirmation-copy">' . esc_html($hero_copy) . '</p>';
    echo '</section>';

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

        echo '          <ol>';
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

        echo '          <div class="axiom-payment-contact-box">';
        echo '              <strong class="contact-title">Please message your order number to WhatsApp, Telegram, or our email.</strong>';
        echo '              <div class="axiom-payment-contact-links">';
        echo '                  <a href="https://wa.me/15307019349" target="_blank" rel="noopener noreferrer"><strong>WhatsApp:</strong> 530-701-9349</a>';
        echo '                  <a href="https://t.me/axiompeptides" target="_blank" rel="noopener noreferrer"><strong>Telegram:</strong> @axiompeptides</a>';
        echo '                  <a href="mailto:realaxiompeptides@gmail.com"><strong>Email:</strong> realaxiompeptides@gmail.com</a>';
        echo '              </div>';
        echo '          </div>';

        echo '          <div class="axiom-payment-note-box">';
        echo '              <strong class="note-title">Good to know</strong>';
        echo '              <ul>';
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
}
