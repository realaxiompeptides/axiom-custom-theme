<?php
defined('ABSPATH') || exit;

global $product;

$product_name = $product ? $product->get_name() : get_the_title();
?>

<section class="axiom-product-tabs-conversion" id="axiomProductTabsConversion">
  <div class="axiom-product-tabs-head">
    <button class="axiom-product-tab-btn is-active" type="button" data-tab="reviews">
      <i class="fa-solid fa-star"></i>
      Reviews
    </button>

    <button class="axiom-product-tab-btn" type="button" data-tab="faq">
      <i class="fa-solid fa-circle-question"></i>
      FAQ
    </button>
  </div>

  <div class="axiom-product-tab-panel is-active" data-panel="reviews">
    <div class="axiom-review-summary-card">
      <div class="axiom-review-score">
        <div class="axiom-review-stars">★★★★★</div>
        <strong>Verified reviews coming soon</strong>
        <p>Only real reviews from verified purchasers will be displayed here.</p>
      </div>

      <div class="axiom-review-proof">
        <span><i class="fa-solid fa-check"></i> Verified purchasers only</span>
        <span><i class="fa-solid fa-flask"></i> Research-use feedback only</span>
        <span><i class="fa-solid fa-ban"></i> No medical claims allowed</span>
      </div>
    </div>

    <div class="axiom-review-empty-card">
      <h3>Be the first to review <?php echo esc_html($product_name); ?></h3>
      <p>Share feedback about ordering, packaging, shipping, and product presentation. Reviews mentioning human use, treatment, results, or medical claims will not be published.</p>

      <a class="axiom-review-write-btn" href="#respond">
        Write a Review
      </a>
    </div>
  </div>

  <div class="axiom-product-tab-panel" data-panel="faq">
    <div class="axiom-faq-list">

      <details class="axiom-faq-item" open>
        <summary>What does “research use only” mean?</summary>
        <p>This product is intended strictly for laboratory, analytical, and in-vitro research use only. It is not for human or veterinary consumption.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>Do you include a Certificate of Analysis?</summary>
        <p>When available, COA documents are shown on the product page or in the COA library so researchers can review testing information before ordering.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>Where does this product ship from?</summary>
        <p>Orders are fulfilled from the United States and shipped discreetly with tracking.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>How fast do orders ship?</summary>
        <p>Orders placed before the posted cutoff are typically prepared for same-day shipment when inventory is available.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>How should this product be stored?</summary>
        <p>Storage recommendations may vary by product. Always review the product label, COA, and product-specific storage information before use in a research setting.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>Can this be used for human consumption?</summary>
        <p>No. This product is not for human consumption and is not intended to diagnose, treat, cure, or prevent any disease.</p>
      </details>

    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const section = document.getElementById('axiomProductTabsConversion');
  if (!section) return;

  const buttons = section.querySelectorAll('.axiom-product-tab-btn');
  const panels = section.querySelectorAll('.axiom-product-tab-panel');

  buttons.forEach(function (button) {
    button.addEventListener('click', function () {
      const target = button.getAttribute('data-tab');

      buttons.forEach(function (btn) {
        btn.classList.remove('is-active');
      });

      panels.forEach(function (panel) {
        panel.classList.remove('is-active');
      });

      button.classList.add('is-active');

      const activePanel = section.querySelector('[data-panel="' + target + '"]');
      if (activePanel) {
        activePanel.classList.add('is-active');
      }
    });
  });
});
</script>
