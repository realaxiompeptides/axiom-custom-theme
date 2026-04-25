<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Axiom - Premium Shipping Tracking Email Automation
 */

add_action('woocommerce_email_after_order_table', 'axiom_add_tracking_to_customer_emails', 10, 4);
add_action('woocommerce_update_order', 'axiom_maybe_send_tracking_email_when_added', 20, 1);
add_action('woocommerce_order_status_completed', 'axiom_maybe_send_tracking_email_when_added', 20, 1);

function axiom_add_tracking_to_customer_emails($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin || !$order instanceof WC_Order) {
        return;
    }

    $tracking = axiom_get_order_tracking_details($order);

    if (empty($tracking['number'])) {
        return;
    }

    if ($plain_text) {
        echo "\nUSPS Tracking Number: " . esc_html($tracking['number']) . "\n";
        echo "Track your package: " . esc_url($tracking['url']) . "\n";
        return;
    }
}

function axiom_maybe_send_tracking_email_when_added($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    if ($order->get_meta('_axiom_tracking_email_sent') === 'yes') {
        return;
    }

    $tracking = axiom_get_order_tracking_details($order);

    if (empty($tracking['number'])) {
        return;
    }

    axiom_send_tracking_email($order, $tracking);

    $order->update_meta_data('_axiom_tracking_email_sent', 'yes');
    $order->update_meta_data('_axiom_tracking_email_sent_at', current_time('mysql'));
    $order->save();
}

function axiom_get_order_tracking_details($order) {
    $tracking_number = '';
    $tracking_url    = '';
    $carrier         = 'USPS';

    $shipment_items = $order->get_meta('_wc_shipment_tracking_items');

    if (!empty($shipment_items) && is_array($shipment_items)) {
        $first_item = reset($shipment_items);

        if (!empty($first_item['tracking_number'])) {
            $tracking_number = trim($first_item['tracking_number']);
        }

        if (!empty($first_item['tracking_provider'])) {
            $carrier = trim($first_item['tracking_provider']);
        }

        if (!empty($first_item['custom_tracking_link'])) {
            $tracking_url = trim($first_item['custom_tracking_link']);
        }
    }

    $tracking_keys = array(
        '_tracking_number',
        'tracking_number',
        '_usps_tracking_number',
        'usps_tracking_number',
        '_wcshipping_tracking_number',
        '_shipping_tracking_number',
        '_shipment_tracking_number',
        '_wc_shipment_tracking_number',
    );

    foreach ($tracking_keys as $key) {
        if (!empty($tracking_number)) {
            break;
        }

        $value = $order->get_meta($key);

        if (!empty($value) && is_string($value)) {
            $tracking_number = trim($value);
        }
    }

    if (empty($tracking_url) && !empty($tracking_number)) {
        $tracking_url = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . rawurlencode($tracking_number);
    }

    return array(
        'number'  => $tracking_number,
        'url'     => $tracking_url,
        'carrier' => $carrier ?: 'USPS',
    );
}

function axiom_send_tracking_email($order, $tracking) {
    $to = $order->get_billing_email();

    if (empty($to)) {
        return;
    }

    $subject = 'Your Axiom order has shipped';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
    );

    $message = axiom_get_full_tracking_email_html($order, $tracking);

    WC()->mailer()->send($to, $subject, $message, $headers);
}

