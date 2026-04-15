<?php
if (!defined('ABSPATH')) {
    exit;
}

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
