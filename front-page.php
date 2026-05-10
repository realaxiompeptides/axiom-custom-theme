<?php get_header(); ?>

<main>
  <section class="hero-section hero-section--axiom-brand">
    <?php
    $theme_uri        = get_template_directory_uri();
    $hero_abstract    = $theme_uri . '/assets/images/hero-abstract-bg.PNG';
    $hero_vial        = $theme_uri . '/assets/images/hero-vial.PNG';
    $hero_fallback    = $theme_uri . '/assets/images/hero-image.PNG';
    $shop_page_url    = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    ?>

    <div class="hero-visual-bg" aria-hidden="true">
      <div class="hero-gradient-layer"></div>
      <div class="hero-grid-layer"></div>
      <div class="hero-glow hero-glow--one"></div>
      <div class="hero-glow hero-glow--two"></div>
      <div class="hero-strand hero-strand--left"></div>
      <div class="hero-strand hero-strand--right"></div>

      <img
        class="hero-abstract-image"
        src="<?php echo esc_url($hero_abstract); ?>"
        alt=""
        loading="eager"
        decoding="async"
        onerror="this.style.display='none';"
      />
    </div>

    <div class="hero-inner container">
      <div class="hero-copy">
        <p class="hero-kicker">Premium Research Compounds</p>

        <h1>Your Supplier For Research Compounds</h1>

        <p class="hero-subtext">
          Research-grade peptides engineered for precision, transparent batch documentation, and fast U.S. fulfillment.
        </p>

        <div class="hero-actions">
          <a href="<?php echo esc_url($shop_page_url); ?>" class="btn btn-white">
            Shop All Peptides
            <span class="btn-arrow">→</span>
          </a>

          <a href="<?php echo esc_url(home_url('/coa-page/')); ?>" class="btn btn-outline">
            View COA Library
          </a>
        </div>
      </div>

      <div class="hero-product-stage" aria-hidden="true">
        <div class="hero-product-orbit hero-product-orbit--one"></div>
        <div class="hero-product-orbit hero-product-orbit--two"></div>

        <div class="hero-floating-card hero-floating-card--top">
          <span class="hero-floating-card-icon">
            <i class="fa-solid fa-vial-circle-check"></i>
          </span>
          <span>
            <strong>COA Verified</strong>
            <small>Batch documentation</small>
          </span>
        </div>

        <div class="hero-floating-card hero-floating-card--left">
          <span class="hero-floating-card-icon">
            <i class="fa-solid fa-shield-halved"></i>
          </span>
          <span>
            <strong>Research Use Only</strong>
            <small>Clear product standards</small>
          </span>
        </div>

        <div class="hero-floating-card hero-floating-card--right">
          <span class="hero-floating-card-icon">
            <i class="fa-solid fa-truck-fast"></i>
          </span>
          <span>
            <strong>Fast Fulfillment</strong>
            <small>Orders before 2 PM PST</small>
          </span>
        </div>

        <div class="hero-vial-wrap">
          <div class="hero-vial-glow"></div>

          <img
            class="hero-vial-image"
            src="<?php echo esc_url($hero_vial); ?>"
            alt="Axiom Peptides research vial"
            loading="eager"
            decoding="async"
            onerror="this.src='<?php echo esc_url($hero_fallback); ?>'; this.classList.add('hero-vial-image--fallback');"
          />
        </div>
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
          High-quality products, transparent documentation, and fast reliable fulfillment.
        </p>
      </div>

      <div class="axiom-proof-grid">
        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-vial-circle-check"></i></div>
          <div class="axiom-proof-content">
            <h3>99%+ Purity</h3>
            <p>Quality Verified</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-file-circle-check"></i></div>
          <div class="axiom-proof-content">
            <h3>Third-Party Lab Tested</h3>
            <p>Independently Verified</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-truck-fast"></i></div>
          <div class="axiom-proof-content">
            <h3>Same-Day Shipping</h3>
            <p>Fast Order Dispatch</p>
          </div>
        </article>

        <article class="axiom-proof-card">
          <div class="axiom-proof-icon"><i class="fa-solid fa-box"></i></div>
          <div class="axiom-proof-content">
            <h3>Discreet Packaging</h3>
            <p>Private Delivery</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <?php
  $floating_vials_section = get_template_directory() . '/homepage/floating-vials-section.php';
  if (file_exists($floating_vials_section)) {
      include $floating_vials_section;
  }
  ?>

  <section class="homepage-collection section-pad" id="homepage-collection">
    <div class="container">
      <div class="section-heading homepage-collection-heading">
        <h2>Shop Best Sellers</h2>
        <p>The research compounds most frequently chosen by laboratories and independent researchers.</p>
      </div>

      <div id="homepageCollectionGrid">
        <?php
        $homepage_product_slugs = array(
          'glp-3-rt',
          'tesamorelin',
          'ghk-cu',
          'mots-c',
          'nad',
          'mt-1',
          'mt-2',
          'semax',
          'selank',
          'cjc-1295-no-dac',
          'ipamorelin',
          'bpc-157',
          'tb-500',
        );

        $homepage_product_ids = array();

        foreach ($homepage_product_slugs as $slug) {
          $product_post = get_page_by_path($slug, OBJECT, 'product');
          if ($product_post) {
            $homepage_product_ids[] = (int) $product_post->ID;
          }
        }

        $homepage_products = array();

        if (!empty($homepage_product_ids)) {
          $homepage_products = wc_get_products(array(
            'include' => $homepage_product_ids,
            'limit'   => -1,
            'status'  => 'publish',
            'orderby' => 'include',
          ));
        }

        if (!empty($homepage_products)) :
          foreach ($homepage_products as $product) :
            if (!$product || !is_a($product, 'WC_Product')) {
              continue;
            }

            $product_id   = $product->get_id();
            $product_name = $product->get_name();
            $product_link = get_permalink($product_id);

            $image_id   = $product->get_image_id();
            $image_html = $image_id
              ? wp_get_attachment_image($image_id, 'large', false, array('alt' => $product_name))
              : wc_placeholder_img('large');

            $is_on_sale      = $product->is_on_sale();
            $stock_status    = $product->get_stock_status();
            $is_out_of_stock = ($stock_status === 'outofstock');
            $is_on_backorder = ($stock_status === 'onbackorder');

            $badge_text  = '';
            $badge_class = '';

            if ($is_out_of_stock) {
              $badge_text  = 'Out of Stock';
              $badge_class = 'is-out-of-stock';
            } elseif ($is_on_backorder) {
              $badge_text  = 'Backorder';
              $badge_class = 'is-backorder';
            } elseif ($is_on_sale) {
              $badge_text  = 'Sale';
              $badge_class = 'is-sale';
            }

            $regular_price_value = '';
            $current_price_value = '';

            if ($product->is_type('variable')) {
              $regular_price_value = $product->get_variation_regular_price('min', true);
              $sale_price_value    = $product->get_variation_sale_price('min', true);
              $price_value         = $product->get_variation_price('min', true);

              if ($is_on_sale && $regular_price_value !== '' && $sale_price_value !== '') {
                $current_price_value = $sale_price_value;
              } else {
                $current_price_value = $price_value;
              }
            } else {
              $regular_price_value = $product->get_regular_price();
              $sale_price_value    = $product->get_sale_price();
              $price_value         = $product->get_price();

              if ($is_on_sale && $regular_price_value !== '' && $sale_price_value !== '') {
                $current_price_value = $sale_price_value;
              } else {
                $current_price_value = $price_value;
              }
            }

            $show_old_price = (
              $is_on_sale &&
              $regular_price_value !== '' &&
              $current_price_value !== '' &&
              (float) $regular_price_value > (float) $current_price_value
            );
            ?>
            <article class="homepage-product-card">
              <div class="homepage-product-image-wrap">
                <?php if ($badge_text) : ?>
                  <span class="homepage-product-badge <?php echo esc_attr($badge_class); ?>">
                    <?php echo esc_html($badge_text); ?>
                  </span>
                <?php endif; ?>

                <a href="<?php echo esc_url($product_link); ?>" class="homepage-product-image-link">
                  <?php echo $image_html; ?>
                </a>
              </div>

              <div class="homepage-product-card-body">
                <h3 class="homepage-product-title">
                  <a href="<?php echo esc_url($product_link); ?>"><?php echo esc_html($product_name); ?></a>
                </h3>

                <div class="homepage-product-price-block">
                  <?php if ($show_old_price) : ?>
                    <span class="homepage-product-old-price">
                      <?php echo wp_kses_post(wc_price($regular_price_value)); ?>
                    </span>
                  <?php endif; ?>

                  <span class="homepage-product-current-price">
                    <?php echo wp_kses_post(wc_price($current_price_value)); ?>
                  </span>
                </div>

                <a href="<?php echo esc_url($product_link); ?>" class="homepage-product-button">View Product</a>
              </div>
            </article>
            <?php
          endforeach;
        endif;
        ?>
      </div>

      <?php
      $all_published_products_count = wp_count_posts('product')->publish;
      $homepage_products_count      = count($homepage_product_ids);
      $remaining_products_count     = max(0, (int) $all_published_products_count - (int) $homepage_products_count);

      $catalog_button_text = $remaining_products_count > 0
        ? 'View ' . $remaining_products_count . ' More Products'
        : 'View Full Catalog';
      ?>

      <div class="section-cta homepage-collection-cta">
        <a href="<?php echo esc_url($shop_page_url); ?>" class="btn btn-primary-home">
          <?php echo esc_html($catalog_button_text); ?>
        </a>
      </div>
    </div>
  </section>

  <?php
  $coa_trust_section = get_template_directory() . '/homepage/coa-trust-section.php';
  if (file_exists($coa_trust_section)) {
      include $coa_trust_section;
  }
  ?>

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
