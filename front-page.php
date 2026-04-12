<?php get_header(); ?>

<main>
  <section class="hero-section">
    <div class="hero-bg">
      <?php $hero = get_template_directory_uri() . '/assets/images/hero-image.PNG'; ?>
      <img src="<?php echo esc_url($hero); ?>" alt="Laboratory research environment" />
    </div>
    <div class="hero-overlay"></div>

    <div class="hero-content container hero-content-centered-mobile">
      <p class="hero-kicker">Premium Research Compounds</p>
      <h1>Your Supplier For Research Compounds</h1>
      <p class="hero-subtext">
        Research-grade peptides engineered for precision. 99%+ verified purity with independent third-party testing on every batch.
      </p>
      <div class="hero-actions">
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-white">Shop Now</a>
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
            <p>Every batch is third-party tested, helping ensure research-grade quality and consistency.</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-file-circle-check"></i></div>
          <div class="axiom-proof-content">
            <h3>COA Transparency</h3>
            <p>Transparent documentation and batch quality focus for research-use products.</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-truck-fast"></i></div>
          <div class="axiom-proof-content">
            <h3>Fast Fulfillment</h3>
            <p>Fast order handling and clear tracking workflow once fulfilled.</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-flag-usa"></i></div>
          <div class="axiom-proof-content">
            <h3>USA Fulfilled</h3>
            <p>Domestic fulfillment experience designed for speed and reliability.</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section class="homepage-collection section-pad" id="homepage-collection">
    <div class="container">
      <div class="section-heading homepage-collection-heading">
        <h2>Shop Best Sellers</h2>
        <p>The research compounds most frequently chosen by laboratories and independent researchers.</p>
      </div>

      <div id="homepageCollectionGrid">
        <?php
        $featured_query = new WP_Query(array(
          'post_type'      => 'product',
          'post_status'    => 'publish',
          'posts_per_page' => 8,
          'orderby'        => 'date',
          'order'          => 'DESC',
        ));

        if ($featured_query->have_posts()) :
          while ($featured_query->have_posts()) :
            $featured_query->the_post();
            global $product;

            if (!$product || !is_a($product, 'WC_Product')) {
              continue;
            }

            $image_id   = $product->get_image_id();
            $image_html = $image_id
              ? wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, array('alt' => $product->get_name()))
              : wc_placeholder_img('woocommerce_thumbnail');
            ?>
            <article class="homepage-product-card">
              <div class="homepage-product-image-wrap">
                <?php if ($product->is_on_sale()) : ?>
                  <span class="homepage-product-badge">SALE</span>
                <?php endif; ?>

                <a href="<?php the_permalink(); ?>" class="homepage-product-image-link">
                  <?php echo $image_html; ?>
                </a>
              </div>

              <div class="homepage-product-card-body">
                <h3 class="homepage-product-title">
                  <a href="<?php the_permalink(); ?>"><?php echo esc_html($product->get_name()); ?></a>
                </h3>

                <div class="homepage-product-price-block">
                  <?php if ($product->is_on_sale() && $product->get_regular_price()) : ?>
                    <span class="homepage-product-old-price"><?php echo wp_kses_post(wc_price($product->get_regular_price())); ?></span>
                  <?php endif; ?>

                  <span class="homepage-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                </div>

                <a href="<?php the_permalink(); ?>" class="homepage-product-button">View Product</a>
              </div>
            </article>
            <?php
          endwhile;
          wp_reset_postdata();
        endif;
        ?>
      </div>

      <div class="section-cta homepage-collection-cta">
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary-home">View Full Catalog</a>
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
        <p>Answers to the most common questions about our research products, processing times, and research-use policies.</p>
      </div>

      <div class="axiom-faq-list">
        <details class="axiom-faq-item">
          <summary>Are your products for human consumption? <i class="fa-solid fa-plus"></i></summary>
          <p>No. All products offered by Axiom Peptides are sold strictly for laboratory, analytical, and in-vitro research use only.</p>
        </details>

        <details class="axiom-faq-item">
          <summary>Do you provide third-party testing? <i class="fa-solid fa-plus"></i></summary>
          <p>We focus on transparency and batch quality documentation for research-use products.</p>
        </details>

        <details class="axiom-faq-item">
          <summary>How quickly do orders ship? <i class="fa-solid fa-plus"></i></summary>
          <p>Orders are processed as quickly as possible after payment confirmation and fulfillment handling.</p>
        </details>

        <details class="axiom-faq-item">
          <summary>How can I track my order? <i class="fa-solid fa-plus"></i></summary>
          <p>You can use the tracking page once your order has been fulfilled and tracking has been assigned.</p>
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
