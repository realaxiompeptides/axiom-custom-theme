<?php
defined( 'ABSPATH' ) || exit;

if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
	return;
}

$order_id = $order->get_id();
?>

<div class="axiom-thankyou-page">
	<div class="axiom-thankyou-shell">

		<?php
		wc_get_template(
			'checkout/order-received.php',
			array(
				'order' => $order,
			)
		);

		if ( function_exists( 'axiom_render_custom_thankyou_header' ) ) {
			axiom_render_custom_thankyou_header( $order_id );
		}

		$order_details_template = get_stylesheet_directory() . '/woocommerce/order/order-details.php';
		if ( file_exists( $order_details_template ) ) {
			include $order_details_template;
		}

		$customer_details_template = get_stylesheet_directory() . '/woocommerce/order/order-details-customer.php';
		if ( file_exists( $customer_details_template ) ) {
			include $customer_details_template;
		}
		?>

	</div>
</div>
