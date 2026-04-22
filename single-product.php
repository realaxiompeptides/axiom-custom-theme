<?php
defined('ABSPATH') || exit;

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $product = function_exists('wc_get_product') ? wc_get_product(get_the_ID()) : null;

        if ($product && has_term('kits', 'product_cat', $product->get_id())) {
            $kit_template = get_template_directory() . '/product-page/product-page-kit.php';

            if (file_exists($kit_template)) {
                include $kit_template;
            } else {
                include get_template_directory() . '/product-page/product-page.php';
            }
        } else {
            include get_template_directory() . '/product-page/product-page.php';
        }

    endwhile;
endif;

get_footer();
