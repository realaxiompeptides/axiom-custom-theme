<?php
defined('ABSPATH') || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>

<div id="payment" class="woocommerce-checkout-payment axiom-payment-wrap">
	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li>';
				wc_print_notice(
					apply_filters(
						'woocommerce_no_available_payment_methods_message',
						WC()->customer && WC()->customer->get_billing_country()
							? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' )
							: esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' )
					),
					'notice'
				);
				echo '</li>';
			}
			?>
		</ul>
	<?php endif; ?>

	<div class="axiom-checkout-research-wrap axiom-checkout-research-wrap--payment">
		<p class="axiom-checkout-kicker">Required acknowledgment</p>
		<h3>Research use only</h3>
		<p class="axiom-checkout-research-help">
			All products are intended strictly for laboratory, analytical, and in-vitro research use only.
			Not for human or veterinary consumption.
		</p>

		<?php
		woocommerce_form_field(
			'axiom_research_use_ack',
			array(
				'type'     => 'checkbox',
				'class'    => array( 'form-row-wide', 'axiom-checkout-checkbox-row' ),
				'required' => true,
				'label'    => 'I acknowledge this order is for research use only',
			),
			WC()->checkout()->get_value( 'axiom_research_use_ack' )
		);
		?>
	</div>

	<div class="axiom-place-order-section">
		<div class="form-row place-order">
			<noscript>
				<?php
				printf(
					esc_html__(
						'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.',
						'woocommerce'
					),
					'<em>',
					'</em>'
				);
				?>
				<br />
				<button
					type="submit"
					class="button alt"
					name="woocommerce_checkout_update_totals"
					value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"
				>
					<?php esc_html_e( 'Update totals', 'woocommerce' ); ?>
				</button>
			</noscript>

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

			<?php
			echo apply_filters(
				'woocommerce_order_button_html',
				'<button type="submit" class="button alt axiom-place-order-button" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>'
			);
			?>

			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>

		<div class="axiom-place-order-trust-icons">
			<div class="axiom-place-order-trust-item">
				<span class="axiom-place-order-trust-emoji">🔒</span>
				<strong>99%+</strong>
				<small>Purity</small>
			</div>

			<div class="axiom-place-order-trust-item">
				<span class="axiom-place-order-trust-emoji">✅</span>
				<strong>Third-Party</strong>
				<small>Verified</small>
			</div>

			<div class="axiom-place-order-trust-item">
				<span class="axiom-place-order-trust-emoji">📍</span>
				<strong>U.S. Based</strong>
				<small>California</small>
			</div>

			<div class="axiom-place-order-trust-item">
				<span class="axiom-place-order-trust-emoji">📦</span>
				<strong>Same-Day Ship</strong>
				<small>Before 2PM PST</small>
			</div>
		</div>
	</div>
</div>

<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
?>
