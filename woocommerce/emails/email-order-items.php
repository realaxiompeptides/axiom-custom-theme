<?php
defined('ABSPATH') || exit;

foreach ($items as $item_id => $item) :
    $product = $item->get_product();

    if (!$product) {
        continue;
    }

    $product_name = $item->get_name();
    $quantity     = $item->get_quantity();
    $line_total   = $order->get_formatted_line_subtotal($item);
    $product_url  = get_permalink($product->get_id());

    $image_id  = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : wc_placeholder_img_src();

    $meta_html = wc_display_item_meta($item, array(
        'before'    => '<p style="margin:6px 0 0;color:#94a3b8;font-size:12px;line-height:1.4;">',
        'after'     => '</p>',
        'separator' => '<br>',
        'echo'      => false,
    ));
    ?>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 14px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #dbeafe;">
        <tr>
            <td width="104" style="padding:14px;">
                <a href="<?php echo esc_url($product_url); ?>" target="_blank" rel="noopener">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product_name); ?>" width="86" height="86" style="display:block;width:86px;height:86px;object-fit:cover;border-radius:14px;border:1px solid #e5e7eb;background:#ffffff;">
                </a>
            </td>

            <td style="padding:14px 10px 14px 0;">
                <p style="margin:0 0 6px;color:#07122f;font-size:16px;line-height:1.35;font-weight:900;">
                    <?php echo esc_html($product_name); ?>
                </p>

                <p style="margin:0;color:#64748b;font-size:13px;line-height:1.45;">
                    Quantity: <strong><?php echo esc_html($quantity); ?></strong>
                </p>

                <?php echo wp_kses_post($meta_html); ?>
            </td>

            <td width="90" align="right" style="padding:14px;color:#07122f;font-size:15px;font-weight:900;">
                <?php echo wp_kses_post($line_total); ?>
            </td>
        </tr>
    </table>

<?php endforeach; ?>
