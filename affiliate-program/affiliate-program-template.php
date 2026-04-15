<?php
/*
Template Name: Affiliate Program Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="axiom-affiliate-page">
  <section class="axiom-affiliate-hero">
    <div class="container">
      <div class="axiom-affiliate-hero-card">
        <p class="axiom-affiliate-kicker">Axiom Affiliate Program</p>
        <h1>Partner with Axiom</h1>
        <p class="axiom-affiliate-subtitle">
          Join the Axiom Peptides affiliate program and earn commissions by referring qualified customers to our research-use product catalog.
        </p>

        <div class="axiom-affiliate-hero-pills">
          <span><i class="fa-solid fa-link"></i> Referral tracking</span>
          <span><i class="fa-solid fa-chart-line"></i> Performance dashboard</span>
          <span><i class="fa-solid fa-wallet"></i> Commission payouts</span>
        </div>

        <a class="axiom-affiliate-primary-btn" href="#affiliate-apply">
          Apply Now
        </a>
      </div>
    </div>
  </section>

  <section class="axiom-affiliate-main">
    <div class="container">
      <div class="axiom-affiliate-grid">
        <div class="axiom-affiliate-left">
          <div class="axiom-affiliate-card">
            <p class="axiom-affiliate-section-kicker">Why join</p>
            <h2>Grow with Axiom</h2>
            <p class="axiom-affiliate-section-copy">
              We are building a trusted research-use brand and looking for affiliates who can introduce new customers to Axiom through compliant, high-quality promotion.
            </p>

            <div class="axiom-affiliate-feature-list">
              <div class="axiom-affiliate-feature-item">
                <div class="axiom-affiliate-feature-icon"><i class="fa-solid fa-bullhorn"></i></div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Promote with your own referral link</strong>
                  <span>Share your referral link across your social media, content, or community.</span>
                </div>
              </div>

              <div class="axiom-affiliate-feature-item">
                <div class="axiom-affiliate-feature-icon"><i class="fa-solid fa-chart-column"></i></div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Track clicks and referrals</strong>
                  <span>Use your affiliate dashboard to monitor performance and commissions.</span>
                </div>
              </div>

              <div class="axiom-affiliate-feature-item">
                <div class="axiom-affiliate-feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Built for compliant promotion</strong>
                  <span>We want partners who can promote responsibly and professionally.</span>
                </div>
              </div>
            </div>
          </div>


        <aside class="axiom-affiliate-right">
          <div class="axiom-affiliate-card axiom-affiliate-apply-card" id="affiliate-apply">
            <p class="axiom-affiliate-section-kicker">Apply now</p>
            <h2>Become an affiliate</h2>
            <p class="axiom-affiliate-section-copy">
              Complete the form below to apply. Once approved, you will receive access to your affiliate dashboard and referral tools.
            </p>

            <div class="axiom-affiliate-form-note">
              For the “Website” field, you can enter your main Instagram, TikTok, YouTube, X, Linktree, or website URL.
            </div>

            <div class="axiom-affiliate-form-wrap">
              <?php echo do_shortcode('[slicewp_affiliate_registration]'); ?>
            </div>
          </div>

          <div class="axiom-affiliate-card axiom-affiliate-help-card">
            <p class="axiom-affiliate-section-kicker">Need help?</p>
            <h3>Questions about the program?</h3>
            <p>
              Contact us if you need help with your application, referral setup, or affiliate account.
            </p>
            <a class="axiom-affiliate-secondary-btn" href="<?php echo esc_url(home_url('/contact-us/')); ?>">
              Contact Support
            </a>
          </div>
        </aside>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
