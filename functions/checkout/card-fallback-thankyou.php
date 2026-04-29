<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_thankyou', 'axiom_card_fallback_payment_methods_thankyou', 18);

function axiom_card_fallback_payment_methods_thankyou($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order instanceof WC_Order) {
        return;
    }

    $status = $order->get_status();

    $allowed_statuses = array(
        'failed',
        'pending',
        'on-hold',
        'cancelled',
        'canceled',
    );

    if (!in_array($status, $allowed_statuses, true)) {
        return;
    }

    if (!axiom_fallback_order_was_card_payment($order)) {
        return;
    }

    $order_number   = $order->get_order_number();
    $order_total    = $order->get_formatted_order_total();
    $venmo_username = '@YOUR-VENMO-HERE';
    $zelle_email    = 'YOUR-ZELLE-EMAIL-HERE';
    ?>

    <section class="axiom-card-fallback-box" id="axiomCardFallbackBox">
        <div class="axiom-card-fallback-head">
            <h2>Card payment did not complete</h2>
            <p>Complete this order with Venmo or Zelle to avoid delays.</p>
        </div>

        <div class="axiom-card-fallback-mini">
            <span>Order #<?php echo esc_html($order_number); ?></span>
            <strong><?php echo wp_kses_post($order_total); ?></strong>
        </div>

        <div class="axiom-card-fallback-grid">
            <div class="axiom-card-fallback-method">
                <div>
                    <h3>Venmo</h3>
                    <p>Send exact total. Use order #<?php echo esc_html($order_number); ?> as the note.</p>
                </div>

                <div class="axiom-card-fallback-copy-row">
                    <span id="axiomFallbackVenmoUsername"><?php echo esc_html($venmo_username); ?></span>
                    <button type="button" class="axiom-card-fallback-copy" data-copy-target="axiomFallbackVenmoUsername">Copy</button>
                </div>
            </div>

            <div class="axiom-card-fallback-method">
                <div>
                    <h3>Zelle</h3>
                    <p>Send exact total. Add order #<?php echo esc_html($order_number); ?> if your bank allows notes.</p>
                </div>

                <div class="axiom-card-fallback-copy-row">
                    <span id="axiomFallbackZelleEmail"><?php echo esc_html($zelle_email); ?></span>
                    <button type="button" class="axiom-card-fallback-copy" data-copy-target="axiomFallbackZelleEmail">Copy</button>
                </div>
            </div>
        </div>
    </section>

    <style>
        .axiom-card-fallback-box {
            margin: 18px 0 22px !important;
            padding: 18px !important;
            border-radius: 22px !important;
            border: 1px solid rgba(59,111,224,.22) !important;
            background: #f7fbff !important;
            box-shadow: 0 10px 24px rgba(7,17,31,.06) !important;
        }

        .axiom-card-fallback-head h2 {
            margin: 0 0 6px !important;
            color: #07111f !important;
            font-size: 24px !important;
            line-height: 1.15 !important;
            font-weight: 900 !important;
            letter-spacing: -0.03em !important;
        }

        .axiom-card-fallback-head p {
            margin: 0 !important;
            color: #667085 !important;
            font-size: 15px !important;
            line-height: 1.45 !important;
        }

        .axiom-card-fallback-mini {
            margin: 14px 0 !important;
            padding: 12px 14px !important;
            border-radius: 14px !important;
            background: #ffffff !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 10px !important;
            font-size: 15px !important;
            color: #07111f !important;
        }

        .axiom-card-fallback-mini strong {
            font-size: 17px !important;
            font-weight: 900 !important;
        }

        .axiom-card-fallback-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }

        .axiom-card-fallback-method {
            padding: 14px !important;
            border-radius: 18px !important;
            background: #ffffff !important;
            border: 1px solid rgba(7,17,31,.08) !important;
        }

        .axiom-card-fallback-method h3 {
            margin: 0 0 5px !important;
            color: #07111f !important;
            font-size: 18px !important;
            font-weight: 900 !important;
        }

        .axiom-card-fallback-method p {
            margin: 0 0 10px !important;
            color: #667085 !important;
            font-size: 13px !important;
            line-height: 1.45 !important;
        }

        .axiom-card-fallback-copy-row {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 8px !important;
            padding: 10px !important;
            border-radius: 12px !important;
            background: #eef6ff !important;
            color: #07111f !important;
            font-weight: 800 !important;
            font-size: 13px !important;
            word-break: break-word !important;
        }

        .axiom-card-fallback-copy {
            border: none !important;
            border-radius: 999px !important;
            padding: 8px 11px !important;
            background: #3b6fe0 !important;
            color: #ffffff !important;
            font-size: 12px !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            flex: 0 0 auto !important;
        }

        @media (max-width: 768px) {
            .axiom-card-fallback-box {
                margin: 14px 0 18px !important;
                padding: 14px !important;
                border-radius: 18px !important;
            }

            .axiom-card-fallback-head h2 {
                font-size: 21px !important;
            }

            .axiom-card-fallback-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var box = document.getElementById('axiomCardFallbackBox');

        if (!box) {
            return;
        }

        var headings = document.querySelectorAll('h1, h2, h3, strong');
        var target = null;

        headings.forEach(function (heading) {
            if (heading.textContent && heading.textContent.trim().toLowerCase().includes('what happens next')) {
                target = heading.closest('section, div, article') || heading;
            }
        });

        if (target && target.parentNode) {
            target.parentNode.insertBefore(box, target);
        }
    });

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
        'pay by card',
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
