<?php
if (!defined('ABSPATH')) {
    exit;
}

add_filter('woocommerce_email_order_items_args', function($args) {
    $args['show_image'] = true;
    $args['image_size'] = array(90, 90);
    return $args;
});

add_filter('woocommerce_email_styles', function($css) {
    $css .= '
        h1, h2, h3 { font-weight: 900 !important; }
        a { color: #3B6FE0; }
        .td, .th, table { border-color: #24385f !important; }
    ';
    return $css;
});
