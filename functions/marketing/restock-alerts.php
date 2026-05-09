<?php
/**
 * Axiom Restock Alerts — TEST MODE ONLY + DEBUG
 *
 * Sends test restock emails only to cheapeptides@gmail.com.
 * Does not email customers.
 */

if (!defined('ABSPATH')) {
    exit;
}

function axiom_restock_debug_log($message) {
    error_log('[AXIOM RESTOCK TEST] ' . $message);
}

function axiom_restock_is_allowed_product($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        axiom_restock_debug_log('Blocked: invalid product object.');
        return false;
    }

    $product_id = $product->get_id();
    $parent_id  = $product->is_type('variation') ? $product->get_parent_id() : 0;
    $check_id   = $parent_id ?: $product_id;

    $allowed_categories = array(
        'peptides',
    );

    $excluded_categories = array(
        'kits',
        'kit',
        'starter-packs',
        'research-starter-pack',
        'shipping-protection',
        'accessories',
        'bac-water',
        'test-products',
        'testing',
    );

    $excluded_slugs = array(
        'shipping-protection',
        'research-starter-pack',
        'bac-water',
        'bac-water-10ml',
    );

    $product_post = get_post($check_id);
    $product_slug = $product_post ? $product_post->post_name : '';

    axiom_restock_debug_log('Checking product ID ' . $product_id . ' / parent ID ' . $parent_id . ' / slug ' . $product_slug);

    if (in_array($product_slug, $excluded_slugs, true)) {
        axiom_restock_debug_log('Blocked: product slug is excluded: ' . $product_slug);
        return false;
    }

    if (has_term($excluded_categories, 'product_cat', $check_id)) {
        axiom_restock_debug_log('Blocked: product is in excluded category.');
        return false;
    }

    if (!has_term($allowed_categories, 'product_cat', $check_id)) {
        axiom_restock_debug_log('Blocked: product is not in allowed category slug peptides.');
        return false;
    }

    axiom_restock_debug_log('Allowed: product passed category/slug checks.');

    return true;
}

function axiom_restock_get_product_display_name($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return '';
    }

    $name = $product->get_name();

    if ($product->is_type('variation')) {
        $parent = wc_get_product($product->get_parent_id());

        if ($parent) {
            $variation_parts = array();

            foreach ($product->get_attributes() as $attribute_name => $attribute_value) {
                if (!$attribute_value) {
                    continue;
                }

                $taxonomy = str_replace('attribute_', '', $attribute_name);

                if (taxonomy_exists($taxonomy)) {
                    $term = get_term_by('slug', $attribute_value, $taxonomy);
                    $variation_parts[] = $term ? $term->name : $attribute_value;
                } else {
                    $variation_parts[] = $attribute_value;
                }
            }

            if (!empty($variation_parts)) {
                $name = $parent->get_name() . ' - ' . implode(' / ', $variation_parts);
            }
        }
    }

    return $name;
}

function axiom_restock_detect_stock_increase($product) {
    axiom_restock_debug_log('Stock hook fired.');

    if (!$product || !is_a($product, 'WC_Product')) {
        axiom_restock_debug_log('Stopped: invalid product.');
        return;
    }

    $product_id = $product->get_id();

    if (!$product->managing_stock()) {
        axiom_restock_debug_log('Stopped: product ID ' . $product_id . ' does not have Manage Stock enabled.');
        return;
    }

    if (!axiom_restock_is_allowed_product($product)) {
        axiom_restock_debug_log('Stopped: product ID ' . $product_id . ' is not allowed.');
        return;
    }

    $new_stock = $product->get_stock_quantity();

    if ($new_stock === null) {
        axiom_restock_debug_log('Stopped: new stock is null for product ID ' . $product_id);
        return;
    }

    $new_stock = (int) $new_stock;

    $last_stock_key = '_axiom_restock_last_known_qty';
    $old_stock_raw  = get_post_meta($product_id, $last_stock_key, true);

    axiom_restock_debug_log('Product ID ' . $product_id . ' old raw stock: ' . print_r($old_stock_raw, true) . ' / new stock: ' . $new_stock);

    if ($old_stock_raw === '') {
        update_post_meta($product_id, $last_stock_key, $new_stock);
        axiom_restock_debug_log('Initialized product ID ' . $product_id . ' with stock ' . $new_stock . '. No email sent on initialization.');
        return;
    }

    $old_stock = (int) $old_stock_raw;

    update_post_meta($product_id, $last_stock_key, $new_stock);

    if ($new_stock <= $old_stock) {
        axiom_restock_debug_log('Stopped: stock did not increase. Old: ' . $old_stock . ' New: ' . $new_stock);
        return;
    }

    if ($new_stock <= 0) {
        axiom_restock_debug_log('Stopped: new stock is zero or below.');
        return;
    }

    $cooldown_key  = '_axiom_restock_last_alert_time';
    $last_alert_at = (int) get_post_meta($product_id, $cooldown_key, true);

    if ($last_alert_at && (time() - $last_alert_at) < 10 * MINUTE_IN_SECONDS) {
        axiom_restock_debug_log('Stopped: cooldown active for product ID ' . $product_id);
        return;
    }

    update_post_meta($product_id, $cooldown_key, time());

    axiom_restock_debug_log('Sending test email for product ID ' . $product_id . '. Old stock: ' . $old_stock . ' New stock: ' . $new_stock);

    axiom_restock_send_test_email($product, $old_stock, $new_stock);
}

