<?php
defined( 'ABSPATH' ) || exit;

echo '<div style="background:red;color:#fff;padding:16px;font-size:20px;font-weight:800;text-align:center;">CUSTOM THANKYOU TEMPLATE LOADED</div>';

if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
    echo '<div style="padding:16px;">Order object missing.</div>';
    return;
}

$order_id = $order->get_id();
?>

<div style="padding:20px;">
    <h1>Custom thank you template is working</h1>
    <p>Order ID: <?php echo esc_html( $order_id ); ?></p>
</div>

<?php wc_get_template( 'order/order-details.php', array( 'order_id' => $order_id ) ); ?>
<?php wc_get_template( 'order/order-details-customer.php', array( 'order_id' => $order_id ) ); ?>
