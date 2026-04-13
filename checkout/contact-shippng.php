<section class="axiom-checkout-card axiom-checkout-contact-card">
  <div class="axiom-checkout-card-header">
    <p class="axiom-checkout-kicker">Contact Information</p>
    <h2>Contact & shipping</h2>
    <p>Enter your shipping details and order contact information.</p>
  </div>

  <div class="axiom-checkout-card-body">
    <?php if ($checkout->get_checkout_fields()) : ?>
      <?php do_action('woocommerce_checkout_before_customer_details'); ?>

      <div class="axiom-checkout-sections">
        <div class="axiom-checkout-section">
          <h3>Contact Information</h3>
          <?php
          woocommerce_form_field('billing_email', $checkout->get_checkout_fields('billing')['billing_email'], $checkout->get_value('billing_email'));
          woocommerce_form_field('billing_phone', $checkout->get_checkout_fields('billing')['billing_phone'], $checkout->get_value('billing_phone'));
          ?>
        </div>

        <div class="axiom-checkout-section">
          <h3>Shipping Address</h3>
          <?php
          $shipping_fields = $checkout->get_checkout_fields('shipping');

          foreach ($shipping_fields as $key => $field) {
            woocommerce_form_field($key, $field, $checkout->get_value($key));
          }
          ?>
        </div>

        <div class="axiom-checkout-section">
          <h3>Order Notes</h3>
          <?php
          $order_fields = $checkout->get_checkout_fields('order');

          if (!empty($order_fields['order_comments'])) {
            woocommerce_form_field('order_comments', $order_fields['order_comments'], $checkout->get_value('order_comments'));
          }
          ?>
        </div>
      </div>

      <?php do_action('woocommerce_checkout_after_customer_details'); ?>
    <?php endif; ?>
  </div>
</section>
