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
        <a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn btn-white">Shop Now</a>
      </div>
    </div>
  </section>

  <div id="trust-strip-mount"></div>
  <div id="homepage-collection-mount"></div>
  <div id="faq-section-mount"></div>
</main>

<?php get_footer(); ?>
