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
    $venmo_username = '@thomas-harris-axiom';
    $venmo_link     = 'https://venmo.com/code?user_id=4564578725790758651&created=1777431398.570389&printed=1';
    $zelle_phone    = '916-233-5312';

    $theme_uri      = get_template_directory_uri();
    $venmo_icon     = $theme_uri . '/assets/images/venmo.jpg';
    $zelle_icon     = $theme_uri . '/assets/images/zelle.jpg';
    ?>

    <section class="axiom-card-fallback-box" id="axiomCardFallbackBox">
        <div class="axiom-card-fallback-head">
            <h2>Card payment did not complete</h2>
            <p>Complete this order with Venmo or Zelle to avoid delays.</p>
        </div>

        <div class="axiom-card-fallback-mini">
            <div>
                <span>Order</span>
                <strong id="axiomFallbackOrderNumber">#<?php echo esc_html($order_number); ?></strong>
            </div>

            <button type="button" class="axiom-card-fallback-copy small-copy" data-copy-target="axiomFallbackOrderNumber">
                Copy Order #
            </button>

            <div class="fallback-total">
                <span>Total</span>
                <strong><?php echo wp_kses_post($order_total); ?></strong>
            </div>
        </div>

        <div class="axiom-card-fallback-grid">
            <div class="axiom-card-fallback-method">
                <div class="fallback-method-top">
                    <img src="<?php echo esc_url($venmo_icon); ?>" alt="Venmo" class="fallback-method-icon">
                    <div>
                        <h3>Venmo</h3>
                        <p>Send exact total. Use order #<?php echo esc_html($order_number); ?> as the note.</p>
                    </div>
                </div>

                <div class="axiom-card-fallback-copy-row">
                    <span id="axiomFallbackVenmoUsername"><?php echo esc_html($venmo_username); ?></span>
                    <button type="button" class="axiom-card-fallback-copy" data-copy-target="axiomFallbackVenmoUsername">Copy</button>
                </div>

                <a class="axiom-card-fallback-open" href="<?php echo esc_url($venmo_link); ?>" target="_blank" rel="noopener noreferrer">
                    Open Venmo
                </a>
            </div>

            <div class="axiom-card-fallback-method">
                <div class="fallback-method-top">
                    <img src="<?php echo esc_url($zelle_icon); ?>" alt="Zelle" class="fallback-method-icon">
                    <div>
                        <h3>Zelle</h3>
                        <p>Send exact total. Add order #<?php echo esc_html($order_number); ?> if your bank allows notes.</p>
                    </div>
                </div>

                <div class="axiom-card-fallback-copy-row">
                    <span id="axiomFallbackZellePhone"><?php echo esc_html($zelle_phone); ?></span>
                    <button type="button" class="axiom-card-fallback-copy" data-copy-target="axiomFallbackZellePhone">Copy</button>
                </div>
            </div>
        </div>
    </section>

    <style>
        .axiom-card-fallback-box {
            margin: 14px 0 18px !important;
            padding: 14px !important;
            border-radius: 20px !important;
            border: 1px solid rgba(59,111,224,.22) !important;
            background: #f7fbff !important;
            box-shadow: 0 10px 24px rgba(7,17,31,.06) !important;
        }

        .axiom-card-fallback-head h2 {
            margin: 0 0 5px !important;
            color: #07111f !important;
            font-size: 20px !important;
            line-height: 1.15 !important;
            font-weight: 900 !important;
            letter-spacing: -0.03em !important;
        }

        .axiom-card-fallback-head p {
            margin: 0 !important;
            color: #667085 !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
        }

        .axiom-card-fallback-mini {
            margin: 12px 0 !important;
            padding: 10px !important;
            border-radius: 14px !important;
            background: #ffffff !important;
            display: grid !important;
            grid-template-columns: 1fr auto 1fr !important;
            align-items: center !important;
            gap: 8px !important;
            color: #07111f !important;
        }

        .axiom-card-fallback-mini span {
            display: block !important;
            color: #667085 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: .04em !important;
        }

        .axiom-card-fallback-mini strong {
            font-size: 15px !important;
            font-weight: 900 !important;
        }

        .fallback-total {
            text-align: right !important;
        }

        .axiom-card-fallback-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px !important;
        }

        .axiom-card-fallback-method {
            padding: 12px !important;
            border-radius: 17px !important;
            background: #ffffff !important;
            border: 1px solid rgba(7,17,31,.08) !important;
        }

        .fallback-method-top {
            display: flex !important;
            gap: 10px !important;
            align-items: flex-start !important;
            margin-bottom: 10px !important;
        }

        .fallback-method-icon {
            width: 34px !important;
            height: 34px !important;
            object-fit: cover !important;
            border-radius: 10px !important;
            flex: 0 0 auto !important;
            box-shadow: 0 6px 14px rgba(7,17,31,.08) !important;
        }

        .axiom-card-fallback-method h3 {
            margin: 0 0 3px !important;
            color: #07111f !important;
            font-size: 16px !important;
            font-weight: 900 !important;
        }

        .axiom-card-fallback-method p {
            margin: 0 !important;
            color: #667085 !important;
            font-size: 12px !important;
            line-height: 1.35 !important;
        }

        .axiom-card-fallback-copy-row {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 8px !important;
            padding: 9px !important;
            border-radius: 12px !important;
            background: #eef6ff !important;
            color: #07111f !important;
            font-weight: 800 !important;
            font-size: 12px !important;
            word-break: break-word !important;
        }

        .axiom-card-fallback-copy,
        .axiom-card-fallback-open {
            border: none !important;
            border-radius: 999px !important;
            padding: 7px 10px !important;
            background: #3b6fe0 !important;
            color: #ffffff !important;
            font-size: 11px !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            text-decoration: none !important;
            display: inline-block !important;
            flex: 0 0 auto !important;
        }

        .small-copy {
            padding: 8px 10px !important;
            white-space: nowrap !important;
        }

        .axiom-card-fallback-open {
            margin-top: 9px !important;
            width: 100% !important;
            text-align: center !important;
        }

        @media (max-width: 768px) {
            .axiom-card-fallback-box {
                padding: 13px !important;
            }

            .axiom-card-fallback-head h2 {
                font-size: 19px !important;
            }

            .axiom-card-fallback-grid {
                grid-template-columns: 1fr !important;
                gap: 9px !important;
            }

            .axiom-card-fallback-mini {
                grid-template-columns: 1fr !important;
                text-align: left !important;
            }

            .fallback-total {
                text-align: left !important;
            }

            .small-copy {
                width: 100% !important;
                text-align: center !important;
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
