<?php
/**
 * Axiom Restock Alerts
 *
 * Detects when stock is increased on normal peptide products only.
 * Sends an internal restock email to the site admin / Zoho Mail SMTP.
 *
 * This does NOT send mass customer emails yet.
 * Use this first to safely confirm every restock alert works.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if this product is allowed to trigger restock alerts.
 */
function axiom_restock_is_allowed_product($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return false;
    }

    $product_id = $product->get_id();
    $parent_id  = $product->is_type('variation') ? $product->get_parent_id() : 0;
    $check_id   = $parent_id ?: $product_id;

    /**
     * ONLY products in this category can trigger restock alerts.
     *
     * IMPORTANT:
     * Your normal peptide products need to be in a WooCommerce category
     * with the slug: peptides
     */
    $allowed_categories = array(
        'peptides',
    );

    /**
     * These categories can NEVER trigger restock alerts.
     */
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

    /**
     * These exact product slugs can NEVER trigger restock alerts.
     */
    $excluded_slugs = array(
        'shipping-protection',
        'research-starter-pack',
        'bac-water',
        'bac-water-10ml',
    );

    $product_post = get_post($check_id);
    $product_slug = $product_post ? $product_post->post_name : '';

    if (in_array($product_slug, $excluded_slugs, true)) {
        return false;
    }

    if (has_term($excluded_categories, 'product_cat', $check_id)) {
        return false;
    }

    if (!has_term($allowed_categories, 'product_cat', $check_id)) {
        return false;
    }

    return true;
}

/**
 * Get clean product name, including variation details when available.
 *
 * Example:
 * KISSPEPTIN - 5mg / 3mL
 */
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

/**
 * Detect stock increase.
 */
function axiom_restock_detect_stock_increase($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    /**
     * Only products/variations with stock management enabled.
     */
    if (!$product->managing_stock()) {
        return;
    }

    if (!axiom_restock_is_allowed_product($product)) {
        return;
    }

    $product_id = $product->get_id();
    $new_stock  = $product->get_stock_quantity();

    if ($new_stock === null) {
        return;
    }

    $new_stock = (int) $new_stock;

    $last_stock_key = '_axiom_restock_last_known_qty';
    $old_stock_raw  = get_post_meta($product_id, $last_stock_key, true);

    /**
     * First time this code sees the product:
     * save current stock but do NOT send an alert.
     *
     * This prevents a bunch of fake alerts when you first install the code.
     */
    if ($old_stock_raw === '') {
        update_post_meta($product_id, $last_stock_key, $new_stock);
        return;
    }

    $old_stock = (int) $old_stock_raw;

    /**
     * Always update stored stock number.
     */
    update_post_meta($product_id, $last_stock_key, $new_stock);

    /**
     * Only send if stock increased.
     *
     * Examples:
     * 0 -> 20 = sends
     * 5 -> 30 = sends
     * 30 -> 0 = does not send
     * 30 -> 20 = does not send
     */
    if ($new_stock <= $old_stock) {
        return;
    }

    /**
     * Never send if new stock is zero or negative.
     */
    if ($new_stock <= 0) {
        return;
    }

    /**
     * Prevent duplicate alerts if WooCommerce fires stock hooks twice.
     */
    $cooldown_key  = '_axiom_restock_last_alert_time';
    $last_alert_at = (int) get_post_meta($product_id, $cooldown_key, true);

    if ($last_alert_at && (time() - $last_alert_at) < 10 * MINUTE_IN_SECONDS) {
        return;
    }

    update_post_meta($product_id, $cooldown_key, time());

    axiom_restock_send_internal_email($product, $old_stock, $new_stock);
}

add_action('woocommerce_product_set_stock', 'axiom_restock_detect_stock_increase', 20, 1);
add_action('woocommerce_variation_set_stock', 'axiom_restock_detect_stock_increase', 20, 1);

/**
 * Send the internal restock email to you.
 */
function axiom_restock_send_internal_email($product, $old_stock, $new_stock) {
    $product_id = $product->get_id();
    $parent_id  = $product->is_type('variation') ? $product->get_parent_id() : 0;
    $main_id    = $parent_id ?: $product_id;

    $product_name = axiom_restock_get_product_display_name($product);
    $product_url  = get_permalink($main_id);

    $subject = 'Axiom Restock Detected: ' . $product_name;

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

    $message = "A valid restock was detected.\n\n";
    $message .= "PRODUCT DETAILS\n";
    $message .= "-------------------------\n";
    $message .= "Product: " . $product_name . "\n";
    $message .= "Product ID: " . $product_id . "\n";
    $message .= "Parent ID: " . $parent_id . "\n";
    $message .= "Old stock: " . $old_stock . "\n";
    $message .= "New stock: " . $new_stock . "\n";
    $message .= "Product URL: " . $product_url . "\n";
    $message .= "Time: " . current_time('mysql') . "\n\n";

    $message .= "CUSTOMER EMAIL SUBJECT\n";
    $message .= "-------------------------\n";
    $message .= $customer_subject . "\n\n";

    $message .= "CUSTOMER EMAIL BODY\n";
    $message .= "-------------------------\n";
    $message .= $customer_body . "\n\n";

    $message .= "SMS COPY\n";
    $message .= "-------------------------\n";
    $message .= $sms_body . "\n\n";

    $message .= "IMPORTANT\n";
    $message .= "-------------------------\n";
    $message .= "This email was sent to you only. It did not blast customers.\n";
    $message .= "Use Zoho Campaigns later for mass customer emails/SMS.\n";

    /**
     * Sends to WordPress admin email.
     *
     * If your SMTP plugin uses Zoho Mail, this will send through Zoho.
     */
    $to = get_option('admin_email');

    wp_mail($to, $subject, $message);
}
