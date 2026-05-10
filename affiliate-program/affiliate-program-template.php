<?php
/*
Template Name: Affiliate Program Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$registration_url = home_url('/affiliate-registration/');
$account_url      = home_url('/affiliate-account/');
?>

<main class="axiom-affiliate-page axiom-affiliate-program-page">

  <section class="axiom-affiliate-hero">
    <div class="container">
      <div class="axiom-affiliate-hero-card">
        <p class="axiom-affiliate-kicker">Axiom Affiliate Program</p>

        <h1>Partner with Axiom</h1>

        <p class="axiom-affiliate-subtitle">
          Earn commissions by referring customers to Axiom Research. Get your own affiliate dashboard,
          referral link, and partner code after approval.
        </p>

        <div class="axiom-affiliate-hero-pills">
          <span><i class="fa-solid fa-percent"></i> 10% commission</span>
          <span><i class="fa-solid fa-cookie-bite"></i> 30-day tracking</span>
          <span><i class="fa-solid fa-ticket"></i> Custom partner code</span>
          <span><i class="fa-solid fa-chart-line"></i> Affiliate dashboard</span>
        </div>

        <div class="axiom-affiliate-hero-actions">
          <a class="axiom-affiliate-primary-btn" href="<?php echo esc_url($registration_url); ?>">
            Apply Now
          </a>

          <a class="axiom-affiliate-secondary-link" href="<?php echo esc_url($account_url); ?>">
            Already approved? Log in
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="axiom-affiliate-main">
    <div class="container">

      <div class="axiom-affiliate-program-stats">
        <div class="axiom-affiliate-program-stat">
          <div class="axiom-affiliate-program-stat-icon">
            <i class="fa-solid fa-sack-dollar"></i>
          </div>
          <strong>10%</strong>
          <span>Commission per approved sale</span>
        </div>

        <div class="axiom-affiliate-program-stat">
          <div class="axiom-affiliate-program-stat-icon">
            <i class="fa-solid fa-clock"></i>
          </div>
          <strong>30 Days</strong>
          <span>Affiliate cookie duration</span>
        </div>

        <div class="axiom-affiliate-program-stat">
          <div class="axiom-affiliate-program-stat-icon">
            <i class="fa-solid fa-ticket"></i>
          </div>
          <strong>Partner Code</strong>
          <span>Affiliates can request their own code</span>
        </div>
      </div>

      <div class="axiom-affiliate-grid">
        <div class="axiom-affiliate-left">

          <div class="axiom-affiliate-card">
            <p class="axiom-affiliate-section-kicker">Why join</p>
            <h2>Grow with Axiom</h2>

            <p class="axiom-affiliate-section-copy">
              We are looking for partners who can introduce Axiom to qualified audiences through clean,
              professional, and compliant promotion.
            </p>

            <div class="axiom-affiliate-feature-list">
              <div class="axiom-affiliate-feature-item">
                <div class="axiom-affiliate-feature-icon">
                  <i class="fa-solid fa-link"></i>
                </div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Your own referral link</strong>
                  <span>Share your unique affiliate link with your audience and track visits from your dashboard.</span>
                </div>
              </div>

              <div class="axiom-affiliate-feature-item">
                <div class="axiom-affiliate-feature-icon">
                  <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Your own partner code</strong>
                  <span>Request a custom partner code during signup. Once approved, we can create it for you.</span>
                </div>
              </div>

              <div class="axiom-affiliate-feature-item">
                <div class="axiom-affiliate-feature-icon">
                  <i class="fa-solid fa-chart-column"></i>
                </div>
                <div class="axiom-affiliate-feature-copy">
                  <strong>Performance dashboard</strong>
                  <span>Track visits, referrals, commissions, and earnings from your affiliate account.</span>
                </div>
              </div>
            </div>
          </div>

          <div class="axiom-affiliate-card">
            <p class="axiom-affiliate-section-kicker">Program rules</p>
            <h2>Promote responsibly</h2>

            <p class="axiom-affiliate-section-copy">
              Affiliates must follow Axiom’s research-use positioning and avoid making medical, disease,
              treatment, or human-use claims.
            </p>

            <div class="axiom-affiliate-rules-grid">
              <div class="axiom-affiliate-rule-box">
                <strong>Research-use only</strong>
                <span>Promotions must keep products positioned for laboratory and research-use purposes.</span>
              </div>

              <div class="axiom-affiliate-rule-box">
                <strong>No medical claims</strong>
                <span>Do not claim products treat, cure, prevent, or diagnose any condition.</span>
              </div>

              <div class="axiom-affiliate-rule-box">
                <strong>No spam traffic</strong>
                <span>Do not use misleading, spammy, or low-quality traffic methods.</span>
              </div>

              <div class="axiom-affiliate-rule-box">
                <strong>Approval required</strong>
                <span>Affiliate accounts and partner codes are reviewed before going live.</span>
              </div>
            </div>
          </div>

        </div>

        <aside class="axiom-affiliate-right">

          <div class="axiom-affiliate-card axiom-affiliate-apply-card">
            <p class="axiom-affiliate-section-kicker">Apply now</p>
            <h2>Become an affiliate</h2>

            <p class="axiom-affiliate-section-copy">
              Apply for the program, request your partner code, and tell us how you plan to promote Axiom.
            </p>

            <a class="axiom-affiliate-primary-btn axiom-affiliate-full-btn" href="<?php echo esc_url($registration_url); ?>">
              Apply Now
            </a>

            <p class="axiom-affiliate-login-text">
              Already approved?
              <a href="<?php echo esc_url($account_url); ?>">Log in to your dashboard</a>
            </p>
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
