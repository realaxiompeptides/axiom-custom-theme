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

    axiom_enqueue_card_fallback_assets();

    $order_number    = $order->get_order_number();
    $order_total     = wc_format_decimal($order->get_total(), 2);

    $venmo_username  = '@thomas-harris-axiom';
    $venmo_link      = 'https://venmo.com/code?user_id=4564578725790758651&created=1777431398.570389&printed=1';

    $zelle_phone     = '916-233-5312';

    $bitcoin_address = 'bc1qtaef69mdkj8q25z32cjw3h3kayv207ydcgejsy';

    $theme_uri       = get_template_directory_uri();
    $venmo_icon      = $theme_uri . '/assets/images/venmo.jpg';
    $zelle_icon      = $theme_uri . '/assets/images/zelle.jpg';
    $bitcoin_icon    = $theme_uri . '/assets/images/bitcoin.jpg';
    ?>

    <section class="axiom-card-fallback-box" id="axiomCardFallbackBox">
        <div class="axiom-card-fallback-head">
            <h2>Card payment did not complete</h2>
            <p>Use Venmo, Zelle, or Bitcoin below to complete your order without delay.</p>
        </div>

        <div class="axiom-fallback-quick-actions">
            <button type="button" class="axiom-fallback-action-btn" data-copy-target="axiomFallbackOrderNumber">
                <span>Order Number</span>
                <strong id="axiomFallbackOrderNumber">#<?php echo esc_html($order_number); ?></strong>
                <em>Tap to copy</em>
            </button>

            <button type="button" class="axiom-fallback-action-btn" data-copy-target="axiomFallbackOrderTotal">
                <span>Total</span>
                <strong id="axiomFallbackOrderTotal"><?php echo esc_html($order_total); ?></strong>
                <em>Tap to copy</em>
            </button>
        </div>

        <div class="axiom-card-fallback-grid">
            <div class="axiom-card-fallback-method">
                <div class="fallback-method-top">
                    <img src="<?php echo esc_url($venmo_icon); ?>" alt="Venmo" class="fallback-method-icon">
                    <div>
                        <h3>Venmo</h3>
                        <p>Send the exact total. Use order #<?php echo esc_html($order_number); ?> as the note.</p>
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
                        <p>Send the exact total. Add order #<?php echo esc_html($order_number); ?> if your bank allows notes.</p>
                    </div>
                </div>

                <div class="axiom-card-fallback-copy-row">
                    <span id="axiomFallbackZellePhone"><?php echo esc_html($zelle_phone); ?></span>
                    <button type="button" class="axiom-card-fallback-copy" data-copy-target="axiomFallbackZellePhone">Copy</button>
                </div>
            </div>

            <div class="axiom-card-fallback-method axiom-card-fallback-method-wide">
                <div class="fallback-method-top">
                    <img src="<?php echo esc_url($bitcoin_icon); ?>" alt="Bitcoin" class="fallback-method-icon">
                    <div>
                        <h3>Bitcoin</h3>
                        <p>Send the exact total in BTC equivalent. Use order #<?php echo esc_html($order_number); ?> when contacting support after payment.</p>
                    </div>
                </div>

                <div class="axiom-card-fallback-copy-row bitcoin-address-row">
                    <span id="axiomFallbackBitcoinAddress"><?php echo esc_html($bitcoin_address); ?></span>
                    <button type="button" class="axiom-card-fallback-copy" data-copy-target="axiomFallbackBitcoinAddress">Copy Bitcoin Address</button>
                </div>
            </div>
        </div>
    </section>

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
        var button = event.target.closest('.axiom-card-fallback-copy, .axiom-fallback-action-btn');

        if (!button) {
            return;
        }

        var targetId = button.getAttribute('data-copy-target');
        var target = document.getElementById(targetId);

        if (!target) {
            return;
        }

        navigator.clipboard.writeText(target.textContent.trim()).then(function() {
            var oldText = '';

            if (button.classList.contains('axiom-fallback-action-btn')) {
                var em = button.querySelector('em');

                if (!em) {
                    return;
                }

                oldText = em.textContent;
                em.textContent = 'Copied';

                setTimeout(function() {
                    em.textContent = oldText;
                }, 1400);

                return;
            }

            oldText = button.textContent;
            button.textContent = 'Copied';

            setTimeout(function() {
                button.textContent = oldText;
            }, 1400);
        });
    });
    </script>

    <?php
}

function axiom_enqueue_card_fallback_assets() {
    $theme_uri  = get_template_directory_uri();
    $theme_path = get_template_directory();

    $css_file = '/assets/css/checkout/card-fallback-thankyou.css';

    if (file_exists($theme_path . $css_file)) {
        wp_enqueue_style(
            'axiom-card-fallback-thankyou',
            $theme_uri . $css_file,
            array(),
            filemtime($theme_path . $css_file)
        );
    }
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
