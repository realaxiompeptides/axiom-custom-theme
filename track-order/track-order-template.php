<?php
/*
Template Name: Axiom Track Order
*/
defined('ABSPATH') || exit;

get_header();

$order = null;
$error_message = '';
$searched = false;

$order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
$order_email = isset($_GET['order_email']) ? sanitize_email(wp_unslash($_GET['order_email'])) : '';

if ($order_id && $order_email) {
    $searched = true;

    $maybe_order = wc_get_order($order_id);

    if ($maybe_order) {
        $billing_email = $maybe_order->get_billing_email();

        if ($billing_email && strtolower($billing_email) === strtolower($order_email)) {
            $order = $maybe_order;
        } else {
            $error_message = 'We could not find an order matching that order number and email address.';
        }
    } else {
        $error_message = 'We could not find an order matching that order number and email address.';
    }
}

function axiom_track_order_status_label($status) {
    $map = array(
        'pending'    => 'Pending Payment',
        'processing' => 'Processing',
        'on-hold'    => 'On Hold',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
        'refunded'   => 'Refunded',
        'failed'     => 'Failed',
    );

    return isset($map[$status]) ? $map[$status] : ucfirst($status);
}

function axiom_track_order_step_class($current_status, $step) {
    $progress_map = array(
        'pending'    => 1,
        'failed'     => 1,
        'on-hold'    => 2,
        'processing' => 3,
        'completed'  => 4,
        'cancelled'  => 0,
        'refunded'   => 4,
    );

    $step_map = array(
        'received'   => 1,
        'confirmed'  => 2,
        'processing' => 3,
        'shipped'    => 4,
    );

    $current = isset($progress_map[$current_status]) ? $progress_map[$current_status] : 1;
    $needed  = isset($step_map[$step]) ? $step_map[$step] : 999;

    if ($current_status === 'cancelled') {
        return '';
    }

    return $current >= $needed ? 'is-complete' : '';
}

$theme_uri = get_template_directory_uri();
?>

