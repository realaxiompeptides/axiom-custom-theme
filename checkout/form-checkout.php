<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}
?>

<main class="axiom-checkout-page">
  <section class="axiom-checkout-shell">
    <div class="container">
      <div class="axiom-checkout-top">
        <div class="axiom-checkout-heading">
          <p class="axiom-checkout-kicker">Secure Checkout</p>
          <h1>Complete Your Order</h1>
          <p class="axiom-checkout-subtext">
            Fast, secure checkout for research-use-only materials.
          </p>
        </div>

        <div class="axiom-checkout-trust-inline">
          <span><i class="fa-solid fa-shield-halved"></i> Secure checkout</span>
          <span><i class="fa-solid fa-truck-fast"></i> Fast USA fulfillment</span>
          <span><i class="fa-solid fa-flask"></i> Research use only</span>
        </div>
      </div>

      <form name="checkout" method="post" class="checkout woocommerce-checkout axiom-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

        <div class="axiom-checkout-layout">
          <div class="axiom-checkout-main">
            <section class="axiom-checkout-card">
              <div class="axiom-checkout-card-header">
                <span class="axiom-step-pill">1</span>
                <div>
                  <h2>Contact & Shipping</h2>
                  <p>Enter your details for fulfillment and order updates.</p>
                </div>
              </div>

              <div class="axiom-checkout-card-body">
                <?php if ($checkout->get_checkout_fields()) : ?>
                  <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                  <div class="axiom-checkout-fields">
                    <div class="axiom-checkout-fields-left">
                      <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>

                    <div class="axiom-checkout-fields-right">
                      <?php do_action('woocommerce_checkout_shipping'); ?>
                    </div>
                  </div>

                  <?php do_action('woocommerce_checkout_after_customer_details'); ?>
                <?php endif; ?>
              </div>
            </section>

            <section class="axiom-checkout-card">
              <div class="axiom-checkout-card-header">
                <span class="axiom-step-pill">2</span>
                <div>
                  <h2>Payment</h2>
                  <p>Select your payment method and place your order securely.</p>
                </div>
              </div>

              <div class="axiom-checkout-card-body">
                <div class="axiom-payment-icons">
                  <span class="payment-icon-pill"><i class="fa-brands fa-cc-visa"></i></span>
                  <span class="payment-icon-pill"><i class="fa-brands fa-cc-mastercard"></i></span>
                  <span class="payment-icon-pill"><i class="fa-brands fa-cc-amex"></i></span>
                  <span class="payment-icon-pill"><i class="fa-brands fa-cc-discover"></i></span>
                  <span class="payment-icon-pill payment-icon-image-pill">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/venmo.jpg'); ?>" alt="Venmo">
                  </span>
                  <span class="payment-icon-pill payment-icon-image-pill">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/zelle.jpg'); ?>" alt="Zelle">
                  </span>
                  <span class="payment-icon-pill"><i class="fa-brands fa-bitcoin"></i></span>
                </div>

                <div class="axiom-checkout-reassurance">
                  <div class="axiom-checkout-reassurance-item">
                    <i class="fa-solid fa-lock"></i>
                    <span>Encrypted checkout</span>
                  </div>
                  <div class="axiom-checkout-reassurance-item">
                    <i class="fa-solid fa-box"></i>
                    <span>Shipment protection included</span>
                  </div>
                  <div class="axiom-checkout-reassurance-item">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Research use only</span>
                  </div>
                </div>

                <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

                <div id="order_review" class="woocommerce-checkout-review-order">
                  <?php do_action('woocommerce_checkout_order_review'); ?>
                </div>

                <?php do_action('woocommerce_checkout_after_order_review'); ?>
              </div>
            </section>
          </div>

          <aside class="axiom-checkout-sidebar">
            <div class="axiom-checkout-summary-card">
              <div class="axiom-summary-header">
                <h2>Order Summary</h2>
                <p>Review your items before placing your order.</p>
              </div>

              <div class="axiom-summary-review" id="axiomCheckoutSidebarReview">
                <?php woocommerce_order_review(); ?>
              </div>
            </div>
          </aside>
        </div>
      </form>
    </div>
  </section>
</main>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
