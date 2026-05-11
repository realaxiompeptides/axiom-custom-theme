<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_custom_theme_assets() {
    $theme_uri  = get_template_directory_uri();
    $theme_path = get_template_directory();

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

    /*
     * Legal / policy pages
     * File: /assets/css/legal/legal.css
     * Templates: /templates/legal/page-*.php
     */
    if (
        function_exists('is_page_template') &&
        (
            is_page_template('templates/legal/page-terms.php') ||
            is_page_template('templates/legal/page-privacy.php') ||
            is_page_template('templates/legal/page-shipping.php') ||
            is_page_template('templates/legal/page-refunds.php') ||
            is_page_template('templates/legal/page-research-use-only.php') ||
            is_page_template('templates/legal/page-fda-medical-disclaimer.php') ||
            is_page_template('templates/legal/page-age-21-policy.php') ||
            is_page_template('templates/legal/page-payment-policy.php') ||
            is_page_template('templates/legal/page-coa-testing-disclaimer.php') ||
            is_page_template('templates/legal/page-marketing-terms.php')
        ) &&
        file_exists($theme_path . '/assets/css/legal/legal.css')
    ) {
        wp_enqueue_style(
            'axiom-legal',
            $theme_uri . '/assets/css/legal/legal.css',
            array('axiom-base'),
            filemtime($theme_path . '/assets/css/legal/legal.css')
        );
    }

    if (file_exists($theme_path . '/assets/css/popup/popup-layout.css')) {
        wp_enqueue_style(
            'axiom-popup-layout',
            $theme_uri . '/assets/css/popup/popup-layout.css',
            array('axiom-base', 'axiom-age-gate'),
            filemtime($theme_path . '/assets/css/popup/popup-layout.css')
        );
    }

    if (file_exists($theme_path . '/assets/css/popup/popup-form.css')) {
        wp_enqueue_style(
            'axiom-popup-form',
            $theme_uri . '/assets/css/popup/popup-form.css',
            array('axiom-base', 'axiom-age-gate', 'axiom-popup-layout'),
            filemtime($theme_path . '/assets/css/popup/popup-form.css')
        );
    }

    if (file_exists($theme_path . '/assets/css/popup/popup-responsive.css')) {
        wp_enqueue_style(
            'axiom-popup-responsive',
            $theme_uri . '/assets/css/popup/popup-responsive.css',
            array('axiom-base', 'axiom-age-gate', 'axiom-popup-layout', 'axiom-popup-form'),
            filemtime($theme_path . '/assets/css/popup/popup-responsive.css')
        );
    }

    if (file_exists($theme_path . '/assets/css/popup/popup-success.css')) {
        wp_enqueue_style(
            'axiom-popup-success',
            $theme_uri . '/assets/css/popup/popup-success.css',
            array(
                'axiom-base',
                'axiom-age-gate',
                'axiom-popup-layout',
                'axiom-popup-form',
                'axiom-popup-responsive',
            ),
            filemtime($theme_path . '/assets/css/popup/popup-success.css')
        );
    }

    if (file_exists($theme_path . '/assets/css/popup/popup-launcher.css')) {
        wp_enqueue_style(
            'axiom-popup-launcher',
            $theme_uri . '/assets/css/popup/popup-launcher.css',
            array(
                'axiom-base',
                'axiom-age-gate',
                'axiom-popup-layout',
                'axiom-popup-form',
                'axiom-popup-responsive',
                'axiom-popup-success',
            ),
            filemtime($theme_path . '/assets/css/popup/popup-launcher.css')
        );
    }

    if (file_exists($theme_path . '/assets/css/mobile-bottom-nav.css')) {
        wp_enqueue_style(
            'axiom-mobile-bottom-nav',
            $theme_uri . '/assets/css/mobile-bottom-nav.css',
            array('axiom-base'),
            filemtime($theme_path . '/assets/css/mobile-bottom-nav.css')
        );
    }

    if ((function_exists('is_front_page') && is_front_page()) || (function_exists('is_home') && is_home())) {
        if (file_exists($theme_path . '/assets/css/homepage/coa-trust-section.css')) {
            wp_enqueue_style(
                'axiom-home-coa-trust',
                $theme_uri . '/assets/css/homepage/coa-trust-section.css',
                array('axiom-base', 'axiom-home', 'axiom-collection'),
                filemtime($theme_path . '/assets/css/homepage/coa-trust-section.css')
            );
        }
    }

    if (function_exists('is_page_template') && is_page_template('page-kits.php')) {
        if (file_exists($theme_path . '/assets/css/kits/kits-base.css')) {
            wp_enqueue_style(
                'axiom-kits-base',
                $theme_uri . '/assets/css/kits/kits-base.css',
                array('axiom-base'),
                filemtime($theme_path . '/assets/css/kits/kits-base.css')
            );
        }

        if (file_exists($theme_path . '/assets/css/kits/kits-hero.css')) {
            wp_enqueue_style(
                'axiom-kits-hero',
                $theme_uri . '/assets/css/kits/kits-hero.css',
                array('axiom-base', 'axiom-kits-base'),
                filemtime($theme_path . '/assets/css/kits/kits-hero.css')
            );
        }

        if (file_exists($theme_path . '/assets/css/kits/kits-info-strip.css')) {
            wp_enqueue_style(
                'axiom-kits-info-strip',
                $theme_uri . '/assets/css/kits/kits-info-strip.css',
                array('axiom-base', 'axiom-kits-base'),
                filemtime($theme_path . '/assets/css/kits/kits-info-strip.css')
            );
        }

        if (file_exists($theme_path . '/assets/css/kits/kits-explainer.css')) {
            wp_enqueue_style(
                'axiom-kits-explainer',
                $theme_uri . '/assets/css/kits/kits-explainer.css',
                array('axiom-base', 'axiom-kits-base'),
                filemtime($theme_path . '/assets/css/kits/kits-explainer.css')
            );
        }

        if (file_exists($theme_path . '/assets/css/kits/kits-grid.css')) {
            wp_enqueue_style(
                'axiom-kits-grid',
                $theme_uri . '/assets/css/kits/kits-grid.css',
                array('axiom-base', 'axiom-kits-base'),
                filemtime($theme_path . '/assets/css/kits/kits-grid.css')
            );
        }

        if (file_exists($theme_path . '/assets/css/kits/kits-faq.css')) {
            wp_enqueue_style(
                'axiom-kits-faq',
                $theme_uri . '/assets/css/kits/kits-faq.css',
                array('axiom-base', 'axiom-kits-base'),
                filemtime($theme_path . '/assets/css/kits/kits-faq.css')
            );
        }

        if (file_exists($theme_path . '/assets/js/kits/kits-faq.js')) {
            wp_enqueue_script(
                'axiom-kits-faq',
                $theme_uri . '/assets/js/kits/kits-faq.js',
                array(),
                filemtime($theme_path . '/assets/js/kits/kits-faq.js'),
                true
            );
        }
    }

    if (
        function_exists('is_shop') &&
        (is_shop() || is_product_category() || is_product_tag() || is_tax('product_cat') || is_tax('product_tag'))
    ) {
        wp_enqueue_style('axiom-catalog-layout', $theme_uri . '/assets/css/shop/catalog-layout.css', array('axiom-base'), '1.0');
        wp_enqueue_style('axiom-catalog-search', $theme_uri . '/assets/css/shop/catalog-search.css', array('axiom-base', 'axiom-catalog-layout'), '1.0');
        wp_enqueue_style('axiom-catalog-filters', $theme_uri . '/assets/css/shop/catalog-filters.css', array('axiom-base', 'axiom-catalog-layout'), '1.0');
        wp_enqueue_style('axiom-catalog-cards', $theme_uri . '/assets/css/shop/catalog-cards.css', array('axiom-base', 'axiom-catalog-layout'), '1.0');
        wp_enqueue_style('axiom-catalog-disclaimer', $theme_uri . '/assets/css/shop/catalog-disclaimer.css', array('axiom-base', 'axiom-catalog-layout'), '1.0');

        if (file_exists($theme_path . '/assets/js/shop/catalog.js')) {
            wp_enqueue_script('axiom-catalog', $theme_uri . '/assets/js/shop/catalog.js', array(), '1.0', true);

            wp_localize_script('axiom-catalog', 'AXIOM_CATALOG', array(
                'shopUrl' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'),
            ));
        }
    }

    if (function_exists('is_page_template') && is_page_template('track-order/track-order-template.php')) {
        wp_enqueue_style('axiom-track-order', $theme_uri . '/assets/css/track-order/track-order.css', array('axiom-base'), '1.0');

        if (file_exists($theme_path . '/assets/js/track-order/track-order.js')) {
            wp_enqueue_script('axiom-track-order', $theme_uri . '/assets/js/track-order/track-order.js', array(), '1.0', true);
        }
    }

    if (function_exists('is_product') && is_product()) {
        wp_enqueue_style('axiom-product-layout', $theme_uri . '/assets/css/product-page/layout.css', array('axiom-base'), '1.2');
        wp_enqueue_style('axiom-product-purchase-box', $theme_uri . '/assets/css/product-page/purchase-box.css', array('axiom-base', 'axiom-product-layout'), '1.2');
        wp_enqueue_style('axiom-product-trust-elements', $theme_uri . '/assets/css/product-page/trust-elements.css', array('axiom-base', 'axiom-product-layout', 'axiom-product-purchase-box'), '1.2');
        wp_enqueue_style('axiom-product-description', $theme_uri . '/assets/css/product-page/description.css', array('axiom-base', 'axiom-product-layout'), '1.2');
        wp_enqueue_style('axiom-product-sticky-bar', $theme_uri . '/assets/css/product-page/sticky-bar.css', array('axiom-base', 'axiom-product-layout', 'axiom-product-purchase-box'), '1.2');

        if (file_exists($theme_path . '/assets/css/product-page/why-choose-us.css')) {
            wp_enqueue_style(
                'axiom-product-why-choose-us',
                $theme_uri . '/assets/css/product-page/why-choose-us.css',
                array('axiom-base', 'axiom-product-layout', 'axiom-product-description'),
                filemtime($theme_path . '/assets/css/product-page/why-choose-us.css')
            );
        }

        if (file_exists($theme_path . '/assets/css/product-page/reviews-faq-tabs.css')) {
            wp_enqueue_style(
                'axiom-product-reviews-faq-tabs',
                $theme_uri . '/assets/css/product-page/reviews-faq-tabs.css',
                array(),
                filemtime($theme_path . '/assets/css/product-page/reviews-faq-tabs.css')
            );
        }

        if (file_exists($theme_path . '/assets/css/product-page/product-image-trust-icons.css')) {
            wp_enqueue_style(
                'axiom-product-image-trust-icons',
                $theme_uri . '/assets/css/product-page/product-image-trust-icons.css',
                array('axiom-base', 'axiom-product-layout'),
                filemtime($theme_path . '/assets/css/product-page/product-image-trust-icons.css')
            );
        }

        if (file_exists($theme_path . '/assets/css/product-page/enhanced-product.css')) {
            wp_enqueue_style(
                'axiom-enhanced-product',
                $theme_uri . '/assets/css/product-page/enhanced-product.css',
                array('axiom-base', 'axiom-product-layout', 'axiom-product-purchase-box'),
                filemtime($theme_path . '/assets/css/product-page/enhanced-product.css')
            );
        }

        if (file_exists($theme_path . '/assets/js/product-page.js')) {
            wp_enqueue_script(
                'axiom-product-page',
                $theme_uri . '/assets/js/product-page.js',
                array('jquery'),
                filemtime($theme_path . '/assets/js/product-page.js'),
                true
            );
        }

        if (file_exists($theme_path . '/assets/js/product-page/sticky-add-to-cart.js')) {
            wp_enqueue_script(
                'axiom-sticky-add-to-cart',
                $theme_uri . '/assets/js/product-page/sticky-add-to-cart.js',
                array('jquery', 'axiom-product-page', 'axiom-main'),
                filemtime($theme_path . '/assets/js/product-page/sticky-add-to-cart.js'),
                true
            );
        }

        $axiom_current_product = null;

        if (function_exists('wc_get_product')) {
            global $post;

            if (!empty($post) && !empty($post->ID)) {
                $axiom_current_product = wc_get_product($post->ID);
            }
        }

        if ($axiom_current_product && has_term('kits', 'product_cat', $axiom_current_product->get_id())) {
            $kit_css_files = array(
                'kit-page-base'     => '/assets/css/product-page/kit-page-base.css',
                'kit-page-hero'     => '/assets/css/product-page/kit-page-hero.css',
                'kit-page-purchase' => '/assets/css/product-page/kit-page-purchase.css',
                'kit-page-volume'   => '/assets/css/product-page/kit-page-volume.css',
                'kit-page-upsell'   => '/assets/css/product-page/kit-page-upsell.css',
                'kit-page-mobile'   => '/assets/css/product-page/kit-page-mobile.css',
            );

            foreach ($kit_css_files as $handle_suffix => $file_path) {
                if (file_exists($theme_path . $file_path)) {
                    wp_enqueue_style(
                        'axiom-' . $handle_suffix,
                        $theme_uri . $file_path,
                        array('axiom-base', 'axiom-product-layout', 'axiom-product-purchase-box'),
                        filemtime($theme_path . $file_path)
                    );
                }
            }
        }
    }

    if (function_exists('is_page_template') && is_page_template('page-reviews.php')) {
        if (file_exists($theme_path . '/assets/css/reviews-page.css')) {
            wp_enqueue_style(
                'axiom-reviews-page',
                $theme_uri . '/assets/css/reviews-page.css',
                array('axiom-base'),
                filemtime($theme_path . '/assets/css/reviews-page.css')
            );
        }
    }

    if (function_exists('is_checkout') && is_checkout() && !is_order_received_page()) {
        wp_enqueue_style('axiom-checkout-layout', $theme_uri . '/assets/css/checkout/checkout-layout.css', array('axiom-base'), '1.1');
        wp_enqueue_style('axiom-checkout-fields', $theme_uri . '/assets/css/checkout/checkout-fields.css', array('axiom-base', 'axiom-checkout-layout'), '1.1');
        wp_enqueue_style('axiom-checkout-order-summary', $theme_uri . '/assets/css/checkout/checkout-order-summary.css', array('axiom-base', 'axiom-checkout-layout'), '1.1');
        wp_enqueue_style('axiom-checkout-payment', $theme_uri . '/assets/css/checkout/checkout-payment.css', array('axiom-base', 'axiom-checkout-layout'), '1.1');
        wp_enqueue_style('axiom-checkout-shipping-methods', $theme_uri . '/assets/css/checkout/checkout-shipping-methods.css', array('axiom-base', 'axiom-checkout-layout'), '1.0');
        wp_enqueue_style('axiom-checkout-research-box', $theme_uri . '/assets/css/checkout/checkout-research-box.css', array('axiom-base', 'axiom-checkout-layout', 'axiom-checkout-payment'), '1.0');

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

        if (file_exists($theme_path . '/assets/js/checkout-page.js')) {
            wp_enqueue_script(
                'axiom-checkout',
                $theme_uri . '/assets/js/checkout-page.js',
                array('jquery', 'wc-checkout'),
                filemtime($theme_path . '/assets/js/checkout-page.js'),
                true
            );

            wp_localize_script('axiom-checkout', 'AXIOM_CHECKOUT', array(
                'ajaxUrl'           => admin_url('admin-ajax.php'),
                'applyCouponAction' => 'axiom_apply_coupon',
                'applyCouponNonce'  => wp_create_nonce('axiom_apply_coupon'),
            ));
        }
    }

    if (function_exists('is_checkout') && is_checkout() && is_order_received_page()) {
        wp_enqueue_style('axiom-thankyou', $theme_uri . '/assets/css/order-received/thankyou.css', array('axiom-base'), '1.2');
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

    if (file_exists($theme_path . '/assets/js/popup.js')) {
        wp_enqueue_script(
            'axiom-popup',
            $theme_uri . '/assets/js/popup.js',
            array('jquery', 'axiom-main'),
            filemtime($theme_path . '/assets/js/popup.js'),
            true
        );

        wp_localize_script('axiom-popup', 'axiom_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('axiom_popup_capture'),
        ));
    }

    if (file_exists($theme_path . '/assets/js/klaviyo-after-age-gate.js')) {
        wp_enqueue_script(
            'axiom-klaviyo-after-age-gate',
            $theme_uri . '/assets/js/klaviyo-after-age-gate.js',
            array('axiom-main'),
            filemtime($theme_path . '/assets/js/klaviyo-after-age-gate.js'),
            true
        );
    }
}

add_action('wp_enqueue_scripts', 'axiom_custom_theme_assets');
