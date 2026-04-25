<?php
defined('ABSPATH') || exit;

$first_name     = $order->get_billing_first_name() ?: 'there';
$order_number   = $order->get_order_number();
$order_total    = $order->get_formatted_order_total();
$payment_method = strtolower($order->get_payment_method());
$payment_title  = strtolower($order->get_payment_method_title());

function axiom_payment_method_type($payment_method, $payment_title) {
    $haystack = $payment_method . ' ' . $payment_title;

    if (strpos($haystack, 'venmo') !== false) {
        return 'venmo';
    }

    if (strpos($haystack, 'zelle') !== false) {
        return 'zelle';
    }

    if (strpos($haystack, 'cash') !== false || strpos($haystack, 'cashapp') !== false || strpos($haystack, 'cash app') !== false) {
        return 'cashapp';
    }

    if (strpos($haystack, 'crypto') !== false || strpos($haystack, 'bitcoin') !== false || strpos($haystack, 'btc') !== false) {
        return 'bitcoin';
    }

    return 'manual';
}

$method_type = axiom_payment_method_type($payment_method, $payment_title);

$instructions = array(
    'venmo' => array(
        'title'  => 'Complete Payment with Venmo',
        'label'  => 'VENMO',
        'handle' => '@thomas-harris-axiom',
        'link'   => 'https://venmo.com/code?user_id=4564578725790758651&created=1777149962.740827&printed=1',
        'note'   => 'Send the exact order total through Venmo. Please include your order number in the payment note.',
        'icon'   => '💸',
    ),

    'zelle' => array(
        'title'  => 'Complete Payment with Zelle',
        'label'  => 'ZELLE',
        'handle' => 'jaxferone@gmail.com',
        'link'   => '',
        'note'   => 'Send the exact order total through Zelle. Please include your order number in the memo.',
        'icon'   => '🏦',
    ),

    'cashapp' => array(
        'title'  => 'Complete Payment with Cash App Bitcoin',
        'label'  => 'CASH APP BITCOIN',
        'handle' => 'bc1q5gwgacsd796tntenudj6janfnkt4ygzdzl4mn8',
        'link'   => '',
        'note'   => 'Send the exact order total in Bitcoin through Cash App. Please double-check the address before sending.',
        'icon'   => '₿',
    ),

    'bitcoin' => array(
        'title'  => 'Complete Payment with Bitcoin',
        'label'  => 'BITCOIN',
        'handle' => 'bc1q5gwgacsd796tntenudj6janfnkt4ygzdzl4mn8',
        'link'   => '',
        'note'   => 'Send the exact order total in Bitcoin. Please double-check the address before sending.',
        'icon'   => '₿',
    ),

    'manual' => array(
        'title'  => 'Complete Your Payment',
        'label'  => 'PAYMENT OPTIONS',
        'handle' => 'Zelle: jaxferone@gmail.com | Venmo: @thomas-harris-axiom | Bitcoin: bc1q5gwgacsd796tntenudj6janfnkt4ygzdzl4mn8',
        'link'   => 'https://venmo.com/code?user_id=4564578725790758651&created=1777149962.740827&printed=1',
        'note'   => 'Send the exact order total using the payment method selected at checkout. Include your order number so we can match your payment.',
        'icon'   => '✅',
    ),
);

$data = $instructions[$method_type];

do_action('woocommerce_email_header', $data['title'], $email);
?>

<p style="margin:0 0 18px;">Hi <?php echo esc_html($first_name); ?>,</p>

<p style="margin:0 0 20px;">
    Your Axiom order <strong style="color:#ffffff;">#<?php echo esc_html($order_number); ?></strong>
    has been received and is waiting for payment.
</p>

<div style="margin:26px 0;padding:24px;background:#172033;border:1px solid #24385f;border-radius:22px;text-align:center;">
    <div style="width:58px;height:58px;margin:0 auto 14px;border-radius:50%;background:#eef4ff;color:#3B6FE0;display:inline-flex;align-items:center;justify-content:center;font-size:28px;">
        <?php echo esc_html($data['icon']); ?>
    </div>

    <p style="margin:0 0 8px;color:#9db7ff;font-size:13px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;">
        <?php echo esc_html($data['label']); ?>
    </p>

    <h2 style="margin:0 0 14px;color:#ffffff;font-size:26px;line-height:1.2;font-weight:900;">
        <?php echo esc_html($data['title']); ?>
    </h2>

    <p style="margin:0 0 18px;color:#cbd5e1;font-size:15px;line-height:1.7;">
        <?php echo esc_html($data['note']); ?>
    </p>

    <div style="margin:20px 0;padding:18px;background:#0f172a;border:1px solid #263a5f;border-radius:18px;">
        <p style="margin:0 0 7px;color:#94a3b8;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.12em;">
            Send Payment To
        </p>

        <p style="margin:0;color:#ffffff;font-size:18px;font-weight:900;word-break:break-word;">
            <?php echo esc_html($data['handle']); ?>
        </p>

        <?php if (!empty($data['link'])) : ?>
            <a href="<?php echo esc_url($data['link']); ?>" target="_blank" rel="noopener" style="display:inline-block;margin-top:16px;background:#3B6FE0;color:#ffffff;text-decoration:none;padding:14px 24px;border-radius:999px;font-weight:900;">
                Open Venmo
            </a>
        <?php endif; ?>
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;">
        <tr>
            <td style="padding:14px;background:#0f172a;border:1px solid #263a5f;border-radius:16px;color:#cbd5e1;font-size:14px;text-align:center;">
                <strong style="display:block;color:#9db7ff;font-size:11px;text-transform:uppercase;letter-spacing:.1em;">Order Number</strong>
                <span style="color:#ffffff;font-size:17px;font-weight:900;">#<?php echo esc_html($order_number); ?></span>
            </td>

            <td width="12"></td>

            <td style="padding:14px;background:#0f172a;border:1px solid #263a5f;border-radius:16px;color:#cbd5e1;font-size:14px;text-align:center;">
                <strong style="display:block;color:#9db7ff;font-size:11px;text-transform:uppercase;letter-spacing:.1em;">Amount Due</strong>
                <span style="color:#ffffff;font-size:17px;font-weight:900;"><?php echo wp_kses_post($order_total); ?></span>
            </td>
        </tr>
    </table>
</div>

<div style="margin:24px 0;padding:18px;background:#fff7ed;border:1px solid #fed7aa;border-radius:18px;color:#7c2d12;line-height:1.6;">
    <strong>Important:</strong>
    Please send the exact amount and include order number
    <strong>#<?php echo esc_html($order_number); ?></strong>
    so we can match your payment quickly.
</div>

<?php
do_action('woocommerce_email_order_details', $order, false, false, $email);
do_action('woocommerce_email_order_meta', $order, false, false, $email);
do_action('woocommerce_email_customer_details', $order, false, false, $email);
do_action('woocommerce_email_footer', $email);
?>
