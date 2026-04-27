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
          Need help with an order, tracking, COAs, shipping, bulk orders, or payment confirmation? Contact our support team below.
        </p>

        <div class="axiom-contact-hero-badges">
          <div class="axiom-contact-hero-badge">
            <i class="fa-solid fa-headset"></i>
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

            </div>
          </div>

          <div class="axiom-contact-info-card axiom-contact-service-card">
            <p class="axiom-contact-section-kicker">Service details</p>
            <h3>Support & shipping</h3>

            <div class="axiom-contact-info-list">
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

              <div class="axiom-contact-info-item">
                <div class="axiom-contact-info-icon">
                  <i class="fa-solid fa-box-open"></i>
                </div>
                <div class="axiom-contact-info-copy">
                  <span>Order support</span>
                  <strong>Include your order number for faster help</strong>
                </div>
              </div>
            </div>
          </div>

          <div class="axiom-contact-help-card axiom-contact-faq-card">
            <p class="axiom-contact-section-kicker">Frequently asked questions</p>
            <h3>Quick answers</h3>

            <div class="axiom-contact-faq-list">

              <details class="axiom-contact-faq-item">
                <summary>
                  <span>Where can I find COAs?</span>
                  <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="axiom-contact-faq-answer">
                  COAs can be viewed on our COA page here:
                  <a href="https://axiomresearch.shop/coa-page" target="_blank" rel="noopener">View COAs</a>.
                  If you need help finding a specific COA, contact support with the product name.
                </div>
              </details>

              <details class="axiom-contact-faq-item">
                <summary>
                  <span>How do I track my order?</span>
                  <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="axiom-contact-faq-answer">
                  Tracking details are sent after your order is fulfilled. If you need a tracking update, contact support with your order number.
                </div>
              </details>

              <details class="axiom-contact-faq-item">
                <summary>
                  <span>How long does shipping take?</span>
                  <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="axiom-contact-faq-answer">
                  Most orders ship through USPS with tracking. Delivery speed depends on the shipping service selected and USPS transit times.
                </div>
              </details>

              <details class="axiom-contact-faq-item">
                <summary>
                  <span>What payment methods do you accept?</span>
                  <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="axiom-contact-faq-answer">
                  We currently accept Venmo, Zelle, same-day bank payment, Cash App, Bitcoin, and credit/debit card payments.
                </div>
              </details>

              <details class="axiom-contact-faq-item">
                <summary>
                  <span>Do you offer bulk orders?</span>
                  <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="axiom-contact-faq-answer">
                  Yes, bulk orders may be available depending on the product and quantity requested. Contact support with the products and quantities you are interested in.
                </div>
              </details>

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
