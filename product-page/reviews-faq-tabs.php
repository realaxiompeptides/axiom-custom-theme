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
        <p>All products sold by Axiom Peptides are intended strictly for in-vitro research and laboratory use only. They are not intended for human or animal consumption. By purchasing, you confirm that you are acquiring these products for legitimate research purposes.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>How should I store this product?</summary>
        <p>For best stability, store lyophilized products in a cool, dry place away from direct sunlight. After reconstitution, storage requirements may vary by product. Always review the product label, COA, and product-specific documentation.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>Do you include a Certificate of Analysis (COA)?</summary>
        <p>Yes. When available, products include a third-party Certificate of Analysis verifying identity and purity. COAs can be viewed directly on eligible product pages or in our COA library.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>What is your shipping time?</summary>
        <p>Orders are fulfilled from the United States. Most orders are processed quickly and shipped with tracking. Delivery times depend on the selected shipping method and carrier performance.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>What is your return policy?</summary>
        <p>If an order arrives damaged, incorrect, or affected by a fulfillment issue, contact our support team as soon as possible. We review eligible issues and may offer a replacement, store credit, or refund depending on the situation.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>Are Janoshik Analytical & Freedom Labs independent labs?</summary>
        <p>Yes. Janoshik Analytical and Freedom Labs are third-party testing laboratories. When a COA is available, researchers can review the report details, batch information, and testing data before ordering.</p>
      </details>

      <details class="axiom-faq-item">
        <summary>How do your prices compare to other suppliers?</summary>
        <p>We aim to offer research products at competitive prices while keeping quality, COA access, discreet packaging, and reliable fulfillment as priorities.</p>
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
