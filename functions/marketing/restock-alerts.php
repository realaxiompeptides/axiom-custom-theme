<?php
/**
 * Axiom Restock Alerts — TEST MODE ONLY
 *
 * Sends valid restock test emails only to cheapeptides@gmail.com.
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
 * Debug mode.
 *
 * false = do NOT send skipped/debug emails.
 * true  = email you every skip reason. Only turn on temporarily.
 */
function axiom_restock_debug_enabled() {
    return false;
}

/**
 * Manual test email endpoint.
 *
 * Visit:
 * /wp-admin/admin-post.php?action=axiom_restock_test_email
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
        "This is a manual test email from the Axiom restock alert system.\n\nIf you received this, WordPress email sending is working.\n\nCustomers were NOT emailed.\nSMS was NOT sent."
    );

    if ($sent) {
        wp_die('Test email sent to ' . esc_html($to) . '. Check inbox, spam, promotions, and all mail.');
    }

    wp_die('WordPress tried to send the test email, but wp_mail returned false. Your SMTP/Zoho mail setup is the issue.');
}

/**
 * Reset one product's restock memory/cooldown.
 *
 * Visit:
 * /wp-admin/admin-post.php?action=axiom_restock_force_test&product_id=123
 */
add_action('admin_post_axiom_restock_force_test', 'axiom_restock_force_test');

function axiom_restock_force_test() {
    if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }

    $product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;

    if (!$product_id) {
        wp_die('Missing product_id.');
    }

    $product = wc_get_product($product_id);

    if (!$product || !is_a($product, 'WC_Product')) {
        wp_die('Invalid product.');
    }

    delete_post_meta($product_id, '_axiom_restock_last_known_qty');
    delete_post_meta($product_id, '_axiom_restock_last_alert_time');

    wp_die(
        'Restock memory reset for product ID ' . esc_html($product_id) . '. ' .
        'Now set stock to a lower number and save. Then set stock higher and save again. ' .
        'Only valid restock test emails will send to ' . esc_html(axiom_restock_test_email_to()) . '.'
    );
}

/**
 * Show product eligibility info without sending an email.
 *
 * Visit:
 * /wp-admin/admin-post.php?action=axiom_restock_debug_product&product_id=123
 */
add_action('admin_post_axiom_restock_debug_product', 'axiom_restock_debug_product_endpoint');

function axiom_restock_debug_product_endpoint() {
    if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }

    $product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;

    if (!$product_id) {
        wp_die('Missing product_id.');
    }

    $product = wc_get_product($product_id);

    if (!$product || !is_a($product, 'WC_Product')) {
        wp_die('Invalid product.');
    }

    $debug = axiom_restock_get_product_debug_report($product, 'manual_debug_endpoint');

    wp_die('<pre>' . esc_html($debug) . '</pre>');
}

/**
 * Check if product is allowed.
 */
function axiom_restock_is_allowed_product($product) {
    return axiom_restock_get_disallow_reason($product) === '';
}

/**
 * Return blank string if allowed, otherwise exact reason it is blocked.
 */
function axiom_restock_get_disallow_reason($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return 'Invalid product object.';
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

    if (in_array($product_slug, $excluded_slugs, true)) {
        return 'Blocked because product slug is excluded: ' . $product_slug;
    }

    if (has_term($excluded_categories, 'product_cat', $check_id)) {
        return 'Blocked because product is in an excluded category.';
    }

    if (!has_term($allowed_categories, 'product_cat', $check_id)) {
        return 'Blocked because product is not in allowed category slug: peptides.';
    }

    return '';
}

/**
 * Product display name.
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
 * Debug report.
 */
function axiom_restock_get_product_debug_report($product, $source = 'unknown') {
    $product_id = $product->get_id();
    $parent_id  = $product->is_type('variation') ? $product->get_parent_id() : 0;
    $check_id   = $parent_id ?: $product_id;

    $terms = get_the_terms($check_id, 'product_cat');
    $cats  = array();

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $cats[] = $term->name . ' (' . $term->slug . ')';
        }
    }

    $report  = "AXIOM RESTOCK DEBUG REPORT\n";
    $report .= "-------------------------\n";
    $report .= "Source: " . $source . "\n";
    $report .= "Product: " . axiom_restock_get_product_display_name($product) . "\n";
    $report .= "Product ID: " . $product_id . "\n";
    $report .= "Parent ID: " . $parent_id . "\n";
    $report .= "Check ID: " . $check_id . "\n";
    $report .= "Type: " . $product->get_type() . "\n";
    $report .= "Manage stock: " . ($product->managing_stock() ? 'YES' : 'NO') . "\n";
    $report .= "Current stock: " . var_export($product->get_stock_quantity(), true) . "\n";
    $report .= "Stock status: " . $product->get_stock_status() . "\n";
    $report .= "Categories: " . (!empty($cats) ? implode(', ', $cats) : 'none') . "\n";
    $report .= "Last known qty meta: " . var_export(get_post_meta($product_id, '_axiom_restock_last_known_qty', true), true) . "\n";
    $report .= "Last alert time meta: " . var_export(get_post_meta($product_id, '_axiom_restock_last_alert_time', true), true) . "\n";

    $reason = axiom_restock_get_disallow_reason($product);

    $report .= "Allowed product: " . ($reason === '' ? 'YES' : 'NO') . "\n";

    if ($reason !== '') {
        $report .= "Blocked reason: " . $reason . "\n";
    }

    return $report;
}

