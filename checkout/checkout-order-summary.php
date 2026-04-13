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

      $product_id   = $product->get_id();
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
  </div>
</section>
