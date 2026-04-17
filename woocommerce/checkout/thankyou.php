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
		/*
		 * This keeps the normal WooCommerce thank-you message:
		 * "Thank you for your order"
		 */
		wc_get_template(
			'checkout/order-received.php',
			array(
				'order' => $order,
			)
		);

		/*
		 * This loads your real custom order summary
		 */
		$order_details_template = get_stylesheet_directory() . '/woocommerce/order/order-details.php';

		if ( file_exists( $order_details_template ) ) {
			include $order_details_template;
		}

		/*
		 * This loads your shipping/customer details card
		 */
		$customer_details_template = get_stylesheet_directory() . '/woocommerce/order/order-details-customer.php';

		if ( file_exists( $customer_details_template ) ) {
			include $customer_details_template;
		}

		/*
		 * This loads your custom payment instructions once
		 */
		if ( function_exists( 'axiom_render_custom_payment_instructions_thankyou' ) ) {
			axiom_render_custom_payment_instructions_thankyou( $order_id );
		}

		/*
		 * Keep gateway-specific thank you hooks working
		 */
		do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order_id );
		?>

	</div>
</div>
