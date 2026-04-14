<?php
defined('ABSPATH') || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}

$cart_items        = WC()->cart ? WC()->cart->get_cart() : array();
$applied_coupons   = WC()->cart ? WC()->cart->get_coupons() : array();
$chosen_methods    = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods', array() ) : array();
$shipping_packages = WC()->shipping()->get_packages();
?>

<div id="payment" class="woocommerce-checkout-payment axiom-payment-wrap">

	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<div class="axiom-payment-methods-section">
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
		</div>
	<?php endif; ?>

	<div class="axiom-payment-coupon-section">
		<h3 class="axiom-payment-section-title"><?php esc_html_e( 'Have a gift card?', 'woocommerce' ); ?></h3>

		<div class="axiom-payment-coupon-box">
			<div class="axiom-inline-coupon-feedback" style="display:none;"></div>

			<form class="axiom-inline-coupon-form" method="post" action="">
				<div class="axiom-inline-coupon-row">
					<input
						type="text"
						name="coupon_code"
						class="input-text axiom-inline-coupon-input"
						placeholder="<?php echo esc_attr__( 'Enter your code…', 'woocommerce' ); ?>"
						value=""
					/>
					<button
						type="submit"
						class="button axiom-inline-coupon-button"
						name="apply_coupon"
						value="<?php echo esc_attr__( 'Apply', 'woocommerce' ); ?>"
					>
						<?php esc_html_e( 'Apply', 'woocommerce' ); ?>
					</button>
				</div>
			</form>

			<?php if ( ! empty( $applied_coupons ) ) : ?>
				<div class="axiom-applied-coupons">
					<?php foreach ( $applied_coupons as $coupon_code => $coupon ) : ?>
						<span class="axiom-applied-coupon-chip">
							<?php echo esc_html( wc_format_coupon_code( $coupon_code ) ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="axiom-payment-subtotal-section">
		<h3 class="axiom-payment-section-title"><?php esc_html_e( 'Order summary', 'woocommerce' ); ?></h3>

		<div class="axiom-summary-card">
			<div class="axiom-summary-items">
				<?php foreach ( $cart_items as $cart_item_key => $cart_item ) : ?>
					<?php
					$product = isset( $cart_item['data'] ) ? $cart_item['data'] : false;

					if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
						continue;
					}

					$product_name = $product->get_name();
					$quantity     = (int) $cart_item['quantity'];
					$line_total   = WC()->cart->get_product_subtotal( $product, $quantity );
					$image        = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );

					if ( ! $image ) {
						$image = wc_placeholder_img_src();
					}
					?>
					<div class="axiom-summary-item">
						<div class="axiom-summary-item-left">
							<div class="axiom-summary-thumb">
								<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>">
							</div>

							<div class="axiom-summary-copy">
								<strong><?php echo esc_html( $product_name ); ?></strong>
								<span><?php echo esc_html__( 'Qty:', 'woocommerce' ); ?> <?php echo esc_html( $quantity ); ?></span>
							</div>
						</div>

						<div class="axiom-summary-price">
							<?php echo wp_kses_post( $line_total ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="axiom-summary-totals">
				<div class="axiom-summary-row">
					<span><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
					<strong><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></strong>
				</div>

				<?php if ( ! empty( $applied_coupons ) ) : ?>
					<?php foreach ( $applied_coupons as $coupon_code => $coupon ) : ?>
						<?php
						$discount_amount = WC()->cart->get_coupon_discount_amount( $coupon_code, WC()->cart->display_cart_ex_tax() );
						?>
						<div class="axiom-summary-row axiom-summary-row--discount">
							<span>
								<?php
								printf(
									esc_html__( 'Discount (%s)', 'woocommerce' ),
									esc_html( wc_format_coupon_code( $coupon_code ) )
								);
								?>
							</span>
							<strong>-<?php echo wp_kses_post( wc_price( $discount_amount ) ); ?></strong>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<div class="axiom-summary-row">
					<span><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></span>
					<strong>
						<?php
						$shipping_label = esc_html__( 'Calculated at checkout', 'woocommerce' );

						if ( ! empty( $shipping_packages ) ) {
							foreach ( $shipping_packages as $package_index => $package ) {
								if ( ! empty( $package['rates'] ) && isset( $chosen_methods[ $package_index ] ) ) {
									foreach ( $package['rates'] as $rate_id => $rate ) {
										if ( $rate_id === $chosen_methods[ $package_index ] ) {
											$shipping_label = wc_cart_totals_shipping_method_label( $rate );
											break 2;
										}
									}
								}
							}
						}

						echo wp_kses_post( $shipping_label );
						?>
					</strong>
				</div>

				<?php if ( wc_tax_enabled() && WC()->cart->get_taxes_total() > 0 ) : ?>
					<div class="axiom-summary-row">
						<span><?php esc_html_e( 'Tax', 'woocommerce' ); ?></span>
						<strong><?php echo wp_kses_post( wc_price( WC()->cart->get_taxes_total() ) ); ?></strong>
					</div>
				<?php endif; ?>

				<div class="axiom-summary-row axiom-summary-total">
					<span><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
					<strong><?php echo wp_kses_post( WC()->cart->get_total() ); ?></strong>
				</div>
			</div>

			<div class="axiom-summary-benefits">
				<div class="axiom-summary-benefit">🔒 <span><?php esc_html_e( 'SSL encrypted checkout', 'woocommerce' ); ?></span></div>
				<div class="axiom-summary-benefit">🧪 <span><?php esc_html_e( 'Third-party verified quality', 'woocommerce' ); ?></span></div>
				<div class="axiom-summary-benefit">📄 <span><?php esc_html_e( 'COA included with applicable products', 'woocommerce' ); ?></span></div>
			</div>
		</div>
	</div>

	<div class="axiom-checkout-research-wrap axiom-checkout-research-wrap--payment">
		<div class="axiom-research-use-box">
			<div class="axiom-research-use-icon">
				<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
			</div>

			<div class="axiom-research-use-copy">
				<strong><?php esc_html_e( 'I acknowledge this order is for research use only', 'woocommerce' ); ?></strong>
				<p><?php esc_html_e( 'All products are intended strictly for laboratory, analytical, and in-vitro research use only. Not for human or veterinary consumption.', 'woocommerce' ); ?></p>
			</div>
		</div>

		<?php
		woocommerce_form_field(
			'axiom_research_use_ack',
			array(
				'type'     => 'checkbox',
				'class'    => array( 'form-row-wide', 'axiom-checkout-checkbox-row' ),
				'required' => true,
				'label'    => __( 'I understand and agree', 'woocommerce' ),
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
						'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.',
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
				<strong><?php esc_html_e( '256-bit SSL', 'woocommerce' ); ?></strong>
				<small><?php esc_html_e( 'Encrypted', 'woocommerce' ); ?></small>
			</div>

			<div class="axiom-place-order-trust-item">
				<span class="axiom-place-order-trust-emoji">✔️</span>
				<strong><?php esc_html_e( '99%+ Purity', 'woocommerce' ); ?></strong>
				<small><?php esc_html_e( 'Third-Party Verified', 'woocommerce' ); ?></small>
			</div>

			<div class="axiom-place-order-trust-item">
				<span class="axiom-place-order-trust-emoji">🇺🇸</span>
				<strong><?php esc_html_e( 'U.S. Based', 'woocommerce' ); ?></strong>
				<small><?php esc_html_e( 'California', 'woocommerce' ); ?></small>
			</div>

			<div class="axiom-place-order-trust-item">
				<span class="axiom-place-order-trust-emoji">📦</span>
				<strong><?php esc_html_e( 'Same-Day Ship', 'woocommerce' ); ?></strong>
				<small><?php esc_html_e( 'Before 2PM PST', 'woocommerce' ); ?></small>
			</div>
		</div>
	</div>
</div>

<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
?>
