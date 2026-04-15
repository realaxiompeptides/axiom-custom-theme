<?php
defined('ABSPATH') || exit;

$order = wc_get_order($order_id);

if (!$order) {
    return;
}

$shipping_address = $order->get_formatted_shipping_address();
if (!$shipping_address) {
    $shipping_address = $order->get_formatted_billing_address();
}

if (!$shipping_address) {
    return;
}
?>

<section class="axiom-thankyou-section axiom-thankyou-address-section">
    <div class="axiom-thankyou-address-grid axiom-thankyou-address-grid--single">
        <div class="axiom-thankyou-address-card">
            <div class="axiom-thankyou-section-header">
                <h2><?php esc_html_e('Shipping to', 'woocommerce'); ?></h2>
            </div>

            <div class="axiom-thankyou-address-content">
                <?php echo wp_kses_post($shipping_address); ?>
            </div>
        </div>
    </div>
</section>
