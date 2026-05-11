<?php
/**
 * Template Name: Axiom Shipping Policy
 * Template Post Type: page
 */

defined('ABSPATH') || exit;

get_header();

$site_name = 'Axiom Research';
$email     = 'support@axiomresearch.shop';
$last_date = 'May 11, 2026';
?>

<main class="axiom-legal-page">
  <section class="axiom-legal-hero">
    <div class="axiom-legal-container">
      <p class="axiom-legal-kicker"><?php echo esc_html($site_name); ?></p>
      <h1>Shipping Policy</h1>
      <p class="axiom-legal-subtitle">
        Fulfillment, shipping estimates, tracking, delivery responsibility, and carrier-delay policy.
      </p>
    </div>
  </section>

  <section class="axiom-legal-content">
    <div class="axiom-legal-container">
      <article class="axiom-legal-box">

        <p class="axiom-legal-updated">
          <strong>Last Updated:</strong> <?php echo esc_html($last_date); ?>
        </p>

        <div class="axiom-legal-alert">
          <strong>Shipping Summary:</strong> Orders are fulfilled from the United States. Shipping times are estimates
          only and may vary based on order volume, payment review, carrier delays, weather, address issues, or other
          events outside our control.
        </div>

        <h2>1. Order Processing</h2>
        <p>
          Orders are processed during normal business operations. Processing times may vary based on order volume,
          inventory availability, payment review, fraud screening, compliance review, holidays, weekends, carrier pickup
          schedules, and other operational factors.
        </p>

        <h2>2. Shipping Times Are Estimates</h2>
        <p>
          Shipping and delivery timeframes shown at checkout or in tracking updates are estimates only. We do not
          guarantee delivery dates or carrier transit times.
        </p>

        <h2>3. Tracking Information</h2>
        <p>
          When tracking is available, tracking information may be sent by email or displayed in your account/order
          details. Tracking updates are controlled by the carrier and may take time to appear after a label is created.
        </p>

        <h2>4. Shipping Address Accuracy</h2>
        <p>
          Customers are responsible for providing a complete, accurate, and deliverable shipping address at checkout.
          We are not responsible for lost, delayed, returned, or misdelivered packages caused by incorrect or incomplete
          customer-provided addresses.
        </p>

        <h2>5. Carrier Delays</h2>
        <p>
          We are not responsible for delays caused by USPS, UPS, FedEx, DHL, local carriers, weather, natural disasters,
          high shipping volume, incorrect routing, customs, security review, failed delivery attempts, or other events
          outside our control.
        </p>

        <h2>6. Lost, Stolen, or Marked-Delivered Packages</h2>
        <p>
          If tracking shows a package was delivered, but you cannot locate it, check with household members, neighbors,
          building management, mail rooms, parcel lockers, and the carrier. Packages marked delivered by the carrier may
          not be eligible for refund or replacement.
        </p>

        <h2>7. Returned Packages</h2>
        <p>
          Packages may be returned due to incorrect addresses, failed delivery attempts, refusal, unpaid carrier fees,
          access issues, or other delivery problems. If a package is returned to us, we may offer reshipment, store
          credit, refund, or another resolution at our discretion. Additional shipping fees may apply.
        </p>

        <h2>8. Restricted Locations</h2>
        <p>
          We reserve the right to refuse, cancel, or refund orders to locations where fulfillment, shipping, delivery,
          possession, or use may create legal, regulatory, carrier, compliance, fraud, or business risk.
        </p>

        <h2>9. Research Use Only Shipping Notice</h2>
        <p>
          Products are shipped strictly for lawful laboratory research purposes only. Delivery of a product does not
          authorize human use, veterinary use, medical use, diagnostic use, therapeutic use, food use, cosmetic use,
          household use, or any other prohibited use.
        </p>

        <h2>10. Damaged Shipments</h2>
        <p>
          If an item arrives damaged, contact us within 48 hours of delivery with your order number, package photos,
          product photos, and a clear description of the issue. We may require documentation to review the claim.
        </p>

        <h2>11. Contact</h2>
        <p>
          For shipping questions, contact:
          <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
        </p>

      </article>
    </div>
  </section>
</main>

<?php get_footer(); ?>
