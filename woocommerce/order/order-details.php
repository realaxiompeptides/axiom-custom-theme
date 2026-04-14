<?php
defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id );

if ( ! $order ) {
	return;
}

$order_items       = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$applied_coupons    = $order->get_coupon_codes();
?>

<section class="axiom-thankyou-section axiom-thankyou-order-details">
	<div class="axiom-thankyou-section-header">
		<h2><?php esc_html_e( 'Order summary', 'woocommerce' ); ?></h2>
	</div>

	<div class="axiom-thankyou-summary-card">
		<div class="axiom-thankyou-summary-items">
			<?php foreach ( $order_items as $item_id => $item ) : ?>
				<?php
				$product = $item->get_product();

				if ( ! $product ) {
					continue;
				}

				$product_name = $item->get_name();
				$quantity     = $item->get_quantity();
				$line_total   = $order->get_formatted_line_subtotal( $item );
				$image        = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );

				if ( ! $image ) {
					$image = wc_placeholder_img_src();
				}
				?>
				<div class="axiom-thankyou-summary-item">
					<div class="axiom-thankyou-summary-item-left">
						<div class="axiom-thankyou-summary-thumb">
							<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>">
						</div>

						<div class="axiom-thankyou-summary-copy">
							<strong><?php echo esc_html( $product_name ); ?></strong>
							<span><?php esc_html_e( 'Qty:', 'woocommerce' ); ?> <?php echo esc_html( $quantity ); ?></span>

							<?php
							$item_meta = wc_display_item_meta(
								$item,
								array(
									'before'    => '<div class="axiom-thankyou-item-meta">',
									'after'     => '</div>',
									'separator' => '<br>',
									'echo'      => false,
								)
							);

							if ( $item_meta ) {
								echo wp_kses_post( $item_meta );
							}
							?>
						</div>
					</div>

					<div class="axiom-thankyou-summary-price">
						<?php echo wp_kses_post( $line_total ); ?>
					</div>
				</div>

				<?php if ( $show_purchase_note ) : ?>
					<?php $purchase_note = $product->get_purchase_note(); ?>
					<?php if ( $purchase_note ) : ?>
						<div class="axiom-thankyou-purchase-note">
							<?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<div class="axiom-thankyou-summary-totals">
			<div class="axiom-thankyou-summary-row">
				<span><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
				<strong><?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?></strong>
			</div>

			<?php if ( $order->get_shipping_total() > 0 ) : ?>
				<div class="axiom-thankyou-summary-row">
					<span><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></span>
					<strong>
						<?php
						$shipping_methods = $order->get_shipping_methods();
						$shipping_label   = '';

						if ( ! empty( $shipping_methods ) ) {
							$first_shipping = reset( $shipping_methods );
							$shipping_label = $first_shipping ? $first_shipping->get_name() : '';
						}

						echo wp_kses_post( wc_price( $order->get_shipping_total() ) );

						if ( $shipping_label ) {
							echo ' <small>' . esc_html__( 'via', 'woocommerce' ) . ' ' . esc_html( $shipping_label ) . '</small>';
						}
						?>
					</strong>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $applied_coupons ) ) : ?>
				<?php foreach ( $applied_coupons as $coupon_code ) : ?>
					<?php
					$discount_total = 0;

					foreach ( $order->get_items( 'coupon' ) as $coupon_item ) {
						if ( strtolower( $coupon_item->get_code() ) === strtolower( $coupon_code ) ) {
							$discount_total += (float) $coupon_item->get_discount();
						}
					}
					?>
					<div class="axiom-thankyou-summary-row axiom-thankyou-summary-row--discount">
						<span>
							<?php
							printf(
								esc_html__( 'Discount (%s)', 'woocommerce' ),
								esc_html( wc_format_coupon_code( $coupon_code ) )
							);
							?>
						</span>
						<strong>-<?php echo wp_kses_post( wc_price( $discount_total ) ); ?></strong>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( $order->get_total_tax() > 0 ) : ?>
				<div class="axiom-thankyou-summary-row">
					<span><?php esc_html_e( 'Tax', 'woocommerce' ); ?></span>
					<strong><?php echo wp_kses_post( wc_price( $order->get_total_tax() ) ); ?></strong>
				</div>
			<?php endif; ?>

			<div class="axiom-thankyou-summary-row axiom-thankyou-summary-row--total">
				<span><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
				<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
			</div>

			<div class="axiom-thankyou-summary-row">
				<span><?php esc_html_e( 'Payment method', 'woocommerce' ); ?></span>
				<strong><?php echo esc_html( $order->get_payment_method_title() ); ?></strong>
			</div>
		</div>
	</div>
</section>
