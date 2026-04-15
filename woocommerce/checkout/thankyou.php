<?php
defined( 'ABSPATH' ) || exit;

if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
	return;
}

$theme_uri         = get_template_directory_uri();
$order_id          = $order->get_id();
$order_number      = $order->get_order_number();
$order_total       = (float) $order->get_total();
$order_subtotal    = (float) $order->get_subtotal();
$order_shipping    = (float) $order->get_shipping_total();
$order_tax         = (float) $order->get_total_tax();
$payment_method    = $order->get_payment_method_title();
$order_status      = wc_get_order_status_name( $order->get_status() );
$shipping_methods  = $order->get_shipping_methods();
$shipping_label    = '';

if ( ! empty( $shipping_methods ) ) {
	$first_shipping = reset( $shipping_methods );
	$shipping_label = $first_shipping ? $first_shipping->get_name() : '';
}

/*
 * Estimated ship and delivery dates.
 * Ships same day Mon-Fri. Weekend orders ship Monday.
 */
$created = $order->get_date_created();
$timezone = wp_timezone();
$base_date = $created ? $created->setTimezone( $timezone ) : new WC_DateTime( 'now', $timezone );

$ship_timestamp = $base_date->getTimestamp();
$ship_day_num   = (int) wp_date( 'N', $ship_timestamp, $timezone ); // 1=Mon, 7=Sun

if ( 6 === $ship_day_num ) {
	$ship_timestamp = strtotime( '+2 days', $ship_timestamp ); // Saturday -> Monday
} elseif ( 7 === $ship_day_num ) {
	$ship_timestamp = strtotime( '+1 day', $ship_timestamp ); // Sunday -> Monday
}

$estimated_ship_date = wp_date( 'l, F j', $ship_timestamp, $timezone );

/*
 * Delivery estimate:
 * Ground Advantage: +6 days
 * Priority: +3 days
 * fallback: +5 days
 */
$delivery_days = 5;

if ( $shipping_label ) {
	$shipping_label_lower = strtolower( $shipping_label );

	if ( false !== strpos( $shipping_label_lower, 'ground' ) ) {
		$delivery_days = 6;
	} elseif ( false !== strpos( $shipping_label_lower, 'priority' ) ) {
		$delivery_days = 3;
	}
}

$delivery_timestamp = $ship_timestamp;
$days_added = 0;

while ( $days_added < $delivery_days ) {
	$delivery_timestamp = strtotime( '+1 day', $delivery_timestamp );
	$day_num = (int) wp_date( 'N', $delivery_timestamp, $timezone );

	// Count calendar days, not just business days:
	$days_added++;
}

$estimated_delivery_date = wp_date( 'l, F j', $delivery_timestamp, $timezone );
?>

<div class="axiom-thankyou-page">
	<div class="axiom-thankyou-shell">

		<div class="axiom-thankyou-brand-row">
			<img
				src="<?php echo esc_url( $theme_uri . '/assets/images/axiom-menu-logo.PNG' ); ?>"
				alt="<?php esc_attr_e( 'Axiom Peptides', 'woocommerce' ); ?>"
				class="axiom-thankyou-brand-logo"
			/>
		</div>

		<section class="axiom-payment-confirmation-hero">
			<p class="axiom-payment-confirmation-kicker"><?php esc_html_e( 'Order submitted', 'woocommerce' ); ?></p>
			<h1><?php esc_html_e( 'Complete Your Payment', 'woocommerce' ); ?></h1>
			<p class="axiom-payment-confirmation-copy">
				<?php esc_html_e( 'Your order has been created successfully, but it is not complete until payment is sent and confirmed. Please use the payment section below and include your order number with your payment.', 'woocommerce' ); ?>
			</p>
		</section>

		<section class="axiom-payment-status-card">
			<div class="axiom-payment-status-top">
				<div class="axiom-payment-status-icon-wrap">
					<div class="axiom-payment-status-icon">
						<i class="fa-solid fa-check"></i>
					</div>
				</div>

				<div class="axiom-payment-status-heading">
					<span><?php esc_html_e( 'Order Number', 'woocommerce' ); ?></span>
					<h2>#<?php echo esc_html( $order_number ); ?></h2>
				</div>
			</div>

			<div class="axiom-payment-status-rows">
				<div class="axiom-payment-status-row">
					<span><?php esc_html_e( 'Status', 'woocommerce' ); ?></span>
					<strong><?php echo esc_html( $order_status ); ?></strong>
				</div>

				<div class="axiom-payment-status-row">
					<span><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
					<strong><?php echo wp_kses_post( wc_price( $order_subtotal ) ); ?></strong>
				</div>

				<div class="axiom-payment-status-row">
					<span><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></span>
					<strong><?php echo wp_kses_post( wc_price( $order_shipping ) ); ?></strong>
				</div>

				<?php if ( $order_tax > 0 ) : ?>
					<div class="axiom-payment-status-row">
						<span><?php esc_html_e( 'Tax', 'woocommerce' ); ?></span>
						<strong><?php echo wp_kses_post( wc_price( $order_tax ) ); ?></strong>
					</div>
				<?php endif; ?>

				<div class="axiom-payment-status-row axiom-payment-status-row--total">
					<span><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
					<strong><?php echo wp_kses_post( wc_price( $order_total ) ); ?></strong>
				</div>
			</div>

			<div class="axiom-payment-estimates">
				<div class="axiom-payment-estimate-card">
					<span><?php esc_html_e( 'Estimated Ship Date', 'woocommerce' ); ?></span>
					<strong><?php echo esc_html( $estimated_ship_date ); ?></strong>
					<p><?php esc_html_e( 'We ship same day Monday through Friday. Weekend orders ship the next business day.', 'woocommerce' ); ?></p>
				</div>

				<div class="axiom-payment-estimate-card">
					<span><?php esc_html_e( 'Estimated Delivery', 'woocommerce' ); ?></span>
					<strong><?php echo esc_html( $estimated_delivery_date ); ?></strong>
					<p><?php echo esc_html( $shipping_label ? $shipping_label : __( 'Selected shipping method', 'woocommerce' ) ); ?></p>
				</div>
			</div>
		</section>

		<?php
		/*
		 * Your custom lower sections already working.
		 */
		axiom_render_custom_thankyou_sections( $order_id );
		?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order_id ); ?>
		<?php do_action( 'woocommerce_thankyou', $order_id ); ?>

	</div>
</div>
