<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * BUILD CART ITEMS HTML
 */
function axiom_build_cart_items_html($cart_items) {
    if (empty($cart_items)) return '';

    $rows = '';

    foreach ($cart_items as $item) {
        $product = wc_get_product($item['product_id']);
        if (!$product) continue;

        $name  = $product->get_name();
        $price = wc_price($product->get_price());
        $qty   = intval($item['quantity']);
        $img   = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');

        $rows .= '
        <tr>
            <td style="padding:10px;">
                <img src="'.esc_url($img).'" width="60" style="border-radius:8px;">
            </td>
            <td style="padding:10px;">
                <strong>'.$name.'</strong><br>
                Qty: '.$qty.'
            </td>
            <td style="padding:10px;text-align:right;">
                '.$price.'
            </td>
        </tr>';
    }

    return '<table width="100%" style="border-collapse:collapse;">'.$rows.'</table>';
}

/**
 * MAIN EMAIL TEMPLATE WITH VARIATIONS
 */
function axiom_abandoned_cart_email_template($email, $cart_items, $stage = 1) {

    $cart_html = axiom_build_cart_items_html($cart_items);
    $checkout_url = wc_get_checkout_url();

    // 🔥 RANDOM VARIATIONS
    $variation = rand(1, 3);

    $data = array(

        // =====================
        // STAGE 1 (15 MIN)
        // =====================
        1 => array(
            1 => array(
                'subject' => "You left something behind…",
                'headline' => "You’re almost done"
            ),
            2 => array(
                'subject' => "Your cart is still active",
                'headline' => "Your items are waiting"
            ),
            3 => array(
                'subject' => "Quick reminder about your cart",
                'headline' => "Finish your order"
            ),
        ),

        // =====================
        // STAGE 2 (2 HOURS)
        // =====================
        2 => array(
            1 => array(
                'subject' => "Your items are in high demand",
                'headline' => "Don’t miss out"
            ),
            2 => array(
                'subject' => "Still thinking it over?",
                'headline' => "Your cart is still reserved"
            ),
            3 => array(
                'subject' => "Limited availability",
                'headline' => "These may sell out soon"
            ),
        ),

        // =====================
        // STAGE 3 (24 HOURS)
        // =====================
        3 => array(
            1 => array(
                'subject' => "Final reminder — your cart is expiring",
                'headline' => "Last chance"
            ),
            2 => array(
                'subject' => "Your cart won’t stay forever",
                'headline' => "Complete your order now"
            ),
            3 => array(
                'subject' => "We can’t hold this much longer",
                'headline' => "Your cart is about to clear"
            ),
        ),
    );

    $subject  = $data[$stage][$variation]['subject'];
    $headline = $data[$stage][$variation]['headline'];

    $message = '
    <div style="font-family:Inter,Arial;background:#f5f7fb;padding:20px;">
        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:12px;padding:24px;">
            
            <h2 style="margin-bottom:10px;">'.$headline.'</h2>

            <p style="color:#555;">
                You added items to your cart but didn’t complete checkout.
            </p>

            '.$cart_html.'

            <div style="text-align:center;margin:30px 0;">
                <a href="'.$checkout_url.'" 
                   style="background:#3B6FE0;color:#fff;padding:14px 28px;
                   text-decoration:none;border-radius:8px;font-weight:600;">
                   Complete Your Order
                </a>
            </div>

            <p style="font-size:13px;color:#777;">
                High demand products may sell out quickly.
            </p>

            <hr style="margin:25px 0;">

            <p style="font-size:12px;color:#aaa;text-align:center;">
                Axiom Peptides<br>
                Research Use Only • Not for human consumption
            </p>

        </div>
    </div>';

    return array(
        'subject' => $subject,
        'message' => $message
    );
}
