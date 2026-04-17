<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manual payment gateways that should show the countdown.
 * ONLY manual methods: Zelle, Venmo, Cash App.
 */
function axiom_countdown_manual_gateways() {
    return array(
        'zelle',
        'venmo',
        'cashapp',
    );
}

/**
 * Check if order uses a manual payment method.
 */
function axiom_countdown_is_manual_order($order) {
    if (!$order instanceof WC_Order) {
        return false;
    }

    $gateway_id = strtolower(trim((string) $order->get_payment_method()));

    if (!$gateway_id) {
        return false;
    }

    foreach (axiom_countdown_manual_gateways() as $manual_gateway) {
        if (strpos($gateway_id, strtolower($manual_gateway)) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Render payment countdown.
 */
function axiom_render_payment_countdown($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    if (!axiom_countdown_is_manual_order($order)) {
        return;
    }

    $status = (string) $order->get_status();

    if (!in_array($status, array('pending', 'on-hold'), true)) {
        return;
    }

    $created = $order->get_date_created();
    if (!$created) {
        return;
    }

    $hold_minutes = 30;
    $expires_at   = $created->getTimestamp() + ($hold_minutes * 60);
    $now          = time();

    if ($expires_at <= $now) {
        return;
    }

    $countdown_id = 'axiom-payment-countdown-' . absint($order->get_id());
    ?>
    <div class="axiom-payment-countdown" data-expires="<?php echo esc_attr($expires_at); ?>" id="<?php echo esc_attr($countdown_id); ?>">
        <div class="axiom-payment-countdown__icon" aria-hidden="true">
            <i class="fa-solid fa-clock"></i>
        </div>

        <div class="axiom-payment-countdown__content">
            <div class="axiom-payment-countdown__title">Your order is held for 30 minutes</div>
            <div class="axiom-payment-countdown__copy">
                Please complete payment before the timer ends so your items stay reserved.
            </div>
            <div class="axiom-payment-countdown__timer">
                <span class="axiom-payment-countdown__time">30:00</span>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var countdown = document.getElementById('<?php echo esc_js($countdown_id); ?>');
        if (!countdown) return;

        var display = countdown.querySelector('.axiom-payment-countdown__time');
        var expiresAt = parseInt(countdown.getAttribute('data-expires'), 10) * 1000;

        function updateCountdown() {
            var now = Date.now();
            var diff = Math.max(0, expiresAt - now);

            var totalSeconds = Math.floor(diff / 1000);
            var minutes = Math.floor(totalSeconds / 60);
            var seconds = totalSeconds % 60;

            display.textContent =
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            if (diff <= 0) {
                countdown.classList.add('is-expired');
                countdown.querySelector('.axiom-payment-countdown__title').textContent = 'Payment window expired';
                countdown.querySelector('.axiom-payment-countdown__copy').textContent = 'This order is no longer being held. Please place a new order or contact us if you already sent payment.';
                display.textContent = '00:00';
                clearInterval(timer);
            }
        }

        updateCountdown();
        var timer = setInterval(updateCountdown, 1000);
    })();
    </script>
    <?php
}

/**
 * Countdown styles.
 */
add_action('wp_footer', 'axiom_payment_countdown_styles', 99);
function axiom_payment_countdown_styles() {
    if (!function_exists('is_order_received_page') || !is_order_received_page()) {
        return;
    }
    ?>
    <style>
        .axiom-payment-countdown{
            display:flex;
            align-items:flex-start;
            gap:14px;
            margin:18px 0 18px;
            padding:18px;
            border:1px solid #d9e4f0;
            border-radius:22px;
            background:linear-gradient(135deg, #eef7ff, #f8fbff);
            box-sizing:border-box;
        }

        .axiom-payment-countdown__icon{
            width:42px;
            height:42px;
            min-width:42px;
            border-radius:999px;
            background:#d9efff;
            color:#2f84bf;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            font-size:18px;
            line-height:1;
        }

        .axiom-payment-countdown__content{
            min-width:0;
        }

        .axiom-payment-countdown__title{
            margin:0 0 4px;
            color:#0f172a;
            font-size:20px;
            font-weight:900;
            line-height:1.2;
        }

        .axiom-payment-countdown__copy{
            margin:0 0 12px;
            color:#64748b;
            font-size:15px;
            line-height:1.6;
        }

        .axiom-payment-countdown__timer{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-width:116px;
            min-height:48px;
            padding:0 18px;
            border-radius:999px;
            background:#0f172a;
            color:#ffffff;
            box-sizing:border-box;
        }

        .axiom-payment-countdown__time{
            font-size:24px;
            font-weight:900;
            line-height:1;
            letter-spacing:0.04em;
        }

        .axiom-payment-countdown.is-expired{
            background:#fff7ed;
            border-color:#fed7aa;
        }

        .axiom-payment-countdown.is-expired .axiom-payment-countdown__icon{
            background:#ffedd5;
            color:#c2410c;
        }

        .axiom-payment-countdown.is-expired .axiom-payment-countdown__timer{
            background:#c2410c;
        }

        @media (max-width: 767px){
            .axiom-payment-countdown{
                gap:12px;
                margin:16px 0;
                padding:16px;
                border-radius:20px;
            }

            .axiom-payment-countdown__icon{
                width:38px;
                height:38px;
                min-width:38px;
                font-size:16px;
            }

            .axiom-payment-countdown__title{
                font-size:18px;
            }

            .axiom-payment-countdown__copy{
                font-size:14px;
                margin-bottom:10px;
            }

            .axiom-payment-countdown__timer{
                min-width:104px;
                min-height:44px;
                padding:0 16px;
            }

            .axiom-payment-countdown__time{
                font-size:22px;
            }
        }
    </style>
    <?php
}
