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
      <div class="axiom-checkout-topbar">
        <div class="axiom-checkout-brand">
          <img
            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/axiom-logo.PNG'); ?>"
            alt="Axiom Peptides logo"
          />
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
        <div class="axiom-checkout-grid">
          <div class="axiom-checkout-main">
            <?php
            $contact_shipping_template = get_template_directory() . '/checkout/contact-shippng.php';
            if (file_exists($contact_shipping_template)) {
              include $contact_shipping_template;
            }
            ?>

            <?php
            $research_box_template = get_template_directory() . '/checkout/checkout-research-box.php';
            if (file_exists($research_box_template)) {
              include $research_box_template;
            }
            ?>

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
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/venmo.jpg'); ?>" alt="Venmo" />
                  </span>
                  <span class="axiom-payment-image">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/zelle.jpg'); ?>" alt="Zelle" />
                  </span>
                  <span class="axiom-payment-icon"><i class="fa-brands fa-bitcoin"></i></span>
                </div>

                <?php do_action('woocommerce_checkout_before_order_review'); ?>

                <div id="order_review" class="woocommerce-checkout-review-order">
                  <?php do_action('woocommerce_checkout_order_review'); ?>
                </div>

                <?php do_action('woocommerce_checkout_after_order_review'); ?>
              </div>
            </section>
          </div>

          <aside class="axiom-checkout-sidebar">
            <?php
            $sidebar_template = get_template_directory() . '/checkout/checkout-order-sidebar.php';
            if (file_exists($sidebar_template)) {
              include $sidebar_template;
            }
            ?>
          </aside>
        </div>
      </form>
    </div>
  </section>
</main>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
