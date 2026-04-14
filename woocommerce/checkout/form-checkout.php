<?php
defined('ABSPATH') || exit;

wc_print_notices();

if (
	!$checkout->is_registration_enabled() &&
	$checkout->is_registration_required() &&
	!is_user_logged_in()
) {
	echo esc_html(
		apply_filters(
			'woocommerce_checkout_must_be_logged_in_message',
			__('You must be logged in to checkout.', 'woocommerce')
		)
	);
	return;
}

$theme_uri  = get_template_directory_uri();
$home_url   = home_url('/');
$cart_url   = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
$cart_count = function_exists('WC') && WC()->cart ? absint(WC()->cart->get_cart_contents_count()) : 0;

$checkout_fields = $checkout->get_checkout_fields();
$billing_fields  = isset($checkout_fields['billing']) ? $checkout_fields['billing'] : array();

$contact_keys = array('billing_email', 'billing_phone');
?>

<main class="axiom-checkout-page">
  <section class="axiom-checkout-shell">
    <div class="axiom-checkout-fluid">

      <div class="axiom-checkout-topbar">
        <div class="axiom-checkout-topbar-row">
          <a href="<?php echo esc_url($home_url); ?>" class="axiom-checkout-brand" aria-label="Axiom Peptides home">
            <img
              src="<?php echo esc_url($theme_uri . '/assets/images/axiom-menu-logo.PNG'); ?>"
              alt="Axiom Peptides logo"
            />
          </a>

          <a
            href="<?php echo esc_url($cart_url); ?>"
            class="axiom-checkout-cart-link"
            aria-label="Back to cart"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2Zm10 0c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2ZM7.17 14h9.96c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 21.58 5H6.21l-.94-2H2v2h2l3.6 7.59-1.35 2.44A1.98 1.98 0 0 0 6 16c0 1.1.9 2 2 2h12v-2H8l1.17-2Z"/>
            </svg>
            <span class="axiom-checkout-cart-count"><?php echo esc_html($cart_count); ?></span>
          </a>
        </div>

        <div class="axiom-checkout-secure-note">
          <span><i class="fa-solid fa-lock"></i> Secure checkout</span>
          <span><i class="fa-solid fa-shield-halved"></i> Research use only</span>
        </div>
      </div>

      <form
        name="checkout"
        method="post"
        class="checkout woocommerce-checkout axiom-checkout-form"
        action="<?php echo esc_url(wc_get_checkout_url()); ?>"
        enctype="multipart/form-data"
      >
        <div class="axiom-checkout-grid axiom-checkout-grid--single">
          <div class="axiom-checkout-main axiom-checkout-main--full">

            <section class="axiom-checkout-card axiom-checkout-contact-card">
              <div class="axiom-checkout-card-header">
                <h2 class="axiom-checkout-main-title">Checkout</h2>
                <p>Enter your billing and shipping details below.</p>
              </div>

              <div class="axiom-checkout-card-body">
                <div class="axiom-checkout-contact-section">
                  <h3>Contact Information</h3>

                  <?php
                  foreach ($contact_keys as $field_key) {
                    if (isset($billing_fields[$field_key])) {
                      woocommerce_form_field(
                        $field_key,
                        $billing_fields[$field_key],
                        $checkout->get_value($field_key)
                      );
                    }
                  }
                  ?>

                  <p class="form-row form-row-wide axiom-checkout-email-optin">
                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                      <input
                        id="axiom_email_optin_visual"
                        class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                        type="checkbox"
                      />
                      <span>I would like to receive exclusive emails with discounts and product information</span>
                    </label>
                  </p>
                </div>

                <div class="axiom-checkout-billing-section">
                  <h3>Billing details</h3>

                  <?php
                  foreach ($billing_fields as $field_key => $field_args) {
                    if (in_array($field_key, $contact_keys, true)) {
                      continue;
                    }

                    woocommerce_form_field(
                      $field_key,
                      $field_args,
                      $checkout->get_value($field_key)
                    );
                  }
                  ?>
                </div>

                <?php do_action('woocommerce_checkout_shipping'); ?>

                <?php if (WC()->cart && WC()->cart->needs_shipping()) : ?>
                  <?php
                  $packages = WC()->shipping()->get_packages();
                  $chosen_methods = WC()->session ? (array) WC()->session->get('chosen_shipping_methods', array()) : array();
                  ?>

                  <section class="axiom-checkout-card axiom-checkout-shipping-methods-card">
                    <div class="axiom-checkout-card-header">
                      <p class="axiom-checkout-kicker">Shipping</p>
                      <h2>Available shipping methods</h2>
                      <p>Select the shipping option for this order.</p>
                    </div>

                    <div class="axiom-checkout-card-body">
                      <?php if (!empty($packages)) : ?>
                        <?php foreach ($packages as $package_index => $package) : ?>
                          <?php
                          $available_methods = isset($package['rates']) ? $package['rates'] : array();
                          $chosen_method = isset($chosen_methods[$package_index]) ? $chosen_methods[$package_index] : '';
                          ?>

                          <div class="axiom-checkout-shipping-package">
                            <?php if (!empty($available_methods)) : ?>
                              <ul class="axiom-checkout-shipping-method-list" id="shipping_method">
                                <?php foreach ($available_methods as $method) : ?>
                                  <?php
                                  $method_id = 'shipping_method_' . $package_index . '_' . sanitize_title($method->id);
                                  ?>
                                  <li class="axiom-checkout-shipping-method-item">
                                    <input
                                      type="radio"
                                      class="shipping_method"
                                      name="shipping_method[<?php echo esc_attr($package_index); ?>]"
                                      data-index="<?php echo esc_attr($package_index); ?>"
                                      id="<?php echo esc_attr($method_id); ?>"
                                      value="<?php echo esc_attr($method->id); ?>"
                                      <?php checked($method->id, $chosen_method); ?>
                                    />
                                    <label for="<?php echo esc_attr($method_id); ?>">
                                      <?php echo wp_kses_post(wc_cart_totals_shipping_method_label($method)); ?>
                                    </label>
                                  </li>
                                <?php endforeach; ?>
                              </ul>
                            <?php else : ?>
                              <p class="axiom-checkout-shipping-empty">
                                Enter your full shipping address above to view available shipping methods.
                              </p>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      <?php else : ?>
                        <p class="axiom-checkout-shipping-empty">
                          Enter your full shipping address above to view available shipping methods.
                        </p>
                      <?php endif; ?>
                    </div>
                  </section>
                <?php endif; ?>
              </div>
            </section>

            <section class="axiom-checkout-card axiom-checkout-payment-card">
              <div class="axiom-checkout-card-header">
                <p class="axiom-checkout-kicker">Payment</p>
                <h2>Complete payment</h2>
                <p>Choose your payment method and place your order securely.</p>
              </div>

              <div class="axiom-checkout-card-body">
                <div class="axiom-checkout-payment-icons">
                  <span class="axiom-payment-icon"><i class="fa-brands fa-cc-visa"></i></span>
                  <span class="axiom-payment-icon"><i class="fa-brands fa-cc-mastercard"></i></span>
                  <span class="axiom-payment-icon"><i class="fa-brands fa-cc-amex"></i></span>
                  <span class="axiom-payment-icon"><i class="fa-brands fa-cc-discover"></i></span>

                  <span class="axiom-payment-image">
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/venmo.jpg'); ?>" alt="Venmo" />
                  </span>

                  <span class="axiom-payment-image">
                    <img src="<?php echo esc_url($theme_uri . '/assets/images/zelle.jpg'); ?>" alt="Zelle" />
                  </span>

                  <span class="axiom-payment-icon"><i class="fa-brands fa-bitcoin"></i></span>
                </div>

                <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

                <div class="axiom-checkout-review-wrap">
                  <h3 id="order_review_heading" class="axiom-order-review-heading">
                    <?php esc_html_e('Order summary', 'woocommerce'); ?>
                  </h3>

                  <?php do_action('woocommerce_checkout_before_order_review'); ?>

                  <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action('woocommerce_checkout_order_review'); ?>
                  </div>

                  <?php do_action('woocommerce_checkout_after_order_review'); ?>
                </div>
              </div>
            </section>

          </div>
        </div>
      </form>
    </div>
  </section>
</main>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
