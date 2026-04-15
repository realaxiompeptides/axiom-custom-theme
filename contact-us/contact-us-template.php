<?php
/*
Template Name: Contact Us Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="axiom-contact-page">
  <section class="axiom-contact-hero">
    <div class="container">
      <div class="axiom-contact-hero-card">
        <p class="axiom-contact-kicker">Contact Axiom Peptides</p>
        <h1>We’re here to help</h1>
        <p class="axiom-contact-subtitle">
          Questions about orders, shipping, payment, or general support? Send us a message and our team will get back to you as quickly as possible.
        </p>

        <div class="axiom-contact-hero-badges">
          <div class="axiom-contact-hero-badge">
            <i class="fa-solid fa-envelope"></i>
            <span>Fast email support</span>
          </div>
          <div class="axiom-contact-hero-badge">
            <i class="fa-solid fa-box"></i>
            <span>Order help available</span>
          </div>
          <div class="axiom-contact-hero-badge">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Research use only</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="axiom-contact-main">
    <div class="container">
      <div class="axiom-contact-grid">
        <div class="axiom-contact-form-card">
          <div class="axiom-contact-section-head">
            <p class="axiom-contact-section-kicker">Send a message</p>
            <h2>Get in touch</h2>
            <p>
              Use the form below for order questions, shipping issues, payment questions, or general support.
            </p>
          </div>

          <div class="axiom-contact-form-wrap">
            <?php
            while (have_posts()) :
                the_post();
                the_content();
            endwhile;
            ?>
          </div>
        </div>

        <aside class="axiom-contact-sidebar">
          <div class="axiom-contact-info-card">
            <p class="axiom-contact-section-kicker">Support details</p>
            <h3>Contact information</h3>

            <div class="axiom-contact-info-list">
              <div class="axiom-contact-info-item">
                <div class="axiom-contact-info-icon">
                  <i class="fa-solid fa-envelope"></i>
                </div>
                <div class="axiom-contact-info-copy">
                  <span>Email</span>
                  <strong><a href="mailto:realaxiompeptides@gmail.com">realaxiompeptides@gmail.com</a></strong>
                </div>
              </div>

              <div class="axiom-contact-info-item">
                <div class="axiom-contact-info-icon">
                  <i class="fa-solid fa-clock"></i>
                </div>
                <div class="axiom-contact-info-copy">
                  <span>Response time</span>
                  <strong>Usually within 1 business day</strong>
                </div>
              </div>

              <div class="axiom-contact-info-item">
                <div class="axiom-contact-info-icon">
                  <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div class="axiom-contact-info-copy">
                  <span>Shipping cutoff</span>
                  <strong>Before 2PM PST, Monday–Friday</strong>
                </div>
              </div>
            </div>
          </div>

          <div class="axiom-contact-help-card">
            <p class="axiom-contact-section-kicker">Common questions</p>
            <h3>What can we help with?</h3>

            <div class="axiom-contact-help-list">
              <div class="axiom-contact-help-item">
                <strong>Order status</strong>
                <span>Need an update on an existing order or shipment.</span>
              </div>

              <div class="axiom-contact-help-item">
                <strong>Payment questions</strong>
                <span>Questions about Zelle, Venmo, card payment, or confirmation.</span>
              </div>

              <div class="axiom-contact-help-item">
                <strong>Shipping support</strong>
                <span>Address issues, delivery questions, or tracking help.</span>
              </div>

              <div class="axiom-contact-help-item">
                <strong>General support</strong>
                <span>Questions about products, policies, or your account.</span>
              </div>
            </div>
          </div>

          <div class="axiom-contact-policy-card">
            <p class="axiom-contact-section-kicker">Important</p>
            <h3>Research use only</h3>
            <p>
              All products sold by Axiom Peptides are intended strictly for laboratory, analytical, and in-vitro research use only. Not for human or veterinary consumption.
            </p>
          </div>
        </aside>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
