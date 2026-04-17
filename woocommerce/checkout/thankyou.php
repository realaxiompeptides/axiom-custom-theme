<?php
defined( 'ABSPATH' ) || exit;

if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
	return;
}

$order_id = $order->get_id();

/*
 * Show custom verification screen first for guest / wrong user.
 */
$requires_verification = false;

if ( ! current_user_can( 'edit_shop_orders' ) ) {
	if ( ! is_user_logged_in() ) {
		$requires_verification = true;
	} else {
		$current_user  = wp_get_current_user();
		$billing_email = strtolower( (string) $order->get_billing_email() );
		$current_email = strtolower( (string) $current_user->user_email );

		if ( ! $current_email || $current_email !== $billing_email ) {
			$requires_verification = true;
		}
	}
}

if ( $requires_verification ) {
	$verification_file = get_template_directory() . '/functions/thankyou/verification.php';

	if ( file_exists( $verification_file ) ) {
		?>
		<div class="axiom-thankyou-page">
			<div class="axiom-thankyou-shell">
				<?php include $verification_file; ?>
			</div>
		</div>
		<?php
		return;
	}
}
?>

<div class="axiom-thankyou-page">
	<div class="axiom-thankyou-shell">

		<?php
		/*
		 * Show the "Thank you for your order" section
		 */
		wc_get_template(
			'checkout/order-received.php',
			array(
				'order' => $order,
			)
		);

		/*
		 * Show your custom thank-you content / next steps
		 */
		if ( function_exists( 'axiom_render_custom_thankyou_header' ) ) {
			axiom_render_custom_thankyou_header( $order_id );
		}

		/*
		 * Show the real order summary
		 */
		$order_details_template = get_stylesheet_directory() . '/woocommerce/order/order-details.php';
		if ( file_exists( $order_details_template ) ) {
			include $order_details_template;
		}

		/*
		 * Show shipping/customer details
		 */
		$customer_details_template = get_stylesheet_directory() . '/woocommerce/order/order-details-customer.php';
		if ( file_exists( $customer_details_template ) ) {
			include $customer_details_template;
		}
		?>

	</div>
</div>
