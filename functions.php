<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_custom_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    add_image_size('axiom_product_card', 700, 700, true);
}
add_action('after_setup_theme', 'axiom_custom_theme_setup');

function axiom_custom_theme_assets() {
    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'axiom-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'axiom-fa',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
        array(),
        '6.5.2'
    );

    wp_enqueue_style('axiom-base', $theme_uri . '/assets/css/style.css', array(), '2.0');
    wp_enqueue_style('axiom-menu', $theme_uri . '/assets/css/menu.css', array('axiom-base'), '2.0');
    wp_enqueue_style('axiom-cart', $theme_uri . '/assets/css/cart.css', array('axiom-base'), '2.0');
    wp_enqueue_style('axiom-home', $theme_uri . '/assets/css/home.css', array('axiom-base'), '2.0');
    wp_enqueue_style('axiom-collection', $theme_uri . '/assets/css/homepage-collection.css', array('axiom-base'), '2.0');
    wp_enqueue_style('axiom-faq', $theme_uri . '/assets/css/faq-section.css', array('axiom-base'), '2.0');
    wp_enqueue_style('axiom-footer', $theme_uri . '/assets/css/footer.css', array('axiom-base'), '2.0');
    wp_enqueue_style('axiom-age-gate', $theme_uri . '/assets/css/age-gate.css', array('axiom-base'), '2.0');

    if (
        function_exists('is_shop') &&
        (is_shop() || is_product_category() || is_product_tag() || is_tax('product_cat') || is_tax('product_tag'))
    ) {
        wp_enqueue_style(
            'axiom-catalog-layout',
            $theme_uri . '/assets/css/shop/catalog-layout.css',
            array('axiom-base'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-catalog-search',
            $theme_uri . '/assets/css/shop/catalog-search.css',
            array('axiom-base', 'axiom-catalog-layout'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-catalog-filters',
            $theme_uri . '/assets/css/shop/catalog-filters.css',
            array('axiom-base', 'axiom-catalog-layout'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-catalog-cards',
            $theme_uri . '/assets/css/shop/catalog-cards.css',
            array('axiom-base', 'axiom-catalog-layout'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-catalog-disclaimer',
            $theme_uri . '/assets/css/shop/catalog-disclaimer.css',
            array('axiom-base', 'axiom-catalog-layout'),
            '1.0'
        );

        if (file_exists(get_template_directory() . '/assets/js/shop/catalog.js')) {
            wp_enqueue_script(
                'axiom-catalog',
                $theme_uri . '/assets/js/shop/catalog.js',
                array(),
                '1.0',
                true
            );

            wp_localize_script('axiom-catalog', 'AXIOM_CATALOG', array(
                'shopUrl' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'),
            ));
        }
    }

    if (function_exists('is_page_template') && is_page_template('track-order/track-order-template.php')) {
        wp_enqueue_style(
            'axiom-track-order',
            $theme_uri . '/assets/css/track-order/track-order.css',
            array('axiom-base'),
            '1.0'
        );

        if (file_exists(get_template_directory() . '/assets/js/track-order/track-order.js')) {
            wp_enqueue_script(
                'axiom-track-order',
                $theme_uri . '/assets/js/track-order/track-order.js',
                array(),
                '1.0',
                true
            );
        }
    }

    if (function_exists('is_product') && is_product()) {
        wp_enqueue_style(
            'axiom-product-layout',
            $theme_uri . '/assets/css/product-page/layout.css',
            array('axiom-base'),
            '1.2'
        );

        wp_enqueue_style(
            'axiom-product-purchase-box',
            $theme_uri . '/assets/css/product-page/purchase-box.css',
            array('axiom-base', 'axiom-product-layout'),
            '1.2'
        );

        wp_enqueue_style(
            'axiom-product-trust-elements',
            $theme_uri . '/assets/css/product-page/trust-elements.css',
            array('axiom-base', 'axiom-product-layout', 'axiom-product-purchase-box'),
            '1.2'
        );

        wp_enqueue_style(
            'axiom-product-description',
            $theme_uri . '/assets/css/product-page/description.css',
            array('axiom-base', 'axiom-product-layout'),
            '1.2'
        );

        wp_enqueue_style(
            'axiom-product-sticky-bar',
            $theme_uri . '/assets/css/product-page/sticky-bar.css',
            array('axiom-base', 'axiom-product-layout', 'axiom-product-purchase-box'),
            '1.2'
        );

        wp_enqueue_script(
            'axiom-product-page',
            $theme_uri . '/assets/js/product-page.js',
            array('jquery'),
            '1.4',
            true
        );
    }

    if (function_exists('is_checkout') && is_checkout() && !is_order_received_page()) {
        wp_enqueue_style(
            'axiom-checkout-layout',
            $theme_uri . '/assets/css/checkout/checkout-layout.css',
            array('axiom-base'),
            '1.1'
        );

        wp_enqueue_style(
            'axiom-checkout-fields',
            $theme_uri . '/assets/css/checkout/checkout-fields.css',
            array('axiom-base', 'axiom-checkout-layout'),
            '1.1'
        );

        wp_enqueue_style(
            'axiom-checkout-order-summary',
            $theme_uri . '/assets/css/checkout/checkout-order-summary.css',
            array('axiom-base', 'axiom-checkout-layout'),
            '1.1'
        );

        wp_enqueue_style(
            'axiom-checkout-payment',
            $theme_uri . '/assets/css/checkout/checkout-payment.css',
            array('axiom-base', 'axiom-checkout-layout'),
            '1.1'
        );

        wp_enqueue_style(
            'axiom-checkout-shipping-methods',
            $theme_uri . '/assets/css/checkout/checkout-shipping-methods.css',
            array('axiom-base', 'axiom-checkout-layout'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-checkout-research-box',
            $theme_uri . '/assets/css/checkout/checkout-research-box.css',
            array('axiom-base', 'axiom-checkout-layout', 'axiom-checkout-payment'),
            '1.0'
        );

        wp_enqueue_style(
            'axiom-checkout-mobile',
            $theme_uri . '/assets/css/checkout/checkout-mobile.css',
            array(
                'axiom-base',
                'axiom-checkout-layout',
                'axiom-checkout-fields',
                'axiom-checkout-order-summary',
                'axiom-checkout-payment',
                'axiom-checkout-shipping-methods',
                'axiom-checkout-research-box',
            ),
            '1.1'
        );

        if (file_exists(get_template_directory() . '/assets/js/checkout-page.js')) {
            wp_enqueue_script(
                'axiom-checkout',
                $theme_uri . '/assets/js/checkout-page.js',
                array('jquery', 'wc-checkout'),
                '1.5',
                true
            );

            wp_localize_script('axiom-checkout', 'AXIOM_CHECKOUT', array(
                'ajaxUrl'           => admin_url('admin-ajax.php'),
                'applyCouponAction' => 'axiom_apply_coupon',
                'applyCouponNonce'  => wp_create_nonce('axiom_apply_coupon'),
            ));
        }
    }

    wp_enqueue_script('jquery');
    wp_enqueue_script('wc-cart-fragments');

    wp_enqueue_script(
        'axiom-main',
        $theme_uri . '/assets/js/main.js',
        array('jquery', 'wc-cart-fragments'),
        '2.0',
        true
    );

    wp_localize_script('axiom-main', 'AXIOM_THEME', array(
        'themeUrl'    => $theme_uri,
        'homeUrl'     => home_url('/'),
        'shopUrl'     => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'),
        'cartUrl'     => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/'),
        'checkoutUrl' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/'),
        'accountUrl'  => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'),
        'ajaxUrl'     => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('axiom_cart_drawer'),
    ));
}
add_action('wp_enqueue_scripts', 'axiom_custom_theme_assets');

function axiom_disable_default_catalog_bits() {
    if (!function_exists('is_shop')) {
        return;
    }

    if (is_shop() || is_product_category() || is_product_tag() || is_tax('product_cat') || is_tax('product_tag')) {
        remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
        remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
        remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    }
}
add_action('wp', 'axiom_disable_default_catalog_bits');

function axiom_get_logo_url($filename = 'axiom-logo.PNG') {
    return get_template_directory_uri() . '/assets/images/' . $filename;
}

function axiom_cart_variation_text($cart_item) {
    if (empty($cart_item['variation']) || !is_array($cart_item['variation'])) {
        return '';
    }

    $parts = array();

    foreach ($cart_item['variation'] as $key => $value) {
        if (!$value) {
            continue;
        }

        $label = wc_attribute_label(str_replace('attribute_', '', $key));
        $parts[] = $label . ': ' . $value;
    }

    return implode(' • ', $parts);
}

function axiom_find_bac_water_upsell_product() {
    $candidate_slugs = array(
        'bac-water-10ml',
        'bac-water-10mL',
        'bac-water',
    );

    foreach ($candidate_slugs as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'product');
        if (!$page) {
            continue;
        }

        $product = wc_get_product($page->ID);
        if ($product && $product->is_purchasable() && $product->is_in_stock()) {
            return $product;
        }
    }

    $query = new WP_Query(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        's'              => 'BAC Water',
    ));

    if ($query->have_posts()) {
        $query->the_post();
        $product = wc_get_product(get_the_ID());
        wp_reset_postdata();

        if ($product && $product->is_purchasable() && $product->is_in_stock()) {
            return $product;
        }
    }

    return null;
}

function axiom_get_cart_drawer_payload() {
    $items = array();

    if (!function_exists('WC') || !WC()->cart) {
        return array(
            'count'    => 0,
            'subtotal' => '$0.00',
            'items'    => array(),
            'upsell'   => null,
        );
    }

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = isset($cart_item['data']) ? $cart_item['data'] : null;

        if (!$product || !is_a($product, 'WC_Product')) {
            continue;
        }

        $product_id    = $product->get_id();
        $image         = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail');
        $name          = $product->get_name();
        $quantity      = (int) $cart_item['quantity'];
        $line_subtotal = WC()->cart->get_product_subtotal($product, $quantity);
        $price_html    = $product->get_price_html();
        $variant       = axiom_cart_variation_text($cart_item);

        $items[] = array(
            'key'       => $cart_item_key,
            'productId' => $product_id,
            'name'      => $name,
            'image'     => $image ? $image : wc_placeholder_img_src(),
            'quantity'  => $quantity,
            'subtotal'  => $line_subtotal,
            'priceHtml' => $price_html,
            'variant'   => $variant,
            'link'      => get_permalink($product_id),
        );
    }

    $upsell_data = null;
    $upsell = axiom_find_bac_water_upsell_product();

    if ($upsell) {
        $upsell_image = wp_get_attachment_image_url($upsell->get_image_id(), 'woocommerce_thumbnail');

        $upsell_data = array(
            'productId' => $upsell->get_id(),
            'name'      => $upsell->get_name(),
            'image'     => $upsell_image ? $upsell_image : wc_placeholder_img_src(),
            'priceHtml' => $upsell->get_price_html(),
            'link'      => get_permalink($upsell->get_id()),
        );
    }

    return array(
        'count'    => WC()->cart->get_cart_contents_count(),
        'subtotal' => WC()->cart->get_cart_subtotal(),
        'items'    => $items,
        'upsell'   => $upsell_data,
    );
}

function axiom_get_cart_drawer_data() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    wp_send_json_success(axiom_get_cart_drawer_payload());
}
add_action('wp_ajax_axiom_get_cart_drawer', 'axiom_get_cart_drawer_data');
add_action('wp_ajax_nopriv_axiom_get_cart_drawer', 'axiom_get_cart_drawer_data');

