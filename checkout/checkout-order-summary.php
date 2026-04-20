<?php
defined('ABSPATH') || exit;

$cart = function_exists('WC') ? WC()->cart : null;

if (!$cart) {
    return;
}
?>

<section class="axiom-checkout-card axiom-checkout-summary-card">
  <div class="axiom-checkout-card-header">
    <h2>Order Summary</h2>
  </div>

  <div class="axiom-summary-list">
    <?php foreach ($cart->get_cart() as $cart_item_key => $cart_item) : ?>
      <?php
      $product = isset($cart_item['data']) ? $cart_item['data'] : false;
      if (!$product || !is_a($product, 'WC_Product')) {
          continue;
      }

      $product_name = $product->get_name();
      $quantity     = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
      $line_total   = $cart->get_product_subtotal($product, $quantity);
      $image_url    = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail');

      if (!$image_url) {
          $image_url = wc_placeholder_img_src();
      }

      $variant_text = '';
      if (!empty($cart_item['variation']) && is_array($cart_item['variation'])) {
          $variant_parts = array();

          foreach ($cart_item['variation'] as $key => $value) {
              if (!$value) {
                  continue;
              }

              $label = wc_attribute_label(str_replace('attribute_', '', $key));
              $variant_parts[] = $label . ': ' . $value;
          }

          $variant_text = implode(' • ', $variant_parts);
      }
      ?>

      <div class="axiom-summary-item">
        <div class="axiom-summary-item-left">
          <div class="axiom-summary-thumb">
            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product_name); ?>">
          </div>

          <div class="axiom-summary-copy">
            <strong><?php echo esc_html($product_name); ?></strong>

            <?php if ($variant_text) : ?>
              <span><?php echo esc_html($variant_text); ?></span>
            <?php endif; ?>

            <span>Qty: <?php echo esc_html($quantity); ?></span>
          </div>
        </div>

        <div class="axiom-summary-price">
          <?php echo wp_kses_post($line_total); ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="axiom-summary-totals">
    <div class="axiom-summary-row">
      <span>Subtotal</span>
      <strong><?php echo wp_kses_post($cart->get_cart_subtotal()); ?></strong>
    </div>

    <?php if ($cart->get_coupons()) : ?>
      <?php foreach ($cart->get_coupons() as $code => $coupon) : ?>
        <?php $discount_amount = (float) $cart->get_coupon_discount_amount($code); ?>
        <div class="axiom-summary-row axiom-summary-row-discount">
          <span>Promo Code Discount</span>
          <strong>-<?php echo wp_kses_post(wc_price($discount_amount)); ?></strong>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($cart->get_fees()) : ?>
      <?php foreach ($cart->get_fees() as $fee) : ?>
        <div class="axiom-summary-row <?php echo $fee->amount < 0 ? 'axiom-summary-row-discount' : ''; ?>">
          <span><?php echo esc_html($fee->name); ?></span>
          <strong><?php echo wp_kses_post(wc_price($fee->total)); ?></strong>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="axiom-summary-row">
      <span>Shipping</span>
      <strong>
        <?php
        if ($cart->show_shipping()) {
            echo wp_kses_post(wc_price($cart->get_shipping_total()));
        } else {
            echo 'Calculated at checkout';
        }
        ?>
      </strong>
    </div>

    <?php if ((float) $cart->get_total_tax() > 0) : ?>
      <div class="axiom-summary-row">
        <span>Tax</span>
        <strong><?php echo wp_kses_post(wc_price($cart->get_total_tax())); ?></strong>
      </div>
    <?php endif; ?>

    <div class="axiom-summary-row axiom-summary-row-total">
      <span>Total</span>
      <strong><?php echo wp_kses_post($cart->get_total()); ?></strong>
    </div>
  </div>
</section>
