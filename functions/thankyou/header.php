<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_render_custom_thankyou_header($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

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

    $delivery      = axiom_get_thankyou_delivery_estimate($order, $shipping_label);
    $has_backorder = axiom_order_has_backorder_items($order);
    $shipped_date  = axiom_get_order_shipped_date($order);
    $is_shipped    = !empty($shipped_date);

    $payment_method_id_lower = strtolower($payment_method_id);
    $payment_method_lower    = strtolower($payment_method);

    $is_zelle = (false !== strpos($payment_method_id_lower, 'zelle') || false !== strpos($payment_method_lower, 'zelle'));
    $is_venmo = (false !== strpos($payment_method_id_lower, 'venmo') || false !== strpos($payment_method_lower, 'venmo'));

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
        echo '          <div class="axiom-payment-copy-field"><span>Zelle phone</span><div class="axiom-payment-copy-row"><strong>916-233-5312</strong><button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'916-233-5312\')">Copy</button></div></div>';
        echo '          <div class="axiom-payment-copy-field"><span>Zelle email</span><div class="axiom-payment-copy-row"><strong>jaxferone@gmail.com</strong><button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'jaxferone@gmail.com\')">Copy</button></div></div>';
        echo '          <div class="axiom-payment-copy-field"><span>Payment note</span><div class="axiom-payment-copy-row"><strong>#' . esc_html($order_number) . '</strong><button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'#' . esc_js($order_number) . '\')">Copy</button></div></div>';
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
        echo '          <div class="axiom-payment-copy-field"><span>Venmo username</span><div class="axiom-payment-copy-row"><strong>@thomas-harris-axiom</strong><button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'@thomas-harris-axiom\')">Copy</button></div></div>';
        echo '          <div class="axiom-payment-copy-field"><span>Venmo link</span><div class="axiom-payment-copy-row"><strong><a href="https://venmo.com/thomas-harris-axiom" target="_blank" rel="noopener noreferrer">venmo.com/thomas-harris-axiom</a></strong><button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'https://venmo.com/thomas-harris-axiom\')">Copy</button></div></div>';
        echo '          <div class="axiom-payment-copy-field"><span>Payment note</span><div class="axiom-payment-copy-row"><strong>#' . esc_html($order_number) . '</strong><button type="button" class="axiom-copy-button" onclick="axiomCopyValue(this, \'#' . esc_js($order_number) . '\')">Copy</button></div></div>';
        echo '      </div>';
        echo '  </div>';
    }

    echo '  <div class="axiom-delivery-command-card" data-axiom-delivery-card>';
    echo '      <div class="axiom-delivery-card-glow"></div>';

    echo '      <div class="axiom-delivery-card-top">';
    echo '          <div class="axiom-usps-badge"><i class="fa-brands fa-usps"></i><span>' . esc_html($delivery['service']) . '</span></div>';
    echo '          <div class="axiom-delivery-confidence"><i class="fa-solid fa-circle-check"></i> Smart estimate</div>';
    echo '      </div>';

    echo '      <div class="axiom-delivery-main">';
    echo '          <span class="axiom-delivery-eyebrow">Estimated arrival</span>';
    echo '          <h3><span class="axiom-local-date" data-ts="' . esc_attr($delivery['delivery_start_ts']) . '">' . esc_html($delivery['delivery_start_fallback']) . '</span><span class="axiom-date-dash">–</span><span class="axiom-local-date" data-ts="' . esc_attr($delivery['delivery_end_ts']) . '">' . esc_html($delivery['delivery_end_fallback']) . '</span></h3>';
    echo '          <p>' . esc_html($delivery['delivery_note']) . '</p>';
    echo '      </div>';

    echo '      <div class="axiom-delivery-mini-grid">';
    echo '          <div class="axiom-delivery-mini"><i class="fa-regular fa-calendar-check"></i><span>Order placed</span><strong class="axiom-local-datetime" data-ts="' . esc_attr($delivery['order_created_ts']) . '">' . esc_html($delivery['order_created_fallback']) . '</strong></div>';
    echo '          <div class="axiom-delivery-mini"><i class="fa-solid fa-box"></i><span>Ships by</span><strong class="axiom-local-date" data-ts="' . esc_attr($delivery['ship_ts']) . '">' . esc_html($delivery['ship_fallback']) . '</strong></div>';
    echo '          <div class="axiom-delivery-mini"><i class="fa-solid fa-route"></i><span>Transit time</span><strong>' . esc_html($delivery['transit_label']) . '</strong></div>';
    echo '      </div>';

    echo '      <div class="axiom-delivery-live-status">';

    if ($is_shipped) {
        echo '          <div class="axiom-live-status-row is-good">';
        echo '              <span class="axiom-status-dot axiom-status-dot--green"></span>';
        echo '              <div><strong>Shipped</strong><p>Your order shipped on ' . esc_html($shipped_date) . '.</p></div>';
        echo '          </div>';
    } else {
        echo '          <div class="axiom-live-status-row is-warning">';
        echo '              <span class="axiom-status-dot axiom-status-dot--red"></span>';
        echo '              <div><strong>Not shipped yet</strong><p>Your order is still being prepared for shipment.</p></div>';
        echo '          </div>';
    }

    if ($has_backorder) {
        echo '          <div class="axiom-live-status-row is-warning">';
        echo '              <span class="axiom-status-dot axiom-status-dot--red"></span>';
        echo '              <div><strong>Backorder notice</strong><p>One or more items in this order are on backorder. Shipping may update once ready.</p></div>';
        echo '          </div>';
    } else {
        echo '          <div class="axiom-live-status-row is-good">';
        echo '              <span class="axiom-status-dot axiom-status-dot--green"></span>';
        echo '              <div><strong>No backorder</strong><p>Your order does not currently show backordered items.</p></div>';
        echo '          </div>';
    }

    echo '      </div>';

    echo '      <div class="axiom-delivery-rules">';
    echo '          <div><i class="fa-solid fa-clock"></i> ' . esc_html($delivery['cutoff_note']) . '</div>';
    echo '          <div><i class="fa-solid fa-calendar-xmark"></i> Weekends and USPS holidays are skipped.</div>';
    echo '          <div><i class="fa-solid fa-location-dot"></i> Based on your shipping method and destination.</div>';
    echo '      </div>';

    echo '  </div>';
    echo '</section>';

    echo '<script>
    function axiomCopyValue(button, value) {
        if (!navigator.clipboard) return;

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

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".axiom-local-date").forEach(function(el) {
            var ts = parseInt(el.getAttribute("data-ts"), 10);
            if (!ts) return;

            el.textContent = new Intl.DateTimeFormat(undefined, {
                month: "short",
                day: "numeric"
            }).format(new Date(ts));
        });

        document.querySelectorAll(".axiom-local-datetime").forEach(function(el) {
            var ts = parseInt(el.getAttribute("data-ts"), 10);
            if (!ts) return;

            el.textContent = new Intl.DateTimeFormat(undefined, {
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "2-digit"
            }).format(new Date(ts));
        });
    });
    </script>';
}