/**
 * Main restock checker.
 */
function axiom_restock_check_product_stock($product_id, $source = 'unknown') {
    $product = wc_get_product($product_id);

    if (!$product || !is_a($product, 'WC_Product')) {
        axiom_restock_send_debug_email('Skipped restock check — invalid product ID', 'Product ID: ' . $product_id . "\nSource: " . $source);
        return;
    }

    if (!$product->managing_stock()) {
        axiom_restock_send_skip_debug($product, $source, 'Product does not have Manage stock enabled.');
        return;
    }

    $disallow_reason = axiom_restock_get_disallow_reason($product);

    if ($disallow_reason !== '') {
        axiom_restock_send_skip_debug($product, $source, $disallow_reason);
        return;
    }

    $new_stock = $product->get_stock_quantity();

    if ($new_stock === null) {
        axiom_restock_send_skip_debug($product, $source, 'Stock quantity is null.');
        return;
    }

    $new_stock = (int) $new_stock;

    $last_stock_key = '_axiom_restock_last_known_qty';
    $old_stock_raw  = get_post_meta($product_id, $last_stock_key, true);

    if ($old_stock_raw === '') {
        update_post_meta($product_id, $last_stock_key, $new_stock);
        axiom_restock_send_skip_debug($product, $source, 'First time seeing product. Saved baseline stock only. Increase stock again to trigger alert.');
        return;
    }

    $old_stock = (int) $old_stock_raw;

    update_post_meta($product_id, $last_stock_key, $new_stock);

    if ($new_stock <= $old_stock) {
        axiom_restock_send_skip_debug($product, $source, 'Stock did not increase. Old stock: ' . $old_stock . '. New stock: ' . $new_stock . '.');
        return;
    }

    if ($new_stock <= 0) {
        axiom_restock_send_skip_debug($product, $source, 'New stock is zero or below.');
        return;
    }

    $cooldown_key  = '_axiom_restock_last_alert_time';
    $last_alert_at = (int) get_post_meta($product_id, $cooldown_key, true);

    if ($last_alert_at && (time() - $last_alert_at) < 10 * MINUTE_IN_SECONDS) {
        axiom_restock_send_skip_debug($product, $source, 'Cooldown active. Prevented duplicate alert within 10 minutes.');
        return;
    }

    update_post_meta($product_id, $cooldown_key, time());

    axiom_restock_send_test_email($product, $old_stock, $new_stock, $source);
}

/**
 * Hooks.
 */
add_action('woocommerce_product_set_stock', 'axiom_restock_stock_object_hook', 20, 1);
add_action('woocommerce_variation_set_stock', 'axiom_restock_stock_object_hook', 20, 1);

function axiom_restock_stock_object_hook($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    axiom_restock_check_product_stock($product->get_id(), 'stock_object_hook');
}

add_action('woocommerce_update_product', 'axiom_restock_product_save_hook', 999, 1);

function axiom_restock_product_save_hook($product_id) {
    axiom_restock_check_product_stock($product_id, 'woocommerce_update_product');
}

add_action('woocommerce_update_product_variation', 'axiom_restock_variation_save_hook', 999, 1);

function axiom_restock_variation_save_hook($variation_id) {
    axiom_restock_check_product_stock($variation_id, 'woocommerce_update_product_variation');
}

/**
 * Skip/debug email.
 */
function axiom_restock_send_skip_debug($product, $source, $reason) {
    if (!axiom_restock_debug_enabled()) {
        return;
    }

    $message  = "A restock check ran, but no restock email was sent.\n\n";
    $message .= "REASON\n";
    $message .= "-------------------------\n";
    $message .= $reason . "\n\n";
    $message .= axiom_restock_get_product_debug_report($product, $source);

    axiom_restock_send_debug_email('[DEBUG] Axiom Restock Skipped: ' . axiom_restock_get_product_display_name($product), $message);
}

function axiom_restock_send_debug_email($subject, $message) {
    if (!axiom_restock_debug_enabled()) {
        return;
    }

    wp_mail(axiom_restock_test_email_to(), $subject, $message);
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

    $customer_subject = 'Inventory Update: ' . $product_name;

    $customer_body = $product_name . " inventory has been updated at Axiom Research.\n\n";
    $customer_body .= "Current available stock: " . $new_stock . " units.\n\n";
    $customer_body .= "Availability may change quickly.\n\n";
    $customer_body .= "View product:\n";
    $customer_body .= $product_url . "\n\n";
    $customer_body .= "21+ only. Research use only. Not for human consumption.\n";
    $customer_body .= "Not intended to diagnose, treat, cure, or prevent any disease.\n\n";
    $customer_body .= "Axiom Research\n";
    $customer_body .= "Ships from California\n";
    $customer_body .= home_url();

    $sms_body = 'Axiom Research: Inventory updated for ' . $product_name . '. Limited quantity may be available. 21+ • Research Use Only. Reply STOP to opt out.';

    $message  = "TEST MODE ONLY\n";
    $message .= "This was sent only to " . $to . ".\n";
    $message .= "Customers were NOT emailed.\n";
    $message .= "SMS was NOT sent.\n\n";

    $message .= "A valid stock increase/restock was detected.\n\n";

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
    $message .= "After testing, we can switch this to real customer sends.\n";

    wp_mail($to, $subject, $message);
}
