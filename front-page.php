<?php get_header(); ?>

<main>
  <section class="hero-section">
    <div class="hero-bg">
      <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-image.PNG'); ?>" alt="Laboratory research environment" />
    </div>
    <div class="hero-overlay"></div>

    <div class="hero-content container">
      <p class="hero-kicker">Premium Research Compounds</p>
      <h1>Your Supplier For Research Compounds</h1>
      <p class="hero-subtext">
        Research-grade peptides engineered for precision. 99%+ verified purity with independent third-party testing on every batch.
      </p>

      <div class="hero-actions">
        <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="btn btn-white">Shop Now</a>
      </div>
    </div>
  </section>

  <section class="axiom-proof-strip section-pad">
    <div class="container">
      <div class="axiom-proof-header">
        <span class="axiom-proof-pill">
          <i class="fa-solid fa-shield-halved"></i>
          Research-Grade Standards
        </span>

        <h2>Why Labs Choose Axiom</h2>

        <p>
          Every peptide we ship is backed by rigorous testing, transparent documentation, and a commitment to high quality products.
        </p>
      </div>

      <div class="axiom-proof-grid">
        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-vial-circle-check"></i></div>
          <div class="axiom-proof-content">
            <h3>99%+ Purity</h3>
            <p>Every batch is third-party tested via HPLC and mass spectrometry, guaranteeing research-grade purity you can trust.</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-file-circle-check"></i></div>
          <div class="axiom-proof-content">
            <h3>COAs With Every Order</h3>
            <p>Full Certificate of Analysis included with every product. Complete transparency on identity, purity, and composition.</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-truck-fast"></i></div>
          <div class="axiom-proof-content">
            <h3>Ships Within 24 Hours</h3>
            <p>Orders placed before 2 PM PST ship the same day. Tracking is provided with every order.</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-flag-usa"></i></div>
          <div class="axiom-proof-content">
            <h3>Made In USA</h3>
            <p>Synthesized and quality-controlled in American facilities, ensuring consistent supply chain and regulatory compliance.</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section class="homepage-collection section-pad" id="homepage-collection">
    <div class="container">
      <div class="section-heading homepage-collection-heading">
        <h2>Shop Best Sellers</h2>
        <p>
          The research compounds most frequently chosen by laboratories and independent researchers.
        </p>
      </div>

      <div id="homepageCollectionGrid">
        <?php
        $theme = get_template_directory_uri();
        $shop = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

        $products = array(
          array('name'=>'GLP-3 RT','old'=>'$45.00','price'=>'$39.00','image'=>'reta-5mg-main.PNG','slug'=>'glp-3-rt'),
          array('name'=>'GHK-CU','old'=>'$58.00 – $60.00','price'=>'$30.00 – $43.00','image'=>'ghk-cu-100mg-main.PNG','slug'=>'ghk-cu'),
          array('name'=>'BPC-157','old'=>'$35.00','price'=>'$25.00','image'=>'bpc-157-5mg-main.PNG','slug'=>'bpc-157'),
          array('name'=>'TB-500','old'=>'$45.00','price'=>'$36.00','image'=>'tb-500-5mg-main.PNG','slug'=>'tb-500'),
          array('name'=>'IPAMORELIN','old'=>'$40.00','price'=>'$34.00','image'=>'ipa-5mg-main.PNG','slug'=>'ipamorelin'),
          array('name'=>'CJC 1295 NO DAC','old'=>'$45.00','price'=>'$38.00','image'=>'cjc-1295-no-dac-5mg-main.PNG','slug'=>'cjc-1295-no-dac'),
          array('name'=>'MT-2','old'=>'$30.00','price'=>'$25.00','image'=>'mt-2-10mg-main.PNG','slug'=>'mt-2'),
          array('name'=>'MOTS-C','old'=>'$42.00','price'=>'$37.00','image'=>'mots-c-10mg-main.PNG','slug'=>'mots-c'),
        );

        foreach ($products as $product) :
          $product_link = trailingslashit($shop) . '?s=' . urlencode($product['slug']) . '&post_type=product';
        ?>
          <article class="homepage-product-card">
            <div class="homepage-product-image-wrap">
              <span class="homepage-product-badge">SALE</span>
              <img src="<?php echo esc_url($theme . '/assets/images/products/' . $product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>" />
            </div>

            <div class="homepage-product-card-body">
              <h3 class="homepage-product-title"><?php echo esc_html($product['name']); ?></h3>

              <div class="homepage-product-price-block">
                <span class="homepage-product-old-price"><?php echo esc_html($product['old']); ?></span>
                <span class="homepage-product-price"><?php echo esc_html($product['price']); ?></span>
              </div>

              <a href="<?php echo esc_url($product_link); ?>" class="homepage-product-button">View Product</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="section-cta homepage-collection-cta">
        <a href="<?php echo esc_url($shop); ?>" class="btn btn-primary-home">View Full Catalog</a>
      </div>
    </div>
  </section>

  <section class="axiom-faq section-pad">
    <div class="container">
      <div class="axiom-faq-header">
        <span class="axiom-faq-pill">
          <i class="fa-solid fa-circle-question"></i>
          Common Questions
        </span>

        <h2>Frequently Asked Questions</h2>
        <p>
          Answers to the most common questions about our research products, processing times, and research-use policies.
        </p>
      </div>

      <div class="axiom-faq-list">
        <details class="axiom-faq-item" open>
          <summary>Are your products for human consumption? <i class="fa-solid fa-plus"></i></summary>
          <p>No. All products offered by Axiom Peptides are sold strictly for laboratory, analytical, and in-vitro research use only.</p>
        </details>

        <details class="axiom-faq-item">
          <summary>Do you provide third-party testing? <i class="fa-solid fa-plus"></i></summary>
          <p>Yes. We emphasize transparency and quality, and we provide COA documentation for qualifying products and batches.</p>
        </details>

        <details class="axiom-faq-item">
          <summary>How quickly do orders ship? <i class="fa-solid fa-plus"></i></summary>
          <p>Most orders are processed quickly and typically ship within 24 business hours after payment confirmation.</p>
        </details>

        <details class="axiom-faq-item">
          <summary>How can I track my order? <i class="fa-solid fa-plus"></i></summary>
          <p>You can use our tracking page once your order has been fulfilled and a tracking number has been issued.</p>
        </details>
      </div>

      <div class="axiom-faq-cta">
        <p>Still have questions? Our research support team is here to help.</p>
        <a href="<?php echo esc_url(home_url('/contact-us/')); ?>" class="axiom-faq-button">
          Contact Us
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