function axiom_update_cart_item_quantity() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $cart_key = isset($_POST['cart_key']) ? wc_clean(wp_unslash($_POST['cart_key'])) : '';
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;

    if (!$cart_key) {
        wp_send_json_error(array('message' => 'Missing cart key.'));
    }

    if ($quantity <= 0) {
        WC()->cart->remove_cart_item($cart_key);
    } else {
        WC()->cart->set_quantity($cart_key, $quantity, true);
    }

    WC()->cart->calculate_totals();

    wp_send_json_success(axiom_get_cart_drawer_payload());
}
add_action('wp_ajax_axiom_update_cart_item_quantity', 'axiom_update_cart_item_quantity');
add_action('wp_ajax_nopriv_axiom_update_cart_item_quantity', 'axiom_update_cart_item_quantity');

function axiom_remove_cart_item() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $cart_key = isset($_POST['cart_key']) ? wc_clean(wp_unslash($_POST['cart_key'])) : '';

    if (!$cart_key) {
        wp_send_json_error(array('message' => 'Missing cart key.'));
    }

    WC()->cart->remove_cart_item($cart_key);
    WC()->cart->calculate_totals();

    wp_send_json_success(axiom_get_cart_drawer_payload());
}
add_action('wp_ajax_axiom_remove_cart_item', 'axiom_remove_cart_item');
add_action('wp_ajax_nopriv_axiom_remove_cart_item', 'axiom_remove_cart_item');

