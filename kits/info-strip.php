<?php
defined('ABSPATH') || exit;
?>

<section class="axiom-kits-info-strip">
  <div class="container">
    <div class="axiom-kits-info-grid">
      <article class="axiom-kits-info-card">
        <div class="axiom-kits-info-icon"><i class="fa-solid fa-globe"></i></div>
        <h3><?php echo esc_html($kits_data['warehouse_label']); ?></h3>
        <p>Kit products on this page are fulfilled separately from our international warehouse.</p>
      </article>

      <article class="axiom-kits-info-card">
        <div class="axiom-kits-info-icon"><i class="fa-brands fa-bitcoin"></i></div>
        <h3><?php echo esc_html($kits_data['payment_method']); ?></h3>
        <p>Kit orders currently accept cryptocurrency payment only at checkout.</p>
      </article>

      <article class="axiom-kits-info-card">
        <div class="axiom-kits-info-icon"><i class="fa-solid fa-truck-fast"></i></div>
        <h3><?php echo esc_html($kits_data['shipping_window']); ?></h3>
        <p>Estimated delivery after processing and shipment confirmation for kit orders.</p>
      </article>

      <article class="axiom-kits-info-card">
        <div class="axiom-kits-info-icon"><i class="fa-solid fa-box-open"></i></div>
        <h3><?php echo esc_html($kits_data['shipping_cost']); ?></h3>
        <p>Flat international shipping for kit orders placed through this section.</p>
      </article>
    </div>
  </div>
</section>
