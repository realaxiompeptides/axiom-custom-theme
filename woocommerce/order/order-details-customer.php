<?php
defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id );

if ( ! $order ) {
	return;
}

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>

<section class="axiom-thankyou-section axiom-thankyou-addresses">
	<div class="axiom-thankyou-address-grid">

		<div class="axiom-thankyou-address-card">
			<div class="axiom-thankyou-section-header">
				<h2><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h2>
			</div>

			<div class="axiom-thankyou-address-content">
				<address>
					<?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>

					<?php if ( $order->get_billing_email() ) : ?>
						<p class="axiom-thankyou-address-meta">
							<strong><?php esc_html_e( 'Email:', 'woocommerce' ); ?></strong>
							<span><?php echo esc_html( $order->get_billing_email() ); ?></span>
						</p>
					<?php endif; ?>

					<?php if ( $order->get_billing_phone() ) : ?>
						<p class="axiom-thankyou-address-meta">
							<strong><?php esc_html_e( 'Phone:', 'woocommerce' ); ?></strong>
							<span><?php echo esc_html( $order->get_billing_phone() ); ?></span>
						</p>
					<?php endif; ?>
				</address>
			</div>
		</div>

		<?php if ( $show_shipping ) : ?>
			<div class="axiom-thankyou-address-card">
				<div class="axiom-thankyou-section-header">
					<h2><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>
				</div>

				<div class="axiom-thankyou-address-content">
					<address>
						<?php echo wp_kses_post( $order->get_formatted_shipping_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>
					</address>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>
