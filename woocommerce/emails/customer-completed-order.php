<?php
defined('ABSPATH') || exit;

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p style="margin:0 0 18px;">Hi <?php echo esc_html($order->get_billing_first_name() ?: 'there'); ?>,</p>

<p style="margin:0 0 22px;">
    Your order <strong style="color:#ffffff;">#<?php echo esc_html($order->get_order_number()); ?></strong>
    has been completed.
</p>

<div style="margin:24px 0;padding:20px;background:#172033;border:1px solid #24385f;border-radius:18px;text-align:center;">
    <p style="margin:0;color:#ffffff;font-size:18px;font-weight:900;">Thank you for ordering from Axiom</p>
    <p style="margin:8px 0 0;color:#cbd5e1;">If tracking is available, it will be shown below or sent separately.</p>
</div>

<?php
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);
?>

<?php do_action('woocommerce_email_footer', $email); ?>