function axiom_add_simple_product_to_cart() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(array('message' => 'Missing product ID.'));
    }

    $product = wc_get_product($product_id);

    if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
        wp_send_json_error(array('message' => 'Product unavailable.'));
    }

    if ($product->is_type('variable')) {
        wp_send_json_error(array('message' => 'Variable product requires options.'));
    }

    $added = WC()->cart->add_to_cart($product_id, 1);

    if (!$added) {
        wp_send_json_error(array('message' => 'Could not add product.'));
    }

    WC()->cart->calculate_totals();

    wp_send_json_success(axiom_get_cart_drawer_payload());
}
add_action('wp_ajax_axiom_add_simple_product_to_cart', 'axiom_add_simple_product_to_cart');
add_action('wp_ajax_nopriv_axiom_add_simple_product_to_cart', 'axiom_add_simple_product_to_cart');

function axiom_add_product_from_product_page() {
    check_ajax_referer('axiom_cart_drawer', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable.'));
    }

    $product_id   = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $quantity     = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

    if (!$product_id || $quantity < 1) {
        wp_send_json_error(array('message' => 'Invalid product data.'));
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => 'Product not found.'));
    }

    $added = false;

    if ($product->is_type('variable')) {
        if (!$variation_id) {
            wp_send_json_error(array('message' => 'Please select a variation.'));
        }

        $variation = wc_get_product($variation_id);
        if (!$variation || !$variation->is_purchasable()) {
            wp_send_json_error(array('message' => 'Variation unavailable.'));
        }

        $variation_data = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'attribute_') === 0) {
                $variation_data[wc_clean(wp_unslash($key))] = wc_clean(wp_unslash($value));
            }
        }

        $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data);
    } else {
        if (!$product->is_purchasable()) {
            wp_send_json_error(array('message' => 'Product unavailable.'));
        }

        $added = WC()->cart->add_to_cart($product_id, $quantity);
    }

    if (!$added) {
        wp_send_json_error(array('message' => 'Could not add product to cart.'));
    }

    WC()->cart->calculate_totals();

    wp_send_json_success(array(
        'message' => 'Added to cart.',
        'cart'    => axiom_get_cart_drawer_payload(),
    ));
}
add_action('wp_ajax_axiom_add_product_from_product_page', 'axiom_add_product_from_product_page');
add_action('wp_ajax_nopriv_axiom_add_product_from_product_page', 'axiom_add_product_from_product_page');

