<?php
if (!defined('ABSPATH')) exit;

/**
 * Axiom checkout add-ons:
 * - Shipping Protection
 * - Research Starter Pack
 */

function axiom_get_product_id_by_slug($slug) {
    $product = get_page_by_path($slug, OBJECT, 'product');
    return $product ? (int) $product->ID : 0;
}

function axiom_cart_has_product($product_id) {
    if (!$product_id || !WC()->cart) return false;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if ((int) $cart_item['product_id'] === (int) $product_id) {
            return true;
        }
    }

    return false;
}

function axiom_remove_product_from_cart($product_id) {
    if (!$product_id || !WC()->cart) return;

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if ((int) $cart_item['product_id'] === (int) $product_id) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }
}

function axiom_get_checkout_addon_image($product_id, $fallback_icon = '🛡️') {
    $image_url = $product_id ? get_the_post_thumbnail_url($product_id, 'thumbnail') : '';

    if ($image_url) {
        return '<img class="axiom-addon-img" src="' . esc_url($image_url) . '" alt="">';
    }

    return '<span class="axiom-addon-fallback-icon">' . esc_html($fallback_icon) . '</span>';
}

/**
 * IMPORTANT:
 * This hook is manually placed inside:
 * woocommerce/checkout/payment.php
 * directly below coupon box and above Order summary.
 */
add_action('axiom_checkout_after_coupon_before_summary', function () {
    $shipping_id = axiom_get_product_id_by_slug('shipping-protection');
    $starter_id  = axiom_get_product_id_by_slug('research-starter-pack');

    if (!$shipping_id && !$starter_id) {
        return;
    }

    $shipping_checked = axiom_cart_has_product($shipping_id);
    $starter_checked  = axiom_cart_has_product($starter_id);
    ?>

    <div class="axiom-checkout-addons">
        <h3 class="axiom-addons-title">Complete Your Order</h3>

        <?php if ($shipping_id) : ?>
            <label class="axiom-addon-card <?php echo $shipping_checked ? 'is-selected' : ''; ?>">
                <input
                    type="checkbox"
                    class="axiom-addon-toggle"
                    data-product-id="<?php echo esc_attr($shipping_id); ?>"
                    <?php checked($shipping_checked); ?>
                >

                <div class="axiom-addon-media">
                    <?php echo axiom_get_checkout_addon_image($shipping_id, '🛡️'); ?>
                </div>

                <div class="axiom-addon-content">
                    <div class="axiom-addon-top">
                        <strong>Shipping Protection</strong>
                        <span>$4.95</span>
                    </div>
                    <p>Protect your order against loss, theft, or damage during shipping.</p>
                </div>
            </label>
        <?php endif; ?>

        <?php if ($starter_id) : ?>
            <label class="axiom-addon-card <?php echo $starter_checked ? 'is-selected' : ''; ?>">
                <input
                    type="checkbox"
                    class="axiom-addon-toggle"
                    data-product-id="<?php echo esc_attr($starter_id); ?>"
                    <?php checked($starter_checked); ?>
                >

                <div class="axiom-addon-media">
                    <?php echo axiom_get_checkout_addon_image($starter_id, '💧'); ?>
                </div>

                <div class="axiom-addon-content">
                    <div class="axiom-addon-top">
                        <strong>Research Starter Pack</strong>
                        <span>$15.00</span>
                    </div>
                    <p>Includes 10 syringes, 10 alcohol pads, and 1× 10mL BAC water.</p>
                </div>
            </label>
        <?php endif; ?>
    </div>

    <?php
}, 5);

/**
 * AJAX add/remove product.
 */
add_action('wp_ajax_axiom_toggle_checkout_addon', 'axiom_toggle_checkout_addon');
add_action('wp_ajax_nopriv_axiom_toggle_checkout_addon', 'axiom_toggle_checkout_addon');

