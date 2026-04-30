<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================================
 * 1. CREATE DATABASE TABLE
 * ==========================================
 */
function axiom_create_leads_table() {
    global $wpdb;

    $table = $wpdb->prefix . 'axiom_leads';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NULL,
        phone VARCHAR(30) NULL,
        first_name VARCHAR(100) NULL,
        source VARCHAR(100) DEFAULT 'unknown',
        cart_data LONGTEXT NULL,
        status VARCHAR(50) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'axiom_create_leads_table');


/**
 * ==========================================
 * 2. SAVE LEAD FUNCTION
 * ==========================================
 */
function axiom_save_lead($email = '', $phone = '', $source = 'unknown', $cart_data = '') {
    global $wpdb;

    $table = $wpdb->prefix . 'axiom_leads';

    if (empty($email) && empty($phone)) return;

    $wpdb->insert($table, [
        'email'      => sanitize_email($email),
        'phone'      => sanitize_text_field($phone),
        'source'     => sanitize_text_field($source),
        'cart_data'  => maybe_serialize($cart_data),
        'status'     => 'active',
    ]);
}


/**
 * ==========================================
 * 3. SAVE EMAIL ON CHECKOUT (AUTOMATIC)
 * ==========================================
 */
add_action('woocommerce_checkout_update_order_meta', function($order_id) {
    $order = wc_get_order($order_id);

    if (!$order) return;

    $email = $order->get_billing_email();
    $phone = $order->get_billing_phone();

    $cart_items = [];

    foreach ($order->get_items() as $item) {
        $cart_items[] = $item->get_name();
    }

    axiom_save_lead($email, $phone, 'checkout', $cart_items);
});


/**
 * ==========================================
 * 4. SIMPLE AJAX LEAD CAPTURE (POPUP READY)
 * ==========================================
 */
add_action('wp_ajax_axiom_capture_lead', 'axiom_capture_lead');
add_action('wp_ajax_nopriv_axiom_capture_lead', 'axiom_capture_lead');

function axiom_capture_lead() {
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    axiom_save_lead($email, $phone, 'popup');

    wp_send_json_success(['message' => 'Saved']);
}


/**
 * ==========================================
 * 5. EXPORT CSV (ADMIN ONLY)
 * ==========================================
 */
add_action('admin_post_axiom_export_leads', function() {

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'axiom_leads';

    $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=axiom-leads.csv');

    $output = fopen('php://output', 'w');

    if (!empty($results)) {
        fputcsv($output, array_keys($results[0]));

        foreach ($results as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
});


/**
 * ==========================================
 * 6. ADD EXPORT BUTTON IN ADMIN
 * ==========================================
 */
add_action('admin_menu', function() {
    add_menu_page(
        'Axiom Leads',
        'Axiom Leads',
        'manage_options',
        'axiom-leads',
        'axiom_leads_page'
    );
});

function axiom_leads_page() {
    ?>
    <div class="wrap">
        <h1>Axiom Leads</h1>

        <a href="<?php echo admin_url('admin-post.php?action=axiom_export_leads'); ?>" 
           class="button button-primary">
           Export Leads CSV
        </a>

        <p style="margin-top:15px;">Download all saved emails + phone numbers.</p>
    </div>
    <?php
}