add_action('woocommerce_thankyou', 'axiom_render_thankyou_bottom_customer_help', 80);

function axiom_render_thankyou_bottom_customer_help($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $order_number = $order->get_order_number();

    echo '<section class="axiom-thankyou-bottom-help">';

    if (!is_user_logged_in()) {
        $account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');

        echo '<div class="axiom-thankyou-account-card">';
        echo '  <div class="axiom-thankyou-account-icon"><i class="fa-solid fa-user-plus"></i></div>';
        echo '  <div>';
        echo '      <h3>Create your customer account</h3>';
        echo '      <p>Track this order faster, save your shipping address, and view future order history.</p>';
        echo '      <a href="' . esc_url($account_url) . '" class="axiom-thankyou-account-button">Create account or log in</a>';
        echo '  </div>';
        echo '</div>';
    }

    echo '<div class="axiom-thankyou-support-card">';
    echo '  <div class="axiom-thankyou-support-top">';
    echo '      <i class="fa-solid fa-headset"></i>';
    echo '      <div>';
    echo '          <h3>Need help with your order?</h3>';
    echo '          <p>Contact support and include your order number: <strong>#' . esc_html($order_number) . '</strong></p>';
    echo '      </div>';
    echo '  </div>';
    echo '  <div class="axiom-thankyou-support-actions">';
    echo '      <a href="mailto:realaxiompeptides@gmail.com?subject=Order%20%23' . rawurlencode($order_number) . '%20Support" class="axiom-thankyou-support-btn">Email support</a>';
    echo '      <button type="button" class="axiom-thankyou-support-btn" onclick="axiomCopyValue(this, \'#' . esc_js($order_number) . '\')">Copy order #</button>';
    echo '  </div>';
    echo '</div>';

    echo '</section>';
}

