<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sends manual payment instructions immediately after checkout
 * for Venmo, Zelle, Cash App Bitcoin, and Bitcoin orders.
 */

add_action('woocommerce_checkout_order_processed', 'axiom_send_manual_payment_email_after_checkout', 30, 3);
add_action('woocommerce_thankyou', 'axiom_send_manual_payment_email_fallback', 20);

function axiom_send_manual_payment_email_fallback($order_id) {
    axiom_send_manual_payment_email_after_checkout($order_id);
}

function axiom_send_manual_payment_email_after_checkout($order_id, $posted_data = array(), $order = null) {
    if (!$order instanceof WC_Order) {
        $order = wc_get_order($order_id);
    }

    if (!$order instanceof WC_Order) {
        return;
    }

    if ($order->get_meta('_axiom_manual_payment_email_sent') === 'yes') {
        return;
    }

    if ($order->is_paid() || in_array($order->get_status(), array('processing', 'completed'), true)) {
        return;
    }

    $payment_method = strtolower($order->get_payment_method());
    $payment_title  = strtolower($order->get_payment_method_title());
    $method_type    = axiom_detect_manual_payment_method($payment_method, $payment_title);

    if (!in_array($method_type, array('venmo', 'zelle', 'cashapp', 'bitcoin'), true)) {
        return;
    }

    $to = $order->get_billing_email();

    if (empty($to)) {
        return;
    }

    $instructions = axiom_get_manual_payment_instructions($method_type);
    $subject      = $instructions['subject'];

    $message = axiom_build_manual_payment_email_html($order, $instructions);

    $headers = array('Content-Type: text/html; charset=UTF-8');

    $sent = wp_mail($to, $subject, $message, $headers);

    if ($sent) {
        $order->update_meta_data('_axiom_manual_payment_email_sent', 'yes');
        $order->add_order_note('Manual payment instructions email sent to customer.');
        $order->save();
    }
}

function axiom_detect_manual_payment_method($payment_method, $payment_title) {
    $haystack = $payment_method . ' ' . $payment_title;

    if (strpos($haystack, 'venmo') !== false) {
        return 'venmo';
    }

    if (strpos($haystack, 'zelle') !== false) {
        return 'zelle';
    }

    if (
        strpos($haystack, 'cash') !== false ||
        strpos($haystack, 'cashapp') !== false ||
        strpos($haystack, 'cash app') !== false
    ) {
        return 'cashapp';
    }

    if (
        strpos($haystack, 'crypto') !== false ||
        strpos($haystack, 'bitcoin') !== false ||
        strpos($haystack, 'btc') !== false
    ) {
        return 'bitcoin';
    }

    return 'manual';
}

function axiom_get_manual_payment_instructions($method_type) {
    $methods = array(
        'venmo' => array(
            'subject' => 'Complete your Axiom payment with Venmo',
            'title'   => 'Complete Payment with Venmo',
            'label'   => 'VENMO',
            'handle'  => '@thomas-harris-axiom',
            'link'    => 'https://venmo.com/code?user_id=4564578725790758651&created=1777149962.740827&printed=1',
            'button'  => 'Open Venmo',
            'note'    => 'Send the exact order total through Venmo. Please include your order number in the payment note.',
            'icon'    => '💸',
        ),

        'zelle' => array(
            'subject' => 'Complete your Axiom payment with Zelle',
            'title'   => 'Complete Payment with Zelle',
            'label'   => 'ZELLE',
            'handle'  => 'jaxferone@gmail.com',
            'link'    => '',
            'button'  => '',
            'note'    => 'Send the exact order total through Zelle. Please include your order number in the memo.',
            'icon'    => '🏦',
        ),

        'cashapp' => array(
            'subject' => 'Complete your Axiom payment with Cash App Bitcoin',
            'title'   => 'Complete Payment with Cash App Bitcoin',
            'label'   => 'CASH APP BITCOIN',
            'handle'  => 'bc1q5gwgacsd796tntenudj6janfnkt4ygzdzl4mn8',
            'link'    => '',
            'button'  => '',
            'note'    => 'Send the exact order total in Bitcoin through Cash App. Please double-check the address before sending.',
            'icon'    => '₿',
        ),

        'bitcoin' => array(
            'subject' => 'Complete your Axiom payment with Bitcoin',
            'title'   => 'Complete Payment with Bitcoin',
            'label'   => 'BITCOIN',
            'handle'  => 'bc1q5gwgacsd796tntenudj6janfnkt4ygzdzl4mn8',
            'link'    => '',
            'button'  => '',
            'note'    => 'Send the exact order total in Bitcoin. Please double-check the address before sending.',
            'icon'    => '₿',
        ),
    );

    return isset($methods[$method_type]) ? $methods[$method_type] : $methods['venmo'];
}

function axiom_build_manual_payment_email_html($order, $data) {
    $first_name   = $order->get_billing_first_name() ?: 'there';
    $order_number = $order->get_order_number();
    $order_total  = $order->get_formatted_order_total();
    $logo_url     = get_template_directory_uri() . '/assets/images/axiom-menu-logo.PNG';

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:28px 12px;">
            <tr>
                <td align="center">
                    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#111827;border-radius:24px;overflow:hidden;border:1px solid #1e2b44;">
                        <tr>
                            <td align="center" style="padding:32px 28px 18px;">
                                <div style="background:#ffffff;border-radius:999px;padding:12px 22px;display:inline-block;">
                                    <img src="<?php echo esc_url($logo_url); ?>" alt="Axiom Peptides" style="width:190px;max-width:190px;height:auto;display:block;">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td align="center" style="padding:8px 34px 18px;">
                                <p style="margin:0 0 12px;color:#9db7ff;font-size:13px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;">
                                    Payment Required
                                </p>

                                <h1 style="margin:0;color:#ffffff;font-size:34px;line-height:1.15;font-weight:900;text-align:center;">
                                    <?php echo esc_html($data['title']); ?>
                                </h1>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:0 34px 26px;color:#cbd5e1;font-size:16px;line-height:1.7;">
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
                                                <?php echo esc_html($data['button']); ?>
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
                                ob_start();
                                do_action('woocommerce_email_order_details', $order, false, false, null);
                                echo ob_get_clean();
                                ?>

                                <div style="border-top:1px solid #26344f;margin-top:28px;padding-top:22px;text-align:center;">
                                    <p style="margin:0 0 10px;color:#9db7ff;font-size:13px;font-weight:800;line-height:1.6;">
                                        Lab-tested products • USA fulfilled • Research use only
                                    </p>

                                    <p style="margin:0;color:#94a3b8;font-size:12px;line-height:1.7;">
                                        Axiom Peptides<br>
                                        support@axiomresearch.shop<br>
                                        axiomresearch.shop
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    <?php

    return ob_get_clean();
}
