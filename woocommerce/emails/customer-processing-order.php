<?php
defined('ABSPATH') || exit;

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p style="margin:0 0 18px;">Hi <?php echo esc_html($order->get_billing_first_name() ?: 'there'); ?>,</p>

<p style="margin:0 0 22px;">
    We received your order <strong style="color:#ffffff;">#<?php echo esc_html($order->get_order_number()); ?></strong>
    and it is now being prepared.
</p>

<div style="margin:24px 0;padding:20px;background:#172033;border:1px solid #24385f;border-radius:18px;text-align:center;">
    <p style="margin:0;color:#ffffff;font-size:18px;font-weight:900;">Order received</p>
    <p style="margin:8px 0 0;color:#cbd5e1;">You’ll receive tracking automatically once your shipping label is created.</p>
</div>

<?php
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);
?>

<div style="margin:26px 0;padding:20px;background:#eef4ff;border:1px solid #bfd4ff;border-radius:18px;text-align:center;color:#0f172a;">
    <h2 style="margin:0 0 8px;font-size:20px;">Your Free Research Guide</h2>
    <p style="margin:0 0 16px;">Use this guide as a reference for research-use handling and storage basics.</p>
    <a href="https://axiomresearch.shop" style="display:inline-block;background:#3B6FE0;color:#ffffff;text-decoration:none;padding:14px 24px;border-radius:999px;font-weight:900;">
        View Guide
    </a>
</div>

<?php do_action('woocommerce_email_footer', $email); ?>