function axiom_reorder_checkout_fields($fields) {
    if (isset($fields['billing']['billing_email'])) {
        $fields['billing']['billing_email']['priority'] = 10;
        $fields['billing']['billing_email']['required'] = true;
    }

    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['priority'] = 20;
    }

    if (isset($fields['billing']['billing_first_name'])) {
        $fields['billing']['billing_first_name']['priority'] = 30;
    }

    if (isset($fields['billing']['billing_last_name'])) {
        $fields['billing']['billing_last_name']['priority'] = 40;
    }

    if (isset($fields['billing']['billing_country'])) {
        $fields['billing']['billing_country']['priority'] = 50;
    }

    if (isset($fields['billing']['billing_address_1'])) {
        $fields['billing']['billing_address_1']['priority'] = 60;
    }

    if (isset($fields['billing']['billing_address_2'])) {
        $fields['billing']['billing_address_2']['priority'] = 70;
    }

    if (isset($fields['billing']['billing_city'])) {
        $fields['billing']['billing_city']['priority'] = 80;
    }

    if (isset($fields['billing']['billing_state'])) {
        $fields['billing']['billing_state']['priority'] = 90;
    }

    if (isset($fields['billing']['billing_postcode'])) {
        $fields['billing']['billing_postcode']['priority'] = 100;
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'axiom_reorder_checkout_fields', 999);

