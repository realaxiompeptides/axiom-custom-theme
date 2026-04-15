<?php
defined( 'ABSPATH' ) || exit;

if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
	return;
}

$theme_uri      = get_template_directory_uri();
$order_id       = $order->get_id();
$order_number   = $order->get_order_number();
$order_date     = $order->get_date_created() ? wc_format_datetime( $order->get_date_created() ) : '';
$order_total    = $order->get_formatted_order_total();
$payment_method = $order->get_payment_method_title();
$order_email    = $order->get_billing_email();
?>

<div class="axiom-thankyou-page">
	<div class="axiom-thankyou-shell">

		<div class="axiom-thankyou-header-card">
			<div class="axiom-thankyou-brand">
				<img
					src="<?php echo esc_url( $theme_uri . '/assets/images/axiom-menu-logo.PNG' ); ?>"
					alt="<?php esc_attr_e( 'Axiom Peptides', 'woocommerce' ); ?>"
				/>
			</div>

			<div class="axiom-thankyou-intro">
				<p class="axiom-thankyou-kicker"><?php esc_html_e( 'Order confirmed', 'woocommerce' ); ?></p>
				<h1><?php esc_html_e( 'Thank you. Your order has been received.', 'woocommerce' ); ?></h1>
				<p><?php esc_html_e( 'Please review your order details below.', 'woocommerce' ); ?></p>
			</div>

			<ul class="axiom-thankyou-order-overview">
				<li class="axiom-thankyou-meta-card">
					<span><?php esc_html_e( 'Order number', 'woocommerce' ); ?></span>
					<strong><?php echo esc_html( $order_number ); ?></strong>
				</li>

				<li class="axiom-thankyou-meta-card">
					<span><?php esc_html_e( 'Date', 'woocommerce' ); ?></span>
					<strong><?php echo esc_html( $order_date ); ?></strong>
				</li>

				<?php if ( $order_email ) : ?>
					<li class="axiom-thankyou-meta-card">
						<span><?php esc_html_e( 'Email', 'woocommerce' ); ?></span>
						<strong><?php echo esc_html( $order_email ); ?></strong>
					</li>
				<?php endif; ?>

				<li class="axiom-thankyou-meta-card">
					<span><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
					<strong><?php echo wp_kses_post( $order_total ); ?></strong>
				</li>

				<?php if ( $payment_method ) : ?>
					<li class="axiom-thankyou-meta-card">
						<span><?php esc_html_e( 'Payment method', 'woocommerce' ); ?></span>
						<strong><?php echo esc_html( $payment_method ); ?></strong>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<div class="axiom-thankyou-status-card axiom-thankyou-status-card--failed">
				<h2><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank or merchant has declined your transaction.', 'woocommerce' ); ?></h2>
				<p><?php esc_html_e( 'Please attempt your purchase again.', 'woocommerce' ); ?></p>

				<div class="axiom-thankyou-actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay">
						<?php esc_html_e( 'Pay', 'woocommerce' ); ?>
					</a>

					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button account">
							<?php esc_html_e( 'My account', 'woocommerce' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

		<?php else : ?>

			<div class="axiom-thankyou-content">
				<?php wc_get_template( 'order/order-details.php', array( 'order_id' => $order_id ) ); ?>
				<?php wc_get_template( 'order/order-details-customer.php', array( 'order_id' => $order_id ) ); ?>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	</div>
</div>
