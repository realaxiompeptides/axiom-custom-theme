<?php
defined('ABSPATH') || exit;
?>

<section class="axiom-kits-faq section-pad">
  <div class="container">
    <div class="axiom-kits-grid-header">
      <p class="axiom-kits-section-kicker">FAQ</p>
      <h2>Common kit questions</h2>
    </div>

    <div class="axiom-kits-faq-list">
      <details class="axiom-kits-faq-item">
        <summary>Where do kit orders ship from?</summary>
        <p>Kit orders are fulfilled through our international warehouse and are separate from standard USA-fulfilled single-vial products.</p>
      </details>

      <details class="axiom-kits-faq-item">
        <summary>How much is shipping for kits?</summary>
        <p>Kit orders currently ship at a flat international shipping rate of <?php echo esc_html($kits_data['shipping_cost']); ?>.</p>
      </details>

      <details class="axiom-kits-faq-item">
        <summary>How long does shipping take?</summary>
        <p>Estimated delivery is approximately <?php echo esc_html($kits_data['shipping_window']); ?> after order processing and shipment confirmation.</p>
      </details>

      <details class="axiom-kits-faq-item">
        <summary>What payment methods are accepted?</summary>
        <p>Kit orders currently accept cryptocurrency payment only.</p>
      </details>

      <details class="axiom-kits-faq-item">
        <summary>Can I combine kits with regular products?</summary>
        <p>No. Kit orders should be placed separately from standard products because they follow different fulfillment and payment rules.</p>
      </details>
    </div>

    <div class="axiom-kits-contact-box">
      <h3>Need help with a larger order?</h3>
      <p>If you need help choosing a kit or want to ask about larger-volume ordering, contact us directly.</p>
      <a href="<?php echo esc_url($kits_data['contact_url']); ?>" class="axiom-kits-button axiom-kits-button-secondary">Contact Us</a>
    </div>
  </div>
</section>