add_action('woocommerce_product_set_stock', 'axiom_restock_detect_stock_increase', 20, 1);
add_action('woocommerce_variation_set_stock', 'axiom_restock_detect_stock_increase', 20, 1);

function axiom_restock_send_test_email($product, $old_stock, $new_stock) {
    $product_id = $product->get_id();
    $parent_id  = $product->is_type('variation') ? $product->get_parent_id() : 0;
    $main_id    = $parent_id ?: $product_id;

    $product_name = axiom_restock_get_product_display_name($product);
    $product_url  = get_permalink($main_id);

    $to = 'cheapeptides@gmail.com';

    $subject = '[TEST] Axiom Restock Detected: ' . $product_name;

    $customer_subject = 'Limited Restock: ' . $product_name . ' is back';

    $customer_body = $product_name . " has officially been restocked at Axiom Research.\n\n";
    $customer_body .= "Current available stock: " . $new_stock . " units.\n\n";
    $customer_body .= "This is a limited restock, and availability may change quickly.\n\n";
    $customer_body .= "Shop now:\n";
    $customer_body .= $product_url . "\n\n";
    $customer_body .= "Research use only. Not for human consumption.\n\n";
    $customer_body .= "Axiom Research\n";
    $customer_body .= "Ships from California\n";
    $customer_body .= home_url();

    $sms_body = 'Axiom Research: ' . $product_name . ' is back in stock. Limited quantity available: ' . $product_url . ' Reply STOP to opt out.';

    $message = "TEST MODE ONLY\n";
    $message .= "This was sent only to cheapeptides@gmail.com.\n";
    $message .= "Customers were NOT emailed.\n";
    $message .= "SMS was NOT sent.\n\n";

    $message .= "A valid restock was detected.\n\n";

    $message .= "PRODUCT DETAILS\n";
    $message .= "-------------------------\n";
    $message .= "Product: " . $product_name . "\n";
    $message .= "Product ID: " . $product_id . "\n";
    $message .= "Parent ID: " . $parent_id . "\n";
    $message .= "Old stock: " . $old_stock . "\n";
    $message .= "New stock: " . $new_stock . "\n";
    $message .= "Product URL: " . $product_url . "\n";
    $message .= "Time: " . current_time('mysql') . "\n\n";

    $message .= "CUSTOMER EMAIL SUBJECT PREVIEW\n";
    $message .= "-------------------------\n";
    $message .= $customer_subject . "\n\n";

    $message .= "CUSTOMER EMAIL BODY PREVIEW\n";
    $message .= "-------------------------\n";
    $message .= $customer_body . "\n\n";

    $message .= "SMS COPY PREVIEW\n";
    $message .= "-------------------------\n";
    $message .= $sms_body . "\n\n";

    $message .= "IMPORTANT\n";
    $message .= "-------------------------\n";
    $message .= "This is only the test trigger.\n";
    $message .= "After testing, connect this to Zoho Campaigns for real customer email sends.\n";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: Axiom Research <support@axiomresearch.shop>',
    );

    $sent = wp_mail($to, $subject, $message, $headers);

    if ($sent) {
        axiom_restock_debug_log('wp_mail returned TRUE. Test email attempted/sent to ' . $to);
    } else {
        axiom_restock_debug_log('wp_mail returned FALSE. WordPress mail/SMTP failed.');
    }
}
