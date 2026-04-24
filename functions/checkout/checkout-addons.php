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

/**
 * Show add-ons directly above order summary.
 */
add_action('woocommerce_checkout_before_order_review_heading', function () {
    $shipping_id = axiom_get_product_id_by_slug('shipping-protection');
    $starter_id  = axiom_get_product_id_by_slug('research-starter-pack');

    $shipping_checked = axiom_cart_has_product($shipping_id);
    $starter_checked  = axiom_cart_has_product($starter_id);
    ?>

    <div class="axiom-checkout-addons">
        <h3 class="axiom-addons-title">Complete Your Order</h3>

        <label class="axiom-addon-card">
            <input
                type="checkbox"
                class="axiom-addon-toggle"
                data-product-id="<?php echo esc_attr($shipping_id); ?>"
                <?php checked($shipping_checked); ?>
            >

            <div class="axiom-addon-content">
                <div class="axiom-addon-top">
                    <strong>Shipping Protection</strong>
                    <span>$4.95</span>
                </div>
                <p>Protect your order against loss, theft, or damage during shipping.</p>
            </div>
        </label>

        <label class="axiom-addon-card">
            <input
                type="checkbox"
                class="axiom-addon-toggle"
                data-product-id="<?php echo esc_attr($starter_id); ?>"
                <?php checked($starter_checked); ?>
            >

            <div class="axiom-addon-content">
                <div class="axiom-addon-top">
                    <strong>Research Starter Pack</strong>
                    <span>$15.00</span>
                </div>
                <p>Includes 10 syringes, 10 alcohol pads, and 1× 10mL BAC water.</p>
            </div>
        </label>
    </div>

    <?php
});

/**
 * AJAX add/remove product.
 */
add_action('wp_ajax_axiom_toggle_checkout_addon', 'axiom_toggle_checkout_addon');
add_action('wp_ajax_nopriv_axiom_toggle_checkout_addon', 'axiom_toggle_checkout_addon');

function axiom_toggle_checkout_addon() {
    check_ajax_referer('axiom_checkout_addons_nonce', 'nonce');

    if (!WC()->cart) {
        wp_send_json_error(['message' => 'Cart not available.']);
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $checked    = isset($_POST['checked']) && $_POST['checked'] === 'true';

    if (!$product_id) {
        wp_send_json_error(['message' => 'Missing product ID.']);
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

            checkbox.prop('disabled', true);

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
            margin: 0 0 22px;
        }

        .axiom-addons-title {
            margin: 0 0 12px;
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }

        .axiom-addon-card {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            padding: 16px;
            margin-bottom: 12px;
            border: 2px solid #0b4ea2;
            border-radius: 14px;
            background: #ffffff;
            cursor: pointer;
        }

        .axiom-addon-card:hover {
            background: #f8fbff;
        }

        .axiom-addon-card input {
            width: 22px;
            height: 22px;
            margin-top: 3px;
            accent-color: #0b4ea2;
            flex: 0 0 auto;
        }

        .axiom-addon-content {
            flex: 1;
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
            color: #111827;
        }

        .axiom-addon-top span {
            font-size: 16px;
            font-weight: 800;
            color: #0b4ea2;
            white-space: nowrap;
        }

        .axiom-addon-card p {
            margin: 0;
            font-size: 14px;
            line-height: 1.45;
            color: #6b7280;
        }
    </style>

    <?php
});
