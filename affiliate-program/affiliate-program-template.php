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
        <h1>Earn commission by referring researchers to Axiom</h1>
        <p class="axiom-affiliate-subtitle">
          Join the Axiom Peptides affiliate program and earn commissions for qualified orders you refer. Share your referral link, grow your audience, and get rewarded.
        </p>

        <div class="axiom-affiliate-hero-badges">
          <div class="axiom-affiliate-badge">
            <i class="fa-solid fa-link"></i>
            <span>Referral link tracking</span>
          </div>
          <div class="axiom-affiliate-badge">
            <i class="fa-solid fa-chart-line"></i>
            <span>Dashboard reporting</span>
          </div>
          <div class="axiom-affiliate-badge">
            <i class="fa-solid fa-wallet"></i>
            <span>Commission payouts</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="axiom-affiliate-main">
    <div class="container">
      <div class="axiom-affiliate-grid">
        <div class="axiom-affiliate-content">
          <div class="axiom-affiliate-info-card">
            <p class="axiom-affiliate-section-kicker">Why join</p>
            <h2>Grow with Axiom</h2>
            <p>
              We’re building a trusted research-use brand and looking for affiliates who can introduce new customers to Axiom Peptides through compliant promotion and quality content.
            </p>

            <div class="axiom-affiliate-feature-list">
              <div class="axiom-affiliate-feature">
                <div class="axiom-affiliate-feature-icon">
                  <i class="fa-solid fa-bullhorn"></i>
                </div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Promote with your own link</strong>
                  <span>Share your referral link across your content, communities, and channels.</span>
                </div>
              </div>

              <div class="axiom-affiliate-feature">
                <div class="axiom-affiliate-feature-icon">
                  <i class="fa-solid fa-chart-column"></i>
                </div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Track clicks and orders</strong>
                  <span>Use your SliceWP affiliate dashboard to monitor performance and commissions.</span>
                </div>
              </div>

              <div class="axiom-affiliate-feature">
                <div class="axiom-affiliate-feature-icon">
                  <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Earn on qualified referrals</strong>
                  <span>Approved affiliates can earn commissions on successful referred purchases.</span>
                </div>
              </div>
            </div>
          </div>

          <div class="axiom-affiliate-info-card">
            <p class="axiom-affiliate-section-kicker">Program rules</p>
            <h2>Important guidelines</h2>

            <div class="axiom-affiliate-rules">
              <div class="axiom-affiliate-rule">
                <strong>Use compliant marketing</strong>
                <span>Do not make medical, therapeutic, or disease-related claims about products.</span>
              </div>

              <div class="axiom-affiliate-rule">
                <strong>No misleading promotion</strong>
                <span>Do not impersonate Axiom or misrepresent discounts, policies, or product uses.</span>
              </div>

              <div class="axiom-affiliate-rule">
                <strong>Research use only messaging</strong>
                <span>Products must be promoted only within appropriate research-use-only boundaries.</span>
              </div>

              <div class="axiom-affiliate-rule">
                <strong>Approval required</strong>
                <span>All affiliate applications are reviewed before access is granted.</span>
              </div>
            </div>
          </div>
        </div>

        <aside class="axiom-affiliate-sidebar">
          <div class="axiom-affiliate-signup-card" id="affiliate-signup">
            <p class="axiom-affiliate-section-kicker">Apply now</p>
            <h3>Become an affiliate</h3>
            <p class="axiom-affiliate-signup-copy">
              Complete the form below to apply. Once approved, you’ll get access to your affiliate dashboard and referral links.
            </p>

            <div class="axiom-affiliate-signup-form">
              <?php echo do_shortcode('[slicewp_affiliate_registration]'); ?>
            </div>
          </div>

          <div class="axiom-affiliate-side-card">
            <p class="axiom-affiliate-section-kicker">Need help?</p>
            <h3>Questions about the program?</h3>
            <p>
              Contact us if you need help with your application, referral setup, or affiliate account.
            </p>
            <a class="axiom-affiliate-contact-btn" href="<?php echo esc_url(home_url('/contact-us/')); ?>">
              Contact Support
            </a>
          </div>
        </aside>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
