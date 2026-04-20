<?php
defined('ABSPATH') || exit;
?>

<section class="axiom-kits-hero section-pad">
  <div class="container">
    <div class="axiom-kits-hero-inner">
      <p class="axiom-kits-kicker">Bulk Orders</p>
      <h1><?php echo esc_html($kits_data['title']); ?></h1>
      <p class="axiom-kits-subtitle"><?php echo esc_html($kits_data['subtitle']); ?></p>

      <div class="axiom-kits-hero-actions">
        <a href="#axiomKitsGrid" class="axiom-kits-button axiom-kits-button-primary">Browse Kits</a>
        <a href="<?php echo esc_url($kits_data['shop_url']); ?>" class="axiom-kits-button axiom-kits-button-secondary">Shop Single Vials</a>
      </div>
    </div>
  </div>
</section>
