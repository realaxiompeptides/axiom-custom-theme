<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Your BTC address.
 */
function axiom_crypto_btc_address() {
    return 'bc1qa2c4nfzakewrxf9jcj3m8ql3n436jhzn0spgfr';
}

/**
 * Replace with your real Cash App tag.
 */
function axiom_cashapp_display_value() {
    return '$REPLACE_WITH_YOUR_CASHAPP';
}

/**
 * Gateway ID hints.
 * Add real IDs here if you know them.
 */
function axiom_discount_gateway_id_hints() {
    return array(
        'cashapp' => array(
            'cod',
            'cashapp',
            'cash_app',
            'cash-app',
        ),
        'crypto' => array(
            'bacs',
            'paymento',
            'paymento_gateway',
            'crypto',
            'bitcoin',
            'btc',
        ),
    );
}

/**
 * Title keyword hints.
 */
function axiom_discount_gateway_title_hints() {
    return array(
        'cashapp' => array(
            'cash app',
            'cashapp',
        ),
        'crypto' => array(
            'paymento',
            'crypto',
            'bitcoin',
            'btc',
            'usdt',
            'usdc',
            'ethereum',
            'eth',
        ),
    );
}

/**
 * Get current chosen payment method.
 */
function axiom_get_current_chosen_payment_method() {
    if (WC()->session) {
        $session_method = WC()->session->get('chosen_payment_method');
        if (!empty($session_method)) {
            return $session_method;
        }
    }

    if (!empty($_POST['payment_method'])) {
        return wc_clean(wp_unslash($_POST['payment_method']));
    }

    return '';
}

/**
 * Get gateway object by ID.
 */
function axiom_get_gateway_object_by_id($gateway_id) {
    if (!$gateway_id || !function_exists('WC')) {
        return null;
    }

    $payment_gateways = WC()->payment_gateways();
    if (!$payment_gateways || !method_exists($payment_gateways, 'payment_gateways')) {
        return null;
    }

    $gateways = $payment_gateways->payment_gateways();

    return isset($gateways[$gateway_id]) ? $gateways[$gateway_id] : null;
}

/**
 * Determine if current method is Cash App or Crypto.
 */
function axiom_get_discount_payment_type($gateway_id = '') {
    if (!$gateway_id) {
        $gateway_id = axiom_get_current_chosen_payment_method();
    }

    if (!$gateway_id) {
        return '';
    }

    $gateway_id_normalized = strtolower(trim((string) $gateway_id));
    $id_hints              = axiom_discount_gateway_id_hints();

    foreach ($id_hints as $type => $ids) {
        foreach ($ids as $id_hint) {
            if ($gateway_id_normalized === strtolower($id_hint)) {
                return $type;
            }
        }
    }

    $gateway = axiom_get_gateway_object_by_id($gateway_id);
    $title   = $gateway && !empty($gateway->title) ? strtolower(wp_strip_all_tags($gateway->title)) : '';
    $desc    = $gateway && !empty($gateway->description) ? strtolower(wp_strip_all_tags($gateway->description)) : '';
    $haystack = trim($gateway_id_normalized . ' ' . $title . ' ' . $desc);

    $title_hints = axiom_discount_gateway_title_hints();

    foreach ($title_hints as $type => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($haystack, strtolower($keyword)) !== false) {
                return $type;
            }
        }
    }

    return '';
}

/**
 * Add 5% off text to payment titles.
 */
add_filter('woocommerce_gateway_title', 'axiom_gateway_title_with_discount', 20, 2);
function axiom_gateway_title_with_discount($title, $gateway_id) {
    $type = axiom_get_discount_payment_type($gateway_id);

    if ($type === 'cashapp' || $type === 'crypto') {
        if (strpos($title, '5% OFF') === false) {
            $title .= ' <span class="axiom-payment-discount-label">(5% OFF)</span>';
        }
    }

    return $title;
}

/**
 * Add helpful descriptions on checkout.
 */
add_filter('woocommerce_gateway_description', 'axiom_gateway_description_with_discount', 20, 2);
function axiom_gateway_description_with_discount($description, $gateway_id) {
    $type = axiom_get_discount_payment_type($gateway_id);

    if ($type === 'cashapp') {
        $description .= '<p class="axiom-payment-discount-copy">Pay with Cash App and receive an automatic 5% discount on your order total.</p>';
    }

    if ($type === 'crypto') {
        $description .= '<p class="axiom-payment-discount-copy">Pay with Bitcoin / Crypto and receive an automatic 5% discount on your order total.</p>';
    }

    return $description;
}

/**
 * Apply 5% discount.
 */
add_action('woocommerce_cart_calculate_fees', 'axiom_apply_payment_method_discount', 20, 1);
function axiom_apply_payment_method_discount($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (!is_object($cart) || !WC()->session) {
        return;
    }

    $gateway_id = axiom_get_current_chosen_payment_method();
    $type       = axiom_get_discount_payment_type($gateway_id);

    if (!$type) {
        return;
    }

    $subtotal = (float) $cart->get_subtotal();

    if ($subtotal <= 0) {
        return;
    }

    $discount_amount = round($subtotal * 0.05, wc_get_price_decimals());

    if ($discount_amount > 0) {
        $cart->add_fee(__('Payment Discount (5%)', 'axiom-custom-theme'), -$discount_amount, false);
    }
}

