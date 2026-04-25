<?php
defined('ABSPATH') || exit;

if (!$order instanceof WC_Order) {
    return;
}

$order_number = $order->get_order_number();
$order_date   = wc_format_datetime($order->get_date_created());
?>

<div style="margin:30px 0;padding:24px;background:#172033;border:1px solid #24385f;border-radius:22px;">
    <h2 style="margin:0 0 18px;color:#ffffff;font-size:26px;line-height:1.2;text-align:center;font-weight:900;">
        Order Summary
    </h2>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        <tr>
            <td style="padding:14px;background:#111827;border:1px solid #24385f;border-radius:14px;color:#cbd5e1;font-size:14px;">
                <strong style="display:block;color:#9db7ff;font-size:12px;text-transform:uppercase;letter-spacing:.08em;">Order Number</strong>
                #<?php echo esc_html($order_number); ?>
            </td>
            <td width="12"></td>
            <td style="padding:14px;background:#111827;border:1px solid #24385f;border-radius:14px;color:#cbd5e1;font-size:14px;">
                <strong style="display:block;color:#9db7ff;font-size:12px;text-transform:uppercase;letter-spacing:.08em;">Order Date</strong>
                <?php echo esc_html($order_date); ?>
            </td>
        </tr>
    </table>

    <?php do_action('woocommerce_email_order_items', $order, array(
        'show_sku'      => false,
        'show_image'    => true,
        'image_size'    => array(90, 90),
        'plain_text'    => false,
        'sent_to_admin' => false,
    )); ?>

    <div style="margin-top:20px;padding:18px;background:#111827;border:1px solid #24385f;border-radius:16px;">
        <?php foreach ($order->get_order_item_totals() as $total) : ?>
            <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:1px solid #24385f;">
                <tr>
                    <td style="padding:10px 0;color:#94a3b8;font-size:14px;">
                        <?php echo wp_kses_post($total['label']); ?>
                    </td>
                    <td align="right" style="padding:10px 0;color:#ffffff;font-size:15px;font-weight:900;">
                        <?php echo wp_kses_post($total['value']); ?>
                    </td>
                </tr>
            </table>
        <?php endforeach; ?>
    </div>
</div>
