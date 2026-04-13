<?php
defined('ABSPATH') || exit;
?>

<div class="axiom-order-review-box">
  <table class="shop_table woocommerce-checkout-review-order-table">
    <thead>
      <tr>
        <th><?php esc_html_e('Product', 'woocommerce'); ?></th>
        <th><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

        if ($_product && $_product->exists() && $cart_item['quantity'] > 0) : ?>
          <tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
            <td class="product-name">
              <?php echo wp_kses_post($_product->get_name()); ?>
              <strong class="product-quantity">&times;&nbsp;<?php echo esc_html($cart_item['quantity']); ?></strong>
              <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
            </td>
            <td class="product-total">
              <?php echo wp_kses_post(WC()->cart->get_product_subtotal($_product, $cart_item['quantity'])); ?>
            </td>
          </tr>
        <?php endif;
      endforeach; ?>
    </tbody>

    <tfoot>
      <tr class="cart-subtotal">
        <th><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
        <td><?php wc_cart_totals_subtotal_html(); ?></td>
      </tr>

      <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
        <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
          <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
          <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
        <?php do_action('woocommerce_review_order_before_shipping'); ?>
        <?php wc_cart_totals_shipping_html(); ?>
        <?php do_action('woocommerce_review_order_after_shipping'); ?>
      <?php endif; ?>

      <?php foreach (WC()->cart->get_fees() as $fee) : ?>
        <tr class="fee">
          <th><?php echo esc_html($fee->name); ?></th>
          <td><?php wc_cart_totals_fee_html($fee); ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
        <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
          <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
            <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
              <th><?php echo esc_html($tax->label); ?></th>
              <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr class="tax-total">
            <th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
            <td><?php wc_cart_totals_taxes_total_html(); ?></td>
          </tr>
        <?php endif; ?>
      <?php endif; ?>

      <tr class="order-total">
        <th><?php esc_html_e('Total', 'woocommerce'); ?></th>
        <td><?php wc_cart_totals_order_total_html(); ?></td>
      </tr>
    </tfoot>
  </table>
</div>
