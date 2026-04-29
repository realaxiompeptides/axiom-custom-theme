<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_thankyou', 'axiom_card_fallback_payment_methods_thankyou', 35);

function axiom_card_fallback_payment_methods_thankyou($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order instanceof WC_Order) {
        return;
    }

    $status = $order->get_status();

    if (!in_array($status, array('failed', 'pending', 'on-hold'), true)) {
        return;
    }

    if (!axiom_fallback_order_was_card_payment($order)) {
        return;
    }

    $order_number = $order->get_order_number();
    $order_total  = $order->get_formatted_order_total();

    $venmo_username = '@thomas-harris-axiom';
    $venmo_link     = 'https://venmo.com/thomas-harris-axiom';
    $zelle_email    = function_exists('axiom_zelle_display_value') ? axiom_zelle_display_value() : 'jaxferone@gmail.com';
    ?>

    <section class="axiom-card-fallback-box">
        <h2>Card Payment Not Confirmed</h2>

        <p>
            Your order was received, but your card payment appears to be failed, pending, or not fully confirmed.
            To avoid delays, you can complete payment using Venmo or Zelle below.
        </p>

        <div class="axiom-card-fallback-order">
            <strong>Order #<?php echo esc_html($order_number); ?></strong>
            <span>Total: <?php echo wp_kses_post($order_total); ?></span>
        </div>

        <div class="axiom-card-fallback-grid">
            <div class="axiom-card-fallback-method">
                <h3>Venmo</h3>
                <p>Send the exact order total and use your order number only as the note.</p>

                <div class="axiom-card-fallback-copy-row">
                    <span id="axiomFallbackVenmoUsername"><?php echo esc_html($venmo_username); ?></span>
                    <button type="button" class="axiom-card-fallback-copy" data-copy-target="axiomFallbackVenmoUsername">Copy</button>
                </div>

                <a href="<?php echo esc_url($venmo_link); ?>" target="_blank" rel="noopener noreferrer" class="axiom-card-fallback-link">
                    Open Venmo
                </a>
            </div>

            <div class="axiom-card-fallback-method">
                <h3>Zelle</h3>
                <p>Send the exact order total and use your order number if your bank allows a note.</p>

                <div class="axiom-card-fallback-copy-row">
                    <span id="axiomFallbackZelleEmail"><?php echo esc_html($zelle_email); ?></span>
                    <button type="button" class="axiom-card-fallback-copy" data-copy-target="axiomFallbackZelleEmail">Copy</button>
                </div>
            </div>
        </div>

        <div class="axiom-card-fallback-note">
            Payment note: <strong>#<?php echo esc_html($order_number); ?></strong>
        </div>
    </section>

    <style>
        .axiom-card-fallback-box {
            margin: 28px 0;
            padding: 22px;
            border-radius: 22px;
            border: 1px solid rgba(59,111,224,.22);
            background: #f5f9ff;
            box-shadow: 0 14px 34px rgba(7,17,31,.08);
        }

        .axiom-card-fallback-box h2 {
            margin: 0 0 10px;
            color: #07111f;
            font-size: 26px;
            font-weight: 900;
        }

        .axiom-card-fallback-box p {
            color: #526174;
            line-height: 1.6;
        }

        .axiom-card-fallback-order {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 18px 0;
            padding: 14px 16px;
            border-radius: 14px;
            background: #ffffff;
            font-weight: 800;
        }

        .axiom-card-fallback-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .axiom-card-fallback-method {
            padding: 18px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid rgba(7,17,31,.08);
        }

        .axiom-card-fallback-method h3 {
            margin: 0 0 8px;
            color: #07111f;
            font-size: 20px;
            font-weight: 900;
        }

        .axiom-card-fallback-copy-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 12px;
            border-radius: 12px;
            background: #eef6ff;
            font-weight: 800;
        }

        .axiom-card-fallback-copy,
        .axiom-card-fallback-link {
            border: none;
            border-radius: 999px;
            padding: 10px 14px;
            background: #3b6fe0;
            color: #ffffff;
            font-weight: 900;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 12px;
        }

        .axiom-card-fallback-note {
            margin-top: 16px;
            padding: 14px;
            border-radius: 14px;
            background: #ffffff;
            color: #07111f;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .axiom-card-fallback-grid,
            .axiom-card-fallback-order {
                grid-template-columns: 1fr;
                flex-direction: column;
            }
        }
    </style>

    <script>
    document.addEventListener('click', function(event) {
        var button = event.target.closest('.axiom-card-fallback-copy');

        if (!button) {
            return;
        }

        var targetId = button.getAttribute('data-copy-target');
        var target = document.getElementById(targetId);

        if (!target) {
            return;
        }

        navigator.clipboard.writeText(target.textContent.trim()).then(function() {
            var originalText = button.textContent;
            button.textContent = 'Copied';

            setTimeout(function() {
                button.textContent = originalText;
            }, 1400);
        });
    });
    </script>

    <?php
}

function axiom_fallback_order_was_card_payment($order) {
    if (!$order instanceof WC_Order) {
        return false;
    }

    $method_id    = strtolower((string) $order->get_payment_method());
    $method_title = strtolower((string) $order->get_payment_method_title());

    $haystack = $method_id . ' ' . $method_title;

    $manual_or_bank_terms = array(
        'zelle',
        'venmo',
        'cash app',
        'cashapp',
        'bitcoin',
        'crypto',
        'btc',
        'usdt',
        'ethereum',
        'same day',
        'same-day',
        'bank payment',
        'bank transfer',
        'ach',
        'plaid',
        'link money',
        'link.money',
    );

    foreach ($manual_or_bank_terms as $term) {
        if (strpos($haystack, $term) !== false) {
            return false;
        }
    }

    $card_terms = array(
        'card',
        'credit',
        'debit',
        'visa',
        'mastercard',
        'amex',
        'american express',
        'discover',
        'stripe',
        'bankful',
        'gettrx',
        'square',
        'authorize',
        'paygate',
    );

    foreach ($card_terms as $term) {
        if (strpos($haystack, $term) !== false) {
            return true;
        }
    }

    return false;
}
