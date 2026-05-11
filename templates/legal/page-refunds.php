<?php
/**
 * Template Name: Axiom Refund and Return Policy
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
      <h1>Refund and Return Policy</h1>
      <p class="axiom-legal-subtitle">
        Order cancellation, refund, return, damaged shipment, and payment dispute policy.
      </p>
    </div>
  </section>

  <section class="axiom-legal-content">
    <div class="axiom-legal-container">
      <article class="axiom-legal-box">

        <p class="axiom-legal-updated">
          <strong>Last Updated:</strong> <?php echo esc_html($last_date); ?>
        </p>

        <div class="axiom-legal-warning">
          <strong>Research Product Notice:</strong> Due to the nature of research-use-only products, returns may be
          restricted after an order has been processed, packed, shipped, opened, handled, or delivered.
        </div>

        <h2>1. General Policy</h2>
        <p>
          This Refund and Return Policy applies to purchases made from <?php echo esc_html($site_name); ?>.
          By placing an order, you agree to this policy and understand that products are sold strictly for lawful
          laboratory research purposes only.
        </p>

        <h2>2. Order Cancellations</h2>
        <p>
          Cancellation requests must be submitted as soon as possible. We may cancel and refund an order if the request
          is received before the order has been processed, packed, shipped, or transferred to fulfillment.
        </p>

        <p>
          Once an order is processed, packed, labeled, shipped, or transferred to a carrier, cancellation may no longer
          be available.
        </p>

        <h2>3. Returns</h2>
        <p>
          Because our products are research-use-only materials, we may not accept returns after products have been
          shipped, delivered, opened, handled, stored outside our control, or otherwise removed from our custody.
        </p>

        <p>
          Any approved return must be authorized by <?php echo esc_html($site_name); ?> in writing before the item is
          sent back. Unauthorized returns may be refused, discarded, or returned to sender.
        </p>

        <h2>4. Damaged, Missing, or Incorrect Items</h2>
        <p>
          If your order arrives damaged, missing an item, or contains an incorrect item, contact us within 48 hours of
          delivery. Include your order number, photos of the package, photos of the product, and a clear description of
          the issue.
        </p>

        <p>
          We may offer a replacement, store credit, refund, or other resolution at our discretion after reviewing the
          claim.
        </p>

        <h2>5. Shipping Issues</h2>
        <p>
          We are not responsible for carrier delays, weather delays, incorrect addresses entered by the customer,
          refused packages, abandoned packages, failed delivery attempts, theft after delivery, or delivery issues
          outside our control.
        </p>

        <h2>6. Address Accuracy</h2>
        <p>
          Customers are responsible for entering a complete and accurate shipping address. If an order is shipped to an
          incorrect address provided by the customer, we may be unable to refund or replace the order.
        </p>

        <h2>7. Non-Refundable Items and Fees</h2>
        <p>The following may be non-refundable:</p>

        <ul>
          <li>Orders that have already shipped;</li>
          <li>Opened, handled, or used products;</li>
          <li>Products returned without authorization;</li>
          <li>Shipping fees, carrier fees, payment processing fees, and handling fees;</li>
          <li>Orders delayed by carriers or events outside our control;</li>
          <li>Orders refused due to customer ineligibility, misuse concerns, or compliance concerns.</li>
        </ul>

        <h2>8. Payment Disputes and Chargebacks</h2>
        <p>
          If you have an issue with an order, contact us before opening a payment dispute or chargeback. Fraudulent,
          abusive, or premature disputes may result in account restriction, refusal of future orders, or submission of
          supporting records to the payment provider.
        </p>

        <h2>9. Refund Timing</h2>
        <p>
          Approved refunds are issued to the original payment method when possible. Processing times vary depending on
          the payment provider, bank, card network, and payment method.
        </p>

        <h2>10. Contact</h2>
        <p>
          For refund or return questions, contact:
          <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
        </p>

      </article>
    </div>
  </section>
</main>

<?php get_footer(); ?>
