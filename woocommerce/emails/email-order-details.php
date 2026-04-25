<?php
defined('ABSPATH') || exit;

if (!$order instanceof WC_Order) {
    return;
}

$order_number = $order->get_order_number();
$order_date   = wc_format_datetime($order->get_date_created());
?>

<div style="margin:30px 0;padding:0;background:#172033;border:1px solid #263a5f;border-radius:24px;overflow:hidden;">
    <div style="padding:24px 22px;text-align:center;background:#111827;border-bottom:1px solid #263a5f;">
        <p style="margin:0 0 8px;color:#9db7ff;font-size:12px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;">
            Order Summary
        </p>
        <h2 style="margin:0;color:#ffffff;font-size:28px;line-height:1.2;font-weight:900;">
            What’s in your order
        </h2>
    </div>

    <div style="padding:20px;">
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">
            <tr>
                <td style="padding:14px;background:#0f172a;border:1px solid #263a5f;border-radius:16px;color:#cbd5e1;font-size:14px;">
                    <strong style="display:block;margin-bottom:5px;color:#9db7ff;font-size:11px;text-transform:uppercase;letter-spacing:.1em;">
                        Order Number
                    </strong>
                    <span style="color:#ffffff;font-size:17px;font-weight:900;">#<?php echo esc_html($order_number); ?></span>
                </td>

                <td width="12"></td>

                <td style="padding:14px;background:#0f172a;border:1px solid #263a5f;border-radius:16px;color:#cbd5e1;font-size:14px;">
                    <strong style="display:block;margin-bottom:5px;color:#9db7ff;font-size:11px;text-transform:uppercase;letter-spacing:.1em;">
                        Order Date
                    </strong>
                    <span style="color:#ffffff;font-size:15px;font-weight:900;"><?php echo esc_html($order_date); ?></span>
                </td>
            </tr>
        </table>

        <?php
        do_action('woocommerce_email_order_items', $order, array(
            'show_sku'      => false,
            'show_image'    => true,
            'image_size'    => array(96, 96),
            'plain_text'    => false,
            'sent_to_admin' => false,
        ));
        ?>

        <div style="margin-top:18px;padding:18px;background:#0f172a;border:1px solid #263a5f;border-radius:18px;">
            <p style="margin:0 0 12px;color:#ffffff;font-size:18px;font-weight:900;text-align:center;">
                Order Total
            </p>

            <?php foreach ($order->get_order_item_totals() as $total) : ?>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:1px solid #24385f;">
                    <tr>
                        <td style="padding:11px 0;color:#94a3b8;font-size:14px;font-weight:700;">
                            <?php echo wp_kses_post($total['label']); ?>
                        </td>
                        <td align="right" style="padding:11px 0;color:#ffffff;font-size:15px;font-weight:900;">
                            <?php echo wp_kses_post($total['value']); ?>
                        </td>
                    </tr>
                </table>
            <?php endforeach; ?>
        </div>
    </div>
</div>
