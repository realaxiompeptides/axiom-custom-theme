<?php
defined('ABSPATH') || exit;
?>

<section class="axiom-kits-grid-section section-pad" id="axiomKitsGrid">
  <div class="container">
    <div class="axiom-kits-grid-header">
      <p class="axiom-kits-section-kicker">Available Kits</p>
      <h2>Research kits & bulk options</h2>
      <p>Compare bundle pricing and review the savings against ordering the same quantity as single-vial products on our site.</p>
    </div>

    <?php if (!empty($kits_products)) : ?>
      <div class="axiom-kits-grid">
        <?php foreach ($kits_products as $kit_product) : ?>
          <?php
          if (!$kit_product || !is_a($kit_product, 'WC_Product')) {
              continue;
          }

          $kit_id       = $kit_product->get_id();
          $kit_name     = $kit_product->get_name();
          $kit_link     = get_permalink($kit_id);
          $kit_image    = $kit_product->get_image('large');
          $kit_price    = $kit_product->get_price();
          $kit_price_ui = $kit_product->get_price_html();
          $savings      = axiom_get_kit_savings_data($kit_product);
          ?>
          <article class="axiom-kit-card">
            <a href="<?php echo esc_url($kit_link); ?>" class="axiom-kit-card-image-link">
              <div class="axiom-kit-card-image">
                <?php echo $kit_image ? $kit_image : wc_placeholder_img('large'); ?>
              </div>
            </a>

            <div class="axiom-kit-card-body">
              <h3 class="axiom-kit-card-title">
                <a href="<?php echo esc_url($kit_link); ?>"><?php echo esc_html($kit_name); ?></a>
              </h3>

              <div class="axiom-kit-card-price">
                <?php echo wp_kses_post($kit_price_ui); ?>
              </div>

              <div class="axiom-kit-card-meta">
                <span><i class="fa-solid fa-globe"></i> International warehouse</span>
                <span><i class="fa-brands fa-bitcoin"></i> Crypto only</span>
                <span><i class="fa-solid fa-truck-fast"></i> <?php echo esc_html($kits_data['shipping_window']); ?></span>
              </div>

              <?php if (!empty($savings['has_savings'])) : ?>
                <div class="axiom-kit-savings-box">
                  <div class="axiom-kit-savings-row">
                    <span>Buying individually</span>
                    <strong><?php echo wp_kses_post(wc_price($savings['normal_total'])); ?></strong>
                  </div>
                  <div class="axiom-kit-savings-row">
                    <span>Kit price</span>
                    <strong><?php echo wp_kses_post(wc_price($savings['kit_total'])); ?></strong>
                  </div>
                  <div class="axiom-kit-savings-row axiom-kit-savings-row-highlight">
                    <span>You save</span>
                    <strong>
                      <?php
                      echo wp_kses_post(wc_price($savings['savings_amount']));
                      echo ' (' . esc_html($savings['savings_percentage']) . '%)';
                      ?>
                    </strong>
                  </div>
                </div>
              <?php else : ?>
                <div class="axiom-kit-savings-box axiom-kit-savings-box-note">
                  <p>Bulk pricing designed for stronger per-vial value compared with smaller individual ordering.</p>
                </div>
              <?php endif; ?>

              <a href="<?php echo esc_url($kit_link); ?>" class="axiom-kits-button axiom-kits-button-primary axiom-kit-card-button">
                View Kit
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else : ?>
      <div class="axiom-kits-empty">
        <h3>No kits are live yet</h3>
        <p>We have not added any active kit products yet. Please check back soon.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
