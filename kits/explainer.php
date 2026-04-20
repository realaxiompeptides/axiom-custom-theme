<?php
defined('ABSPATH') || exit;
?>

<section class="axiom-kits-explainer section-pad-sm">
  <div class="container">
    <div class="axiom-kits-explainer-card">
      <div class="axiom-kits-explainer-copy">
        <p class="axiom-kits-section-kicker">How It Works</p>
        <h2>Important kit order information</h2>
        <p>
          Kit orders are separate from our standard single-vial catalog. Standard products may be fulfilled from USA stock,
          while the kits listed here are fulfilled through our international warehouse.
        </p>

        <ul class="axiom-kits-rules">
          <li>Kits ship separately from standard USA-fulfilled products.</li>
          <li>Flat international shipping is currently <?php echo esc_html($kits_data['shipping_cost']); ?>.</li>
          <li>Estimated delivery time is approximately <?php echo esc_html($kits_data['shipping_window']); ?>.</li>
          <li>Payment for kit orders is currently accepted via cryptocurrency only.</li>
          <li>Kit and standard products should be placed as separate orders.</li>
        </ul>
      </div>

      <div class="axiom-kits-highlight-box">
        <span class="axiom-kits-highlight-label">Why order kits?</span>
        <strong>Better bulk pricing</strong>
        <p>Kit pricing is designed to lower your per-vial cost compared with ordering the same quantity individually.</p>
      </div>
    </div>
  </div>
</section>