function axiom_toggle_checkout_addon() {
    check_ajax_referer('axiom_checkout_addons_nonce', 'nonce');

    if (!WC()->cart) {
        wp_send_json_error(array('message' => 'Cart not available.'));
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $checked    = isset($_POST['checked']) && $_POST['checked'] === 'true';

    if (!$product_id) {
        wp_send_json_error(array('message' => 'Missing product ID.'));
    }

    if ($checked) {
        if (!axiom_cart_has_product($product_id)) {
            WC()->cart->add_to_cart($product_id, 1);
        }
    } else {
        axiom_remove_product_from_cart($product_id);
    }

    WC()->cart->calculate_totals();

    wp_send_json_success();
}

/**
 * JS + CSS.
 */
add_action('wp_footer', function () {
    if (!is_checkout()) return;
    ?>

    <script>
    jQuery(function($) {
        $(document.body).on('change', '.axiom-addon-toggle', function() {
            const checkbox = $(this);
            const card = checkbox.closest('.axiom-addon-card');

            checkbox.prop('disabled', true);

            if (checkbox.is(':checked')) {
                card.addClass('is-selected');
            } else {
                card.removeClass('is-selected');
            }

            $.ajax({
                url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                type: 'POST',
                data: {
                    action: 'axiom_toggle_checkout_addon',
                    nonce: '<?php echo esc_js(wp_create_nonce('axiom_checkout_addons_nonce')); ?>',
                    product_id: checkbox.data('product-id'),
                    checked: checkbox.is(':checked')
                },
                success: function() {
                    $(document.body).trigger('update_checkout');
                },
                complete: function() {
                    checkbox.prop('disabled', false);
                }
            });
        });
    });
    </script>

    <style>
        .axiom-checkout-addons {
            margin: 22px 0 24px;
        }

        .axiom-addons-title {
            margin: 0 0 12px;
            font-size: 20px;
            font-weight: 900;
            color: #08122b;
        }

        .axiom-addon-card {
            display: grid;
            grid-template-columns: 28px 68px 1fr;
            gap: 12px;
            align-items: center;
            padding: 14px;
            margin-bottom: 12px;
            border: 2px solid #dbe7f3;
            border-radius: 18px;
            background: #ffffff;
            cursor: pointer;
            transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
        }

        .axiom-addon-card:hover,
        .axiom-addon-card.is-selected {
            border-color: #0b4ea2;
            background: #f8fbff;
            box-shadow: 0 8px 24px rgba(11, 78, 162, 0.08);
        }

        .axiom-addon-card input {
            width: 24px;
            height: 24px;
            accent-color: #0b4ea2;
        }

        .axiom-addon-media {
            width: 68px;
            height: 68px;
            border-radius: 16px;
            border: 1px solid #e5edf6;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .axiom-addon-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .axiom-addon-fallback-icon {
            font-size: 32px;
            line-height: 1;
        }

        .axiom-addon-content {
            min-width: 0;
        }

        .axiom-addon-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 6px;
        }

        .axiom-addon-top strong {
            font-size: 16px;
            font-weight: 900;
            color: #08122b;
        }

        .axiom-addon-top span {
            font-size: 17px;
            font-weight: 900;
            color: #0b4ea2;
            white-space: nowrap;
        }

        .axiom-addon-card p {
            margin: 0;
            font-size: 13px;
            line-height: 1.4;
            color: #6b7280;
        }

        @media (max-width: 480px) {
            .axiom-addon-card {
                grid-template-columns: 26px 56px 1fr;
                gap: 10px;
                padding: 12px;
            }

            .axiom-addon-media {
                width: 56px;
                height: 56px;
                border-radius: 14px;
            }

            .axiom-addon-top strong {
                font-size: 14px;
            }

            .axiom-addon-top span {
                font-size: 15px;
            }

            .axiom-addon-card p {
                font-size: 12px;
            }
        }
    </style>

    <?php
});