function axiom_get_thankyou_delivery_estimate($order, $shipping_label) {
    $la_timezone = new DateTimeZone('America/Los_Angeles');

    $created = $order->get_date_created()
        ? new DateTime('@' . $order->get_date_created()->getTimestamp())
        : new DateTime('now', $la_timezone);

    $created->setTimezone($la_timezone);

    $country = strtoupper($order->get_shipping_country() ?: $order->get_billing_country());
    $is_international = ($country && $country !== 'US');

    $shipping_lower = strtolower((string) $shipping_label);
    $shipping_total = (float) $order->get_shipping_total();

    if ($is_international) {
        $service = 'USPS International';
        $min_days = 10;
        $max_days = 21;

        if ($country === 'CA') {
            $min_days = 7;
            $max_days = 14;
        } elseif (in_array($country, array('AU', 'NZ'), true)) {
            $min_days = 12;
            $max_days = 24;
        }

        $delivery_note = 'International delivery can take longer if customs or the local carrier delays the package.';
    } elseif (strpos($shipping_lower, 'priority') !== false) {
        $service = 'USPS Priority Mail';
        $min_days = 2;
        $max_days = 3;
        $delivery_note = 'Priority Mail is calculated using business days after your package ships.';
    } else {
        $service = $shipping_total <= 0 ? 'Free Shipping - USPS Ground Advantage' : 'USPS Ground Advantage';
        $min_days = 3;
        $max_days = 6;
        $delivery_note = 'Ground Advantage is calculated using business days after your package ships.';
    }

    $ship = clone $created;
    $cutoff = clone $created;
    $cutoff->setTime(14, 0, 0);

    if (!axiom_is_usps_shipping_day($ship)) {
        $ship = axiom_next_usps_shipping_day($ship);
        $cutoff_note = 'Order was placed on a weekend or USPS holiday.';
    } elseif ($created > $cutoff) {
        $ship->modify('+1 day');
        $ship = axiom_next_usps_shipping_day($ship);
        $cutoff_note = 'Order was placed after the shipping cutoff.';
    } else {
        $cutoff_note = 'Order was placed before the shipping cutoff.';
    }

    $delivery_start = axiom_add_usps_business_days($ship, $min_days);
    $delivery_end   = axiom_add_usps_business_days($ship, $max_days);

    return array(
        'service' => $service,
        'order_created_ts' => $created->getTimestamp() * 1000,
        'ship_ts' => $ship->getTimestamp() * 1000,
        'delivery_start_ts' => $delivery_start->getTimestamp() * 1000,
        'delivery_end_ts' => $delivery_end->getTimestamp() * 1000,
        'order_created_fallback' => $created->format('M j, g:i A'),
        'ship_fallback' => $ship->format('M j'),
        'delivery_start_fallback' => $delivery_start->format('M j'),
        'delivery_end_fallback' => $delivery_end->format('M j'),
        'transit_label' => $min_days . '-' . $max_days . ' business days',
        'delivery_note' => $delivery_note,
        'cutoff_note' => $cutoff_note,
    );
}

function axiom_order_has_backorder_items($order) {
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        if (!$product) continue;

        if ($product->is_on_backorder((int) $item->get_quantity())) {
            return true;
        }
    }

    return false;
}

function axiom_get_order_shipped_date($order) {
    $completed_date = $order->get_date_completed();

    if ($completed_date) {
        return $completed_date->date_i18n('M j');
    }

    return '';
}

function axiom_add_usps_business_days(DateTime $date, $days) {
    $result = clone $date;
    $added = 0;

    while ($added < $days) {
        $result->modify('+1 day');

        if (axiom_is_usps_shipping_day($result)) {
            $added++;
        }
    }

    return $result;
}

function axiom_next_usps_shipping_day(DateTime $date) {
    $next = clone $date;

    while (!axiom_is_usps_shipping_day($next)) {
        $next->modify('+1 day');
    }

    return $next;
}

function axiom_is_usps_shipping_day(DateTime $date) {
    $weekday = (int) $date->format('N');

    if ($weekday >= 6) return false;

    return !in_array($date->format('Y-m-d'), axiom_usps_holiday_dates((int) $date->format('Y')), true);
}

function axiom_usps_holiday_dates($year) {
    return array(
        axiom_observed_usps_date("$year-01-01"),
        axiom_nth_weekday_usps($year, 1, 1, 3),
        axiom_nth_weekday_usps($year, 2, 1, 3),
        axiom_last_weekday_usps($year, 5, 1),
        axiom_observed_usps_date("$year-06-19"),
        axiom_observed_usps_date("$year-07-04"),
        axiom_nth_weekday_usps($year, 9, 1, 1),
        axiom_nth_weekday_usps($year, 10, 1, 2),
        axiom_observed_usps_date("$year-11-11"),
        axiom_nth_weekday_usps($year, 11, 4, 4),
        axiom_observed_usps_date("$year-12-25"),
    );
}

function axiom_observed_usps_date($date_string) {
    $date = new DateTime($date_string);
    $weekday = (int) $date->format('N');

    if ($weekday === 6) {
        $date->modify('-1 day');
    } elseif ($weekday === 7) {
        $date->modify('+1 day');
    }

    return $date->format('Y-m-d');
}

function axiom_nth_weekday_usps($year, $month, $weekday, $nth) {
    $date = new DateTime("$year-$month-01");

    while ((int) $date->format('N') !== $weekday) {
        $date->modify('+1 day');
    }

    $date->modify('+' . ($nth - 1) . ' weeks');

    return $date->format('Y-m-d');
}

function axiom_last_weekday_usps($year, $month, $weekday) {
    $date = new DateTime("last day of $year-$month");

    while ((int) $date->format('N') !== $weekday) {
        $date->modify('-1 day');
    }

    return $date->format('Y-m-d');
}
