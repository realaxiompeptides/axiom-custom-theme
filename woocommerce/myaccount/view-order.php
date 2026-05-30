<?php
defined('ABSPATH') || exit;

$order = wc_get_order($order_id);

if (!$order) {
    return;
}

$order_number = $order->get_order_number();
$order_status = wc_get_order_status_name($order->get_status());
$order_date   = $order->get_date_created() ? $order->get_date_created()->date_i18n('F j, Y') : '';
$order_total  = $order->get_formatted_order_total();
$payment      = $order->get_payment_method_title();
?>

<section class="axiom-view-order-page">

    <div class="axiom-view-order-hero">
        <span>Order Details</span>
        <h1>Order #<?php echo esc_html($order_number); ?></h1>
        <p>Placed on <?php echo esc_html($order_date); ?> and currently marked as <strong><?php echo esc_html($order_status); ?></strong>.</p>
    </div>

    <div class="axiom-view-order-status-card">
        <div>
            <span>Status</span>
            <strong><?php echo esc_html($order_status); ?></strong>
        </div>
        <div>
            <span>Total</span>
            <strong><?php echo wp_kses_post($order_total); ?></strong>
        </div>
        <div>
            <span>Payment</span>
            <strong><?php echo esc_html($payment); ?></strong>
        </div>
    </div>

    <div class="axiom-view-order-section">
        <h2>Items in this order</h2>

        <div class="axiom-view-order-items">
            <?php foreach ($order->get_items() as $item_id => $item) : 
                $product = $item->get_product();
                $image   = $product ? $product->get_image('woocommerce_thumbnail') : '';
                ?>
                <div class="axiom-view-order-item">
                    <div class="axiom-view-order-item-image">
                        <?php echo $image ? wp_kses_post($image) : '<i class="fa-solid fa-box"></i>'; ?>
                    </div>

                    <div class="axiom-view-order-item-info">
                        <strong><?php echo esc_html($item->get_name()); ?></strong>
                        <span>Qty: <?php echo esc_html($item->get_quantity()); ?></span>
                    </div>

                    <div class="axiom-view-order-item-total">
                        <?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="axiom-view-order-section">
        <h2>Order totals</h2>

        <div class="axiom-view-order-totals">
            <?php foreach ($order->get_order_item_totals() as $key => $total) : ?>
                <div class="axiom-view-order-total-row <?php echo esc_attr($key); ?>">
                    <span><?php echo wp_kses_post($total['label']); ?></span>
                    <strong><?php echo wp_kses_post($total['value']); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="axiom-view-order-address-grid">
        <?php if ($order->get_formatted_billing_address()) : ?>
            <div class="axiom-view-order-address-card">
                <h2>Billing address</h2>
                <address><?php echo wp_kses_post($order->get_formatted_billing_address()); ?></address>
            </div>
        <?php endif; ?>

        <?php if ($order->get_formatted_shipping_address()) : ?>
            <div class="axiom-view-order-address-card">
                <h2>Shipping address</h2>
                <address><?php echo wp_kses_post($order->get_formatted_shipping_address()); ?></address>
            </div>
        <?php endif; ?>
    </div>

    <div class="axiom-view-order-support">
        <i class="fa-solid fa-headset"></i>
        <div>
            <h2>Need help with this order?</h2>
            <p>Contact support and include order number <strong>#<?php echo esc_html($order_number); ?></strong>.</p>
            <a href="mailto:realaxiompeptides@gmail.com?subject=Order%20%23<?php echo rawurlencode($order_number); ?>%20Support">Email support</a>
        </div>
    </div>

</section>