function axiom_get_full_tracking_email_html($order, $tracking) {
    $first_name = $order->get_billing_first_name();
    $order_num  = $order->get_order_number();

    $logo_url = get_template_directory_uri() . '/assets/images/axiom-menu-logo.PNG';

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:28px 12px;">
            <tr>
                <td align="center">
                    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#111827;border-radius:24px;overflow:hidden;border:1px solid #1e2b44;">
                        
                        <tr>
                            <td align="center" style="padding:34px 32px 26px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="border-top:2px solid #ffffff;width:28%;font-size:1px;line-height:1px;">&nbsp;</td>
                                        <td align="center" style="width:44%;padding:0 18px;">
                                            <div style="background:#ffffff;border-radius:999px;padding:12px 18px;display:inline-block;">
                                                <img src="<?php echo esc_url($logo_url); ?>" alt="Axiom Peptides" style="width:150px;max-width:150px;height:auto;display:block;">
                                            </div>
                                        </td>
                                        <td style="border-top:2px solid #ffffff;width:28%;font-size:1px;line-height:1px;">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td align="center" style="padding:8px 34px 10px;">
                                <p style="margin:0 0 14px;color:#9db7ff;font-size:13px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;">
                                    USPS Tracking Added
                                </p>

                                <h1 style="margin:0;color:#ffffff;font-size:38px;line-height:1.12;font-weight:900;text-align:center;">
                                    Your order is on the way
                                </h1>

                                <p style="margin:22px auto 0;color:#cbd5e1;font-size:17px;line-height:1.65;text-align:center;max-width:470px;">
                                    Hi <?php echo esc_html($first_name ?: 'there'); ?>, your Axiom order
                                    <strong style="color:#ffffff;">#<?php echo esc_html($order_num); ?></strong> has shipped.
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:28px 34px;">
                                <?php echo axiom_get_tracking_card_html($tracking); ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:0 34px 26px;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="background:#172033;border:1px solid #24385f;border-radius:20px;">
                                    <tr>
                                        <td align="center" style="padding:22px 20px;text-align:center;">
                                            <p style="margin:0 0 10px;color:#ffffff;font-size:18px;font-weight:900;text-align:center;">
                                                Shipping Note
                                            </p>
                                            <p style="margin:0 auto;color:#cbd5e1;font-size:15px;line-height:1.7;text-align:center;max-width:460px;">
                                                USPS may take a little time to show the first scan after the label is created.
                                                Tracking usually updates once the package is scanned into the USPS network.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:0 34px 30px;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fbff;border:1px solid #dbe8ff;border-radius:20px;">
                                    <tr>
                                        <td style="padding:22px;">
                                            <h2 style="margin:0 0 16px;color:#07122f;font-size:21px;font-weight:900;text-align:center;">
                                                What’s in your order
                                            </h2>

                                            <?php echo axiom_get_order_items_html($order); ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td align="center" style="padding:0 34px 34px;">
                                <p style="margin:0 0 12px;color:#9db7ff;font-size:13px;font-weight:800;line-height:1.6;text-align:center;">
                                    Lab-tested products • USA fulfilled • Research use only
                                </p>
                                <p style="margin:0;color:#94a3b8;font-size:12px;line-height:1.7;text-align:center;">
                                    Axiom Peptides<br>
                                    support@axiomresearch.shop<br>
                                    axiomresearch.shop
                                </p>
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

function axiom_get_tracking_card_html($tracking) {
    ob_start();
    ?>
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#eef4ff;border:1px solid #bdd2ff;border-radius:22px;">
        <tr>
            <td align="center" style="padding:30px 22px;text-align:center;">
                <p style="margin:0 0 12px;color:#315bb8;font-size:13px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;text-align:center;">
                    USPS Tracking Number
                </p>

                <p style="margin:0 0 24px;color:#07122f;font-size:30px;line-height:1.25;font-weight:900;word-break:break-word;text-align:center;">
                    <?php echo esc_html($tracking['number']); ?>
                </p>

                <a href="<?php echo esc_url($tracking['url']); ?>" target="_blank" rel="noopener" style="display:inline-block;background:#3B6FE0;color:#ffffff;text-decoration:none;padding:16px 34px;border-radius:999px;font-size:15px;font-weight:900;">
                    Track My Package
                </a>
            </td>
        </tr>
    </table>
    <?php
    return ob_get_clean();
}

function axiom_get_order_items_html($order) {
    ob_start();

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();

        if (!$product) {
            continue;
        }

        $product_name = $item->get_name();
        $quantity     = $item->get_quantity();
        $image_id     = $product->get_image_id();
        $image_url    = $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : wc_placeholder_img_src();
        $product_url  = get_permalink($product->get_id());
        ?>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 14px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;">
            <tr>
                <td width="98" style="padding:14px;">
                    <a href="<?php echo esc_url($product_url); ?>" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product_name); ?>" width="78" height="78" style="display:block;width:78px;height:78px;object-fit:cover;border-radius:14px;border:1px solid #e5e7eb;">
                    </a>
                </td>
                <td style="padding:14px 14px 14px 0;">
                    <p style="margin:0 0 7px;color:#07122f;font-size:16px;line-height:1.35;font-weight:900;">
                        <?php echo esc_html($product_name); ?>
                    </p>
                    <p style="margin:0;color:#64748b;font-size:14px;">
                        Quantity: <?php echo esc_html($quantity); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    return ob_get_clean();
}
