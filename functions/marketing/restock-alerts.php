<?php
/**
 * Axiom Restock Alerts — TEST MODE ONLY
 *
 * Sends test restock emails only to cheapeptides@gmail.com.
 * Does NOT send to customers.
 * Does NOT send SMS.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test email receiver.
 */
function axiom_restock_test_email_to() {
    return 'cheapeptides@gmail.com';
}

/**
 * Manual test email endpoint.
 *
 * After uploading this file, visit:
 * /wp-admin/admin-post.php?action=axiom_restock_test_email
 *
 * This confirms whether WordPress/Zoho SMTP can send at all.
 */
add_action('admin_post_axiom_restock_test_email', 'axiom_restock_manual_test_email');

function axiom_restock_manual_test_email() {
    if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }

    $to = axiom_restock_test_email_to();

    $sent = wp_mail(
        $to,
        '[TEST] Axiom Restock Email System Works',
        "This is a manual test email from the Axiom restock alert system.\n\nIf you received this, WordPress email sending is working.\n\nCustomers were NOT emailed."
    );

    if ($sent) {
        wp_die('Test email sent to ' . esc_html($to) . '. Check inbox, spam, promotions, and all mail.');
    }

    wp_die('WordPress tried to send the test email, but wp_mail returned false. Your SMTP/Zoho mail setup is the issue.');
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
     * Your normal peptide products must be in WooCommerce category slug:
     * peptides
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
 * Main restock checker.
 */
function axiom_restock_check_product_stock($product_id, $source = 'unknown') {
    $product = wc_get_product($product_id);

    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    /**
     * Only products/variations with Manage Stock enabled.
     */
    if (!$product->managing_stock()) {
        return;
    }

    /**
     * Only allowed normal peptide products.
     */
    if (!axiom_restock_is_allowed_product($product)) {
        return;
    }

    $new_stock = $product->get_stock_quantity();

    if ($new_stock === null) {
        return;
    }

    $new_stock = (int) $new_stock;

    $last_stock_key = '_axiom_restock_last_known_qty';
    $old_stock_raw  = get_post_meta($product_id, $last_stock_key, true);

    /**
     * First time this file sees this product:
     * save current stock but do NOT send.
     *
     * This prevents fake mass alerts when you first install the code.
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
     * 0 -> 20 sends
     * 5 -> 30 sends
     * 30 -> 0 does not send
     * 30 -> 20 does not send
     */
    if ($new_stock <= $old_stock) {
        return;
    }

    /**
     * Never send if new stock is zero or below.
     */
    if ($new_stock <= 0) {
        return;
    }

    /**
     * Prevent duplicate emails if multiple WooCommerce hooks fire on the same save.
     */
    $cooldown_key  = '_axiom_restock_last_alert_time';
    $last_alert_at = (int) get_post_meta($product_id, $cooldown_key, true);

    if ($last_alert_at && (time() - $last_alert_at) < 10 * MINUTE_IN_SECONDS) {
        return;
    }

    update_post_meta($product_id, $cooldown_key, time());

    axiom_restock_send_test_email($product, $old_stock, $new_stock, $source);
}

/**
 * Hook 1:
 * Fires when WooCommerce directly changes stock.
 */
add_action('woocommerce_product_set_stock', 'axiom_restock_stock_object_hook', 20, 1);
add_action('woocommerce_variation_set_stock', 'axiom_restock_stock_object_hook', 20, 1);

function axiom_restock_stock_object_hook($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    axiom_restock_check_product_stock($product->get_id(), 'stock_object_hook');
}

/**
 * Hook 2:
 * Fallback for manually saving simple products in WooCommerce admin.
 */
add_action('woocommerce_update_product', 'axiom_restock_product_save_hook', 999, 1);

function axiom_restock_product_save_hook($product_id) {
    axiom_restock_check_product_stock($product_id, 'woocommerce_update_product');
}

/**
 * Hook 3:
 * Fallback for manually saving variations in WooCommerce admin.
 */
add_action('woocommerce_update_product_variation', 'axiom_restock_variation_save_hook', 999, 1);

function axiom_restock_variation_save_hook($variation_id) {
    axiom_restock_check_product_stock($variation_id, 'woocommerce_update_product_variation');
}

/**
 * Send test restock email only to cheapeptides@gmail.com.
 */
function axiom_restock_send_test_email($product, $old_stock, $new_stock, $source) {
    $product_id = $product->get_id();
    $parent_id  = $product->is_type('variation') ? $product->get_parent_id() : 0;
    $main_id    = $parent_id ?: $product_id;

    $product_name = axiom_restock_get_product_display_name($product);
    $product_url  = get_permalink($main_id);

    $to = axiom_restock_test_email_to();

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
    $message .= "This was sent only to " . $to . ".\n";
    $message .= "Customers were NOT emailed.\n";
    $message .= "SMS was NOT sent.\n\n";

    $message .= "A valid restock was detected.\n\n";

    $message .= "TRIGGER SOURCE\n";
    $message .= "-------------------------\n";
    $message .= $source . "\n\n";

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

    /**
     * Do NOT force a From header here.
     * Some Zoho SMTP setups reject emails when the From header
     * does not match the authenticated SMTP mailbox/alias.
     */
    wp_mail($to, $subject, $message);
}
