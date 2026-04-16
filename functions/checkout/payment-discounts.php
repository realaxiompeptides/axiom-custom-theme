<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CHANGE THESE IDs TO MATCH YOUR ACTUAL PAYMENT METHOD IDS.
 *
 * Common examples:
 * - cod
 * - bacs
 * - cheque
 * - woo_merchant_gateway_id
 * - crypto_plugin_gateway_id
 */
function axiom_discount_payment_method_ids() {
    return array(
        'cashapp' => 'cod',   // change if your Cash App method uses a different gateway ID
        'crypto'  => 'bacs',  // change if your crypto plugin uses a different gateway ID
    );
}

/**
 * Human-readable labels.
 */
function axiom_discount_payment_method_labels() {
    return array(
        'cashapp' => 'Cash App',
        'crypto'  => 'Bitcoin / Crypto',
    );
}

/**
 * BTC address for crypto instructions.
 */
function axiom_crypto_btc_address() {
    return 'bc1qa2c4nfzakewrxf9jcj3m8ql3n436jhzn0spgfr';
}

/**
 * Cash App tag or instructions.
 * REPLACE THIS with your real Cash App handle.
 */
function axiom_cashapp_display_value() {
    return '$REPLACE_WITH_YOUR_CASHAPP';
}

/**
 * Return true if chosen method gets discount.
 */
function axiom_payment_method_gets_discount($payment_method_id) {
    $ids = axiom_discount_payment_method_ids();
    return in_array($payment_method_id, array_values($ids), true);
}

/**
 * Add " (5% OFF)" right next to checkout payment options.
 */
add_filter('woocommerce_gateway_title', 'axiom_gateway_title_with_discount', 20, 2);
function axiom_gateway_title_with_discount($title, $gateway_id) {
    if (axiom_payment_method_gets_discount($gateway_id)) {
        $title .= ' <span class="axiom-payment-discount-label">(5% OFF)</span>';
    }

    return $title;
}

/**
 * Add custom descriptions to checkout payment methods.
 */
add_filter('woocommerce_gateway_description', 'axiom_gateway_description_with_discount', 20, 2);
function axiom_gateway_description_with_discount($description, $gateway_id) {
    $ids = axiom_discount_payment_method_ids();

    if ($gateway_id === $ids['cashapp']) {
        $description .= '<p class="axiom-payment-discount-copy">Pay with Cash App and receive 5% off your order total.</p>';
    }

    if ($gateway_id === $ids['crypto']) {
        $description .= '<p class="axiom-payment-discount-copy">Pay with Bitcoin / Crypto and receive 5% off your order total.</p>';
    }

    return $description;
}

/**
 * Apply 5% discount based on chosen payment method.
 */
add_action('woocommerce_cart_calculate_fees', 'axiom_apply_payment_method_discount', 20, 1);
function axiom_apply_payment_method_discount($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (!WC()->session || !is_object($cart)) {
        return;
    }

    $chosen_payment_method = WC()->session->get('chosen_payment_method');

    if (!$chosen_payment_method || !axiom_payment_method_gets_discount($chosen_payment_method)) {
        return;
    }

    $discount_base = (float) $cart->get_subtotal();

    if ($discount_base <= 0) {
        return;
    }

    $discount_amount = round($discount_base * 0.05, wc_get_price_decimals());

    if ($discount_amount > 0) {
        $cart->add_fee(__('Payment Discount (5%)', 'axiom-custom-theme'), -$discount_amount, false);
    }
}

/**
 * Force checkout refresh when payment method changes.
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
        '1.0.0',
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
 * Style the 5% OFF text on checkout.
 */
add_action('wp_head', 'axiom_checkout_discount_styles');
function axiom_checkout_discount_styles() {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
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
 * Save extra payment instruction meta on order.
 */
add_action('woocommerce_checkout_create_order', 'axiom_store_payment_instruction_meta', 20, 2);
function axiom_store_payment_instruction_meta($order, $data) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $payment_method = $order->get_payment_method();
    $ids            = axiom_discount_payment_method_ids();
    $labels         = axiom_discount_payment_method_labels();

    if ($payment_method === $ids['cashapp']) {
        $order->update_meta_data('_axiom_payment_method_display', $labels['cashapp'] . ' (5% OFF)');
        $order->update_meta_data('_axiom_payment_instruction_title', 'Cash App Payment Instructions');
        $order->update_meta_data('_axiom_payment_instruction_body', 'Send your payment via Cash App to ' . axiom_cashapp_display_value() . '. Your 5% discount has already been applied to the order total.');
    }

    if ($payment_method === $ids['crypto']) {
        $order->update_meta_data('_axiom_payment_method_display', $labels['crypto'] . ' (5% OFF)');
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
 * Order details / My Account page instructions.
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
 * Replace payment method text shown in order meta areas.
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
