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
$back_url     = wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'));

$status_class = 'is-default';

if (in_array($order->get_status(), array('processing', 'completed'), true)) {
    $status_class = 'is-good';
} elseif (in_array($order->get_status(), array('cancelled', 'failed', 'refunded'), true)) {
    $status_class = 'is-bad';
} elseif (in_array($order->get_status(), array('pending', 'on-hold'), true)) {
    $status_class = 'is-waiting';
}
?>

<section class="axiom-view-order-page">

    <a href="<?php echo esc_url($back_url); ?>" class="axiom-view-order-back">
        <i class="fa-solid fa-arrow-left"></i>
        Back to orders
    </a>

    <div class="axiom-view-order-hero">
        <span>Order Details</span>
        <h1>Order #<?php echo esc_html($order_number); ?></h1>
        <p>
            Placed on <?php echo esc_html($order_date); ?> and currently marked as
            <strong><?php echo esc_html($order_status); ?></strong>.
        </p>
    </div>

    <div class="axiom-shipping-notice-card axiom-view-order-shipping-notice">
        <div class="axiom-shipping-notice-top">
            <div class="axiom-shipping-notice-icon">
                <i class="fa-solid fa-truck-fast"></i>
            </div>

            <div class="axiom-shipping-notice-content">
                <span>Shipping Notice</span>
                <h3>Carrier delivery times are estimates only</h3>
                <p>
                    USPS, UPS, and FedEx delivery windows are estimates only and are not guaranteed unless the selected service specifically includes a carrier-backed guarantee.
                    Once your order is accepted by the carrier, delivery delays, missed scans, routing issues, weather delays, and lost packages are outside of our direct control.
                </p>
            </div>
        </div>

        <div class="axiom-shipping-carriers">
            <div>
                <i class="fa-brands fa-usps"></i>
                <strong>USPS</strong>
            </div>

            <div>
                <i class="fa-brands fa-ups"></i>
                <strong>UPS</strong>
            </div>

            <div>
                <i class="fa-brands fa-fedex"></i>
                <strong>FedEx</strong>
            </div>
        </div>

        <p class="axiom-shipping-help">
            If your package is delayed or missing, please contact support and we will help open a carrier investigation with the shipping carrier.
        </p>
    </div>

    <div class="axiom-view-order-status-card">
        <div>
            <span>Status</span>
            <strong class="axiom-view-order-status-pill <?php echo esc_attr($status_class); ?>">
                <?php echo esc_html($order_status); ?>
            </strong>
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

    <?php if ($order->has_status(array('pending', 'on-hold'))) : ?>
        <div class="axiom-view-order-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Payment may still be pending</strong>
                <p>If you already paid, please wait for confirmation. If you need help, contact support with your order number.</p>
            </div>
        </div>
    <?php endif; ?>

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

    <?php
    $customer_note = $order->get_customer_note();

    if ($customer_note) :
        ?>
        <div class="axiom-view-order-section">
            <h2>Order note</h2>
            <p class="axiom-view-order-note"><?php echo wp_kses_post(wpautop($customer_note)); ?></p>
        </div>
    <?php endif; ?>

    <div class="axiom-view-order-support">
        <i class="fa-solid fa-headset"></i>
        <div>
            <h2>Need help with this order?</h2>
            <p>Contact support and include order number <strong>#<?php echo esc_html($order_number); ?></strong>.</p>
            <a href="mailto:support@axiomresearch.shop?subject=Order%20%23<?php echo rawurlencode($order_number); ?>%20Support">
                Email support
            </a>
        </div>
    </div>

</section>
