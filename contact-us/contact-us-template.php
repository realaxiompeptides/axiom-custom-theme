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
          Need help with an order, tracking, COAs, shipping, or payment confirmation? Contact our support team below.
        </p>

        <div class="axiom-contact-hero-badges">
          <div class="axiom-contact-hero-badge">
            <i class="fa-solid fa-envelope"></i>
            <span>Fast support</span>
          </div>
          <div class="axiom-contact-hero-badge">
            <i class="fa-solid fa-vial-circle-check"></i>
            <span>COA help</span>
          </div>
          <div class="axiom-contact-hero-badge">
            <i class="fa-solid fa-truck-fast"></i>
            <span>USPS shipping</span>
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
              For fastest support, include your order number, email used at checkout, and a short description of what you need help with.
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

              <a class="axiom-contact-info-item axiom-contact-info-item-link" href="mailto:support@axiomresearch.shop">
                <div class="axiom-contact-info-icon">
                  <i class="fa-solid fa-envelope"></i>
                </div>
                <div class="axiom-contact-info-copy">
                  <span>Email support</span>
                  <strong>support@axiomresearch.shop</strong>
                  <small>Best for order help and payment confirmation</small>
                </div>
                <div class="axiom-contact-arrow">
                  <i class="fa-solid fa-arrow-right"></i>
                </div>
              </a>

              <a class="axiom-contact-info-item axiom-contact-info-item-link axiom-contact-whatsapp" href="https://wa.me/15307019349" target="_blank" rel="noopener">
                <div class="axiom-contact-info-icon">
                  <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div class="axiom-contact-info-copy">
                  <span>WhatsApp</span>
                  <strong>+1 (530) 701-9349</strong>
                  <small>Quick support from a USA number</small>
                </div>
                <div class="axiom-contact-arrow">
                  <i class="fa-solid fa-arrow-right"></i>
                </div>
              </a>

              <a class="axiom-contact-info-item axiom-contact-info-item-link axiom-contact-telegram" href="https://t.me/axiompeptides" target="_blank" rel="noopener">
                <div class="axiom-contact-info-icon">
                  <i class="fa-brands fa-telegram"></i>
                </div>
                <div class="axiom-contact-info-copy">
                  <span>Telegram</span>
                  <strong>@axiompeptides</strong>
                  <small>Message us directly on Telegram</small>
                </div>
                <div class="axiom-contact-arrow">
                  <i class="fa-solid fa-arrow-right"></i>
                </div>
              </a>

              <a class="axiom-contact-info-item axiom-contact-info-item-link axiom-contact-discord" href="https://discord.gg/bg8d3wF6E" target="_blank" rel="noopener">
                <div class="axiom-contact-info-icon">
                  <i class="fa-brands fa-discord"></i>
                </div>
                <div class="axiom-contact-info-copy">
                  <span>Discord group</span>
                  <strong>Join our community</strong>
                  <small>Updates, support, and community questions</small>
                </div>
                <div class="axiom-contact-arrow">
                  <i class="fa-solid fa-arrow-right"></i>
                </div>
              </a>

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
                  <span>Shipping carrier</span>
                  <strong>USPS shipping with tracking</strong>
                </div>
              </div>

            </div>
          </div>

          <div class="axiom-contact-help-card axiom-contact-faq-card">
            <p class="axiom-contact-section-kicker">Frequently asked questions</p>
            <h3>Quick answers</h3>

            <div class="axiom-contact-help-list">
              <div class="axiom-contact-help-item">
                <strong>Where can I find COAs?</strong>
                <span>COAs are available on product pages when provided. If you need help finding one, contact support with the product name.</span>
              </div>

              <div class="axiom-contact-help-item">
                <strong>How do I track my order?</strong>
                <span>Tracking details are sent after fulfillment. Please include your order number if you need a tracking update.</span>
              </div>

              <div class="axiom-contact-help-item">
                <strong>How long does shipping take?</strong>
                <span>Most orders ship through USPS. Delivery time depends on the service selected and USPS transit speed.</span>
              </div>

              <div class="axiom-contact-help-item">
                <strong>What payment methods do you support?</strong>
                <span>We support multiple payment options. For payment confirmation, include your order number and payment method.</span>
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
