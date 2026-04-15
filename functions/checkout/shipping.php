<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_render_checkout_shipping_methods_fragment() {
    if (!function_exists('WC') || !WC()->cart) {
        return '<p class="axiom-checkout-shipping-empty">Shipping is currently unavailable.</p>';
    }

    $packages = WC()->shipping()->get_packages();
    $chosen_methods = WC()->session ? (array) WC()->session->get('chosen_shipping_methods', array()) : array();

    ob_start();

    if (WC()->cart->needs_shipping() && !empty($packages)) {
        foreach ($packages as $package_index => $package) {
            $available_methods = isset($package['rates']) ? $package['rates'] : array();
            $chosen_method = isset($chosen_methods[$package_index]) ? $chosen_methods[$package_index] : '';

            if (!empty($available_methods)) {
                echo '<ul class="axiom-checkout-shipping-method-list">';

                foreach ($available_methods as $method) {
                    $method_id = 'shipping_method_' . $package_index . '_' . sanitize_title($method->id);

                    echo '<li class="axiom-checkout-shipping-method-item">';
                    echo '<input type="radio"
                        class="shipping_method"
                        name="shipping_method[' . esc_attr($package_index) . ']"
                        data-index="' . esc_attr($package_index) . '"
                        id="' . esc_attr($method_id) . '"
                        value="' . esc_attr($method->id) . '" ' . checked($method->id, $chosen_method, false) . ' />';
                    echo '<label for="' . esc_attr($method_id) . '">';
                    echo wp_kses_post(wc_cart_totals_shipping_method_label($method));
                    echo '</label>';
                    echo '</li>';
                }

                echo '</ul>';
            } else {
                echo '<p class="axiom-checkout-shipping-empty">Enter your full shipping address above to view available shipping methods.</p>';
            }
        }
    } else {
        echo '<p class="axiom-checkout-shipping-empty">Enter your full shipping address above to view available shipping methods.</p>';
    }

    return ob_get_clean();
}

function axiom_checkout_shipping_methods_fragment($fragments) {
    ob_start();
    ?>
    <div class="axiom-checkout-shipping-methods-fragment">
        <?php echo axiom_render_checkout_shipping_methods_fragment(); ?>
    </div>
    <?php
    $fragments['.axiom-checkout-shipping-methods-fragment'] = ob_get_clean();

    return $fragments;
}
add_filter('woocommerce_update_order_review_fragments', 'axiom_checkout_shipping_methods_fragment');