function axiom_checkout_research_use_validation() {
    if (!isset($_POST['axiom_research_use_ack'])) {
        wc_add_notice('Please confirm the research use only acknowledgment before placing your order.', 'error');
    }
}
add_action('woocommerce_checkout_process', 'axiom_checkout_research_use_validation');

function axiom_checkout_research_use_save($order_id) {
    $value = isset($_POST['axiom_research_use_ack']) ? 'yes' : 'no';
    update_post_meta($order_id, '_axiom_research_use_ack', $value);
}
add_action('woocommerce_checkout_update_order_meta', 'axiom_checkout_research_use_save');

/*
 * Render shipping methods for the custom checkout block.
 */
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

function axiom_apply_coupon() {
    check_ajax_referer('axiom_apply_coupon', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array(
            'message' => 'Cart unavailable.',
        ));
    }

    $coupon_code = isset($_POST['coupon_code'])
        ? wc_format_coupon_code(wc_clean(wp_unslash($_POST['coupon_code'])))
        : '';

    if (!$coupon_code) {
        wp_send_json_error(array(
            'message' => 'Please enter a discount code.',
        ));
    }

    if (WC()->cart->has_discount($coupon_code)) {
        wp_send_json_success(array(
            'message' => 'Discount already applied.',
            'coupon_code' => $coupon_code,
        ));
    }

    wc_clear_notices();

    $applied = WC()->cart->apply_coupon($coupon_code);

    if (is_wp_error($applied)) {
        wp_send_json_error(array(
            'message' => $applied->get_error_message(),
        ));
    }

    if (!$applied) {
        $notices = wc_get_notices('error');
        $message = 'Discount code not valid.';

        if (!empty($notices) && !empty($notices[0]['notice'])) {
            $message = wp_strip_all_tags($notices[0]['notice']);
        }

        wc_clear_notices();

        wp_send_json_error(array(
            'message' => $message,
        ));
    }

    WC()->cart->calculate_totals();

    $notices = wc_get_notices('success');
    $message = 'Discount applied.';

    if (!empty($notices) && !empty($notices[0]['notice'])) {
        $message = wp_strip_all_tags($notices[0]['notice']);
    }

    wc_clear_notices();

    wp_send_json_success(array(
        'message' => $message,
        'coupon_code' => $coupon_code,
    ));
}
add_action('wp_ajax_axiom_apply_coupon', 'axiom_apply_coupon');
add_action('wp_ajax_nopriv_axiom_apply_coupon', 'axiom_apply_coupon');