<main class="axiom-track-page">
    <section class="axiom-track-hero">
        <div class="axiom-track-hero-inner">
            <p class="axiom-track-kicker">Order Tracking</p>
            <h1>Track Your Order</h1>
            <p class="axiom-track-subtitle">
                Enter your order number and billing email to view your live WooCommerce order status.
            </p>
        </div>
    </section>

    <section class="axiom-track-form-section">
        <div class="axiom-track-form-card">
            <form method="get" class="axiom-track-form" action="">
                <div class="axiom-track-field">
                    <label for="axiomTrackOrderId">Order Number</label>
                    <input
                        type="number"
                        id="axiomTrackOrderId"
                        name="order_id"
                        placeholder="Example: 1234"
                        value="<?php echo esc_attr($order_id ? $order_id : ''); ?>"
                        required
                    >
                </div>

                <div class="axiom-track-field">
                    <label for="axiomTrackOrderEmail">Billing Email</label>
                    <input
                        type="email"
                        id="axiomTrackOrderEmail"
                        name="order_email"
                        placeholder="you@example.com"
                        value="<?php echo esc_attr($order_email); ?>"
                        required
                    >
                </div>

                <button type="submit" class="axiom-track-submit-btn">Track Order</button>
            </form>
        </div>
    </section>

    <?php if ($searched && $error_message) : ?>
        <section class="axiom-track-result-section">
            <div class="axiom-track-message-card axiom-track-message-card--error">
                <h2>Order not found</h2>
                <p><?php echo esc_html($error_message); ?></p>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($order) : ?>
        <?php
        $status = $order->get_status();
        $status_label = axiom_track_order_status_label($status);
        $order_date = $order->get_date_created() ? $order->get_date_created()->date_i18n('F j, Y') : '';
        $tracking_number = $order->get_meta('_tracking_number');
        $tracking_url = $order->get_meta('_tracking_url');
        $shipping_method = $order->get_shipping_method();
        ?>
        <section class="axiom-track-result-section">
            <div class="axiom-track-summary-card">
                <div class="axiom-track-summary-top">
                    <div>
                        <p class="axiom-track-summary-kicker">Order Summary</p>
                        <h2>Order #<?php echo esc_html($order->get_order_number()); ?></h2>
                    </div>

                    <span class="axiom-track-status-badge status-<?php echo esc_attr($status); ?>">
                        <?php echo esc_html($status_label); ?>
                    </span>
                </div>

                <div class="axiom-track-summary-grid">
                    <div class="axiom-track-summary-item">
                        <span>Placed On</span>
                        <strong><?php echo esc_html($order_date); ?></strong>
                    </div>

                    <div class="axiom-track-summary-item">
                        <span>Total</span>
                        <strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
                    </div>

                    <div class="axiom-track-summary-item">
                        <span>Shipping Method</span>
                        <strong><?php echo esc_html($shipping_method ? $shipping_method : 'Not available yet'); ?></strong>
                    </div>

                    <div class="axiom-track-summary-item">
                        <span>Payment Status</span>
                        <strong><?php echo esc_html($status_label); ?></strong>
                    </div>
                </div>
            </div>

            <?php if ($status !== 'cancelled') : ?>
                <div class="axiom-track-progress-card">
                    <h3>Order Progress</h3>

                    <div class="axiom-track-progress-steps">
                        <div class="axiom-track-step <?php echo esc_attr(axiom_track_order_step_class($status, 'received')); ?>">
                            <span class="axiom-track-step-dot"></span>
                            <span class="axiom-track-step-label">Order Received</span>
                        </div>

                        <div class="axiom-track-step <?php echo esc_attr(axiom_track_order_step_class($status, 'confirmed')); ?>">
                            <span class="axiom-track-step-dot"></span>
                            <span class="axiom-track-step-label">Payment Confirmed</span>
                        </div>

                        <div class="axiom-track-step <?php echo esc_attr(axiom_track_order_step_class($status, 'processing')); ?>">
                            <span class="axiom-track-step-dot"></span>
                            <span class="axiom-track-step-label">Preparing Shipment</span>
                        </div>

                        <div class="axiom-track-step <?php echo esc_attr(axiom_track_order_step_class($status, 'shipped')); ?>">
                            <span class="axiom-track-step-dot"></span>
                            <span class="axiom-track-step-label">Shipped / Completed</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tracking_number || $tracking_url) : ?>
                <div class="axiom-track-shipping-card">
                    <h3>Tracking Details</h3>

                    <?php if ($tracking_number) : ?>
                        <p><strong>Tracking Number:</strong> <?php echo esc_html($tracking_number); ?></p>
                    <?php endif; ?>

                    <?php if ($tracking_url) : ?>
                        <p>
                            <a
                                href="<?php echo esc_url($tracking_url); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="axiom-track-link-btn"
                            >
                                Open Carrier Tracking
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="axiom-track-items-card">
                <h3>Items in This Order</h3>

                <div class="axiom-track-items-list">
                    <?php foreach ($order->get_items() as $item_id => $item) : ?>
                        <?php
                        $product = $item->get_product();
                        $product_name = $item->get_name();
                        $quantity = $item->get_quantity();
                        $subtotal = $order->get_formatted_line_subtotal($item);
                        $image = $product ? $product->get_image('woocommerce_thumbnail') : wc_placeholder_img('woocommerce_thumbnail');
                        ?>
                        <div class="axiom-track-item">
                            <div class="axiom-track-item-image">
                                <?php echo $image; ?>
                            </div>

                            <div class="axiom-track-item-main">
                                <h4><?php echo esc_html($product_name); ?></h4>
                                <p>Qty: <?php echo esc_html($quantity); ?></p>
                            </div>

                            <div class="axiom-track-item-price">
                                <?php echo wp_kses_post($subtotal); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