/**
 * Refresh checkout when payment method changes.
 */
add_action('wp_enqueue_scripts', 'axiom_enqueue_checkout_discount_script');
function axiom_enqueue_checkout_discount_script() {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }

    wp_register_script(
        'axiom-checkout-payment-discount',
        false,
        array('jquery'),
        '1.0.1',
        true
    );

    wp_enqueue_script('axiom-checkout-payment-discount');

    wp_add_inline_script(
        'axiom-checkout-payment-discount',
        "jQuery(function($){
            $('form.checkout').on('change', 'input[name=\"payment_method\"]', function(){
                $('body').trigger('update_checkout');
            });
        });"
    );
}

/**
 * Styling.
 */
add_action('wp_head', 'axiom_checkout_discount_styles');
function axiom_checkout_discount_styles() {
    if (!function_exists('is_checkout') || !is_checkout()) {
        return;
    }
    ?>
    <style>
      .axiom-payment-discount-label{
        color:#2f8df3;
        font-weight:900;
        margin-left:6px;
        white-space:nowrap;
      }
      .axiom-payment-discount-copy{
        margin:8px 0 0;
        color:#64748b;
        font-size:14px;
        line-height:1.5;
      }
    </style>
    <?php
}

/**
 * Save custom payment instructions to order meta.
 */
add_action('woocommerce_checkout_create_order', 'axiom_store_payment_instruction_meta', 20, 2);
function axiom_store_payment_instruction_meta($order, $data) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $gateway_id = $order->get_payment_method();
    $type       = axiom_get_discount_payment_type($gateway_id);

    if ($type === 'cashapp') {
        $order->update_meta_data('_axiom_payment_method_display', 'Cash App (5% OFF)');
        $order->update_meta_data('_axiom_payment_instruction_title', 'Cash App Payment Instructions');
        $order->update_meta_data('_axiom_payment_instruction_body', 'Send your payment via Cash App to ' . axiom_cashapp_display_value() . '. Your 5% discount has already been applied to the order total.');
    }

    if ($type === 'crypto') {
        $gateway = axiom_get_gateway_object_by_id($gateway_id);
        $gateway_title = $gateway && !empty($gateway->title) ? wp_strip_all_tags($gateway->title) : 'Bitcoin / Crypto';

        $order->update_meta_data('_axiom_payment_method_display', $gateway_title . ' (5% OFF)');
        $order->update_meta_data('_axiom_payment_instruction_title', 'Bitcoin / Crypto Payment Instructions');
        $order->update_meta_data('_axiom_payment_instruction_body', 'Send your Bitcoin payment to: ' . axiom_crypto_btc_address() . '. Your 5% discount has already been applied to the order total.');
    }
}

/**
 * Thank you page instructions.
 */
add_action('woocommerce_thankyou', 'axiom_render_custom_payment_instructions_thankyou', 20);
function axiom_render_custom_payment_instructions_thankyou($order_id) {
    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $title = $order->get_meta('_axiom_payment_instruction_title');
    $body  = $order->get_meta('_axiom_payment_instruction_body');

    if (!$title || !$body) {
        return;
    }

    echo '<section class="axiom-thankyou-payment-box" style="margin:24px 0;padding:20px;border:1px solid #dbe6f2;border-radius:20px;background:#f8fbff;">';
    echo '<h2 style="margin:0 0 10px;font-size:22px;font-weight:900;color:#0f172a;">' . esc_html($title) . '</h2>';
    echo '<p style="margin:0;color:#475569;line-height:1.6;">' . esc_html($body) . '</p>';
    echo '</section>';
}

/**
 * Order details page instructions.
 */
add_action('woocommerce_order_details_after_order_table', 'axiom_render_custom_payment_instructions_order_details', 20);
function axiom_render_custom_payment_instructions_order_details($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $title = $order->get_meta('_axiom_payment_instruction_title');
    $body  = $order->get_meta('_axiom_payment_instruction_body');

    if (!$title || !$body) {
        return;
    }

    echo '<section class="axiom-order-payment-box" style="margin:24px 0;padding:20px;border:1px solid #dbe6f2;border-radius:20px;background:#f8fbff;">';
    echo '<h2 style="margin:0 0 10px;font-size:22px;font-weight:900;color:#0f172a;">' . esc_html($title) . '</h2>';
    echo '<p style="margin:0;color:#475569;line-height:1.6;">' . esc_html($body) . '</p>';
    echo '</section>';
}

/**
 * Replace displayed payment method title on order screens.
 */
add_filter('woocommerce_order_get_payment_method_title', 'axiom_custom_order_payment_method_title', 20, 2);
function axiom_custom_order_payment_method_title($title, $order) {
    if (!$order instanceof WC_Order) {
        return $title;
    }

    $custom_title = $order->get_meta('_axiom_payment_method_display');

    if ($custom_title) {
        return $custom_title;
    }

    return $title;
}
