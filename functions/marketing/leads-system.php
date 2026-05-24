<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==========================================
 * AXIOM LEADS SYSTEM
 * Stores popup emails, SMS numbers, checkout leads,
 * coupon codes, CSV export, and live Omnisend sync.
 *
 * IMPORTANT:
 * The database columns still use "brevo_*" names so your
 * existing table does not break. The sync now goes to Omnisend.
 * ==========================================
 */

/**
 * ==========================================
 * OMNISEND LIVE SYNC SETTINGS
 * ==========================================
 */

if (!function_exists('axiom_omnisend_api_key')) {
    function axiom_omnisend_api_key() {
        if (defined('AXIOM_OMNISEND_API_KEY') && AXIOM_OMNISEND_API_KEY) {
            return trim((string) AXIOM_OMNISEND_API_KEY);
        }

        return trim((string) get_option('axiom_omnisend_api_key', ''));
    }
}

/**
 * Backward-compatible function name.
 * Other old code can still call axiom_brevo_api_key(),
 * but this now returns your Omnisend API key.
 */
if (!function_exists('axiom_brevo_api_key')) {
    function axiom_brevo_api_key() {
        return axiom_omnisend_api_key();
    }
}

function axiom_brevo_leads_list_id() {
    return 0;
}

function axiom_omnisend_sync_enabled() {
    return true;
}

/**
 * Backward-compatible function name.
 */
function axiom_brevo_sync_enabled() {
    return axiom_omnisend_sync_enabled();
}

/**
 * Format phone numbers for Omnisend.
 * Omnisend wants international format like +19262863779.
 */
function axiom_format_phone_for_brevo($phone) {
    $phone = trim((string) $phone);

    if ($phone === '') {
        return '';
    }

    $digits = preg_replace('/\D+/', '', $phone);

    if ($digits === '') {
        return '';
    }

    if (strlen($digits) === 10) {
        return '+1' . $digits;
    }

    if (strlen($digits) === 11 && substr($digits, 0, 1) === '1') {
        return '+' . $digits;
    }

    if (strpos($phone, '+') === 0 && strlen($digits) >= 10) {
        return '+' . $digits;
    }

    return '';
}

/**
 * ==========================================
 * 1. CREATE / UPDATE DATABASE TABLE
 * ==========================================
 */
function axiom_create_leads_table() {
    global $wpdb;

    $table   = $wpdb->prefix . 'axiom_leads';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(255) NULL,
        phone VARCHAR(50) NULL,
        first_name VARCHAR(100) NULL,
        source VARCHAR(100) DEFAULT 'unknown',
        cart_data LONGTEXT NULL,
        status VARCHAR(50) DEFAULT 'active',
        discount_code VARCHAR(100) NULL,
        discount_percent INT NULL,
        brevo_synced TINYINT(1) DEFAULT 0,
        brevo_last_sync DATETIME NULL,
        brevo_error TEXT NULL,
        ip_address VARCHAR(100) NULL,
        user_agent TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL,
        PRIMARY KEY (id),
        KEY email (email),
        KEY phone (phone),
        KEY source (source),
        KEY discount_code (discount_code),
        KEY brevo_synced (brevo_synced)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('after_setup_theme', 'axiom_create_leads_table');
add_action('admin_init', 'axiom_create_leads_table');

/**
 * ==========================================
 * 2. SAFE COLUMN CHECKER
 * ==========================================
 */
function axiom_leads_maybe_add_column($column_name, $definition) {
    global $wpdb;

    $table = $wpdb->prefix . 'axiom_leads';
    $column_name = sanitize_key($column_name);

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SHOW COLUMNS FROM {$table} LIKE %s",
            $column_name
        )
    );

    if (!$exists) {
        $wpdb->query("ALTER TABLE {$table} ADD {$column_name} {$definition}");
    }
}

function axiom_leads_ensure_columns() {
    axiom_leads_maybe_add_column('discount_code', 'VARCHAR(100) NULL');
    axiom_leads_maybe_add_column('discount_percent', 'INT NULL');
    axiom_leads_maybe_add_column('brevo_synced', 'TINYINT(1) DEFAULT 0');
    axiom_leads_maybe_add_column('brevo_last_sync', 'DATETIME NULL');
    axiom_leads_maybe_add_column('brevo_error', 'TEXT NULL');
    axiom_leads_maybe_add_column('ip_address', 'VARCHAR(100) NULL');
    axiom_leads_maybe_add_column('user_agent', 'TEXT NULL');
    axiom_leads_maybe_add_column('updated_at', 'DATETIME NULL');
}

add_action('admin_init', 'axiom_leads_ensure_columns');

/**
 * ==========================================
 * 3. OMNISEND LIVE SYNC
 * ==========================================
 *
 * Popup leads = subscribed marketing contacts.
 * Checkout leads = nonSubscribed contacts unless they came from popup.
 *
 * This lowers risk because checkout customers are NOT automatically
 * opted into marketing unless they clearly submitted the popup.
 */
function axiom_sync_lead_to_brevo($lead_id, $email, $phone = '', $first_name = '', $source = 'unknown', $discount_code = '', $discount_percent = null) {
    if (!axiom_omnisend_sync_enabled()) {
        return false;
    }

    $api_key = axiom_omnisend_api_key();

    if (empty($api_key)) {
        axiom_mark_brevo_sync_result($lead_id, false, 'Missing Omnisend API key.');
        return false;
    }

    $email = sanitize_email($email);

    if (empty($email) || !is_email($email)) {
        axiom_mark_brevo_sync_result($lead_id, false, 'Missing or invalid email.');
        return false;
    }

    $phone            = sanitize_text_field($phone);
    $first_name       = sanitize_text_field($first_name);
    $source           = sanitize_text_field($source);
    $discount_code    = sanitize_text_field($discount_code);
    $discount_percent = !is_null($discount_percent) ? absint($discount_percent) : null;

    $is_popup_optin = ($source === 'popup');

    $email_status = $is_popup_optin ? 'subscribed' : 'nonSubscribed';

    $tags = array(
        'source: axiom website',
        'axiom_lead',
    );

    if ($is_popup_optin) {
        $tags[] = 'source: axiom popup';
        $tags[] = 'popup_subscriber';
    } else {
        $tags[] = 'source: ' . $source;
    }

    if (!empty($discount_percent)) {
        $tags[] = 'discount_' . $discount_percent;
    }

    if (!empty($discount_code)) {
        $tags[] = 'has_coupon_code';
    }

    $custom_properties = array(
        'source'           => $source,
        'discount_code'    => $discount_code,
        'discount_percent' => $discount_percent,
        'axiom_popup'      => $is_popup_optin ? 'yes' : 'no',
    );

    $formatted_phone = axiom_format_phone_for_brevo($phone);

    if (!empty($formatted_phone)) {
        $custom_properties['phone'] = $formatted_phone;
    }

    $identifier = array(
        'type' => 'email',
        'id'   => $email,
        'channels' => array(
            'email' => array(
                'status'     => $email_status,
                'statusDate' => gmdate('c'),
            ),
        ),
    );

    $payload = array(
        'identifiers'      => array($identifier),
        'tags'             => $tags,
        'customProperties' => $custom_properties,
    );

    if (!empty($first_name)) {
        $payload['firstName'] = $first_name;
    }

    $response = wp_remote_post(
        'https://api.omnisend.com/api/contacts',
        array(
            'timeout' => 20,
            'headers' => array(
                'Authorization'    => 'Omnisend-API-Key ' . $api_key,
                'Omnisend-Version' => '2026-03-15',
                'Content-Type'     => 'application/json',
                'Accept'           => 'application/json',
            ),
            'body' => wp_json_encode($payload),
        )
    );

    if (is_wp_error($response)) {
        axiom_mark_brevo_sync_result($lead_id, false, $response->get_error_message());
        return false;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if (in_array($code, array(200, 201, 202, 204), true)) {
        axiom_mark_brevo_sync_result($lead_id, true, '');
        return true;
    }

    axiom_mark_brevo_sync_result($lead_id, false, 'Omnisend HTTP ' . $code . ': ' . $body);
    return false;
}

function axiom_mark_brevo_sync_result($lead_id, $success, $error = '') {
    global $wpdb;

    $lead_id = absint($lead_id);

    if (!$lead_id) {
        return;
    }

    $table = $wpdb->prefix . 'axiom_leads';

    $wpdb->update(
        $table,
        array(
            'brevo_synced'    => $success ? 1 : 0,
            'brevo_last_sync' => current_time('mysql'),
            'brevo_error'     => $success ? '' : sanitize_textarea_field($error),
            'updated_at'      => current_time('mysql'),
        ),
        array('id' => $lead_id),
        array('%d', '%s', '%s', '%s'),
        array('%d')
    );
}

/**
 * Manual sync existing unsynced leads.
 */
function axiom_sync_unsynced_leads_to_omnisend_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    $table = $wpdb->prefix . 'axiom_leads';

    $leads = $wpdb->get_results(
        "SELECT * FROM {$table}
         WHERE email IS NOT NULL
         AND email != ''
         AND (brevo_synced = 0 OR brevo_synced IS NULL)
         ORDER BY id DESC
         LIMIT 25",
        ARRAY_A
    );

    $synced = 0;
    $failed = 0;

    foreach ($leads as $lead) {
        $ok = axiom_sync_lead_to_brevo(
            $lead['id'],
            $lead['email'],
            $lead['phone'],
            $lead['first_name'],
            $lead['source'],
            $lead['discount_code'],
            $lead['discount_percent']
        );

        if ($ok) {
            $synced++;
        } else {
            $failed++;
        }
    }

    wp_safe_redirect(
        admin_url('admin.php?page=axiom-leads&omnisend_synced=' . absint($synced) . '&omnisend_failed=' . absint($failed))
    );
    exit;
}

add_action('admin_post_axiom_sync_leads_to_brevo', 'axiom_sync_unsynced_leads_to_omnisend_handler');
add_action('admin_post_axiom_sync_leads_to_omnisend', 'axiom_sync_unsynced_leads_to_omnisend_handler');

/**
 * ==========================================
 * 4. CREATE UNIQUE ONE-TIME WOOCOMMERCE COUPON
 * ==========================================
 */
function axiom_generate_popup_coupon($email, $discount_percent = 10) {
    if (!function_exists('wc_get_coupon_id_by_code') || !class_exists('WC_Coupon')) {
        return false;
    }

    $email = sanitize_email($email);

    if (!is_email($email)) {
        return false;
    }

    $discount_percent = absint($discount_percent);

    if ($discount_percent !== 15) {
        $discount_percent = 10;
    }

    $prefix = ($discount_percent === 15) ? 'AXIOM15-' : 'WELCOME10-';

    do {
        $random = strtoupper(wp_generate_password(6, false, false));
        $code   = $prefix . $random;
    } while (wc_get_coupon_id_by_code($code));

    $coupon = new WC_Coupon();
    $coupon->set_code($code);
    $coupon->set_discount_type('percent');
    $coupon->set_amount($discount_percent);
    $coupon->set_individual_use(true);
    $coupon->set_usage_limit(1);
    $coupon->set_usage_limit_per_user(1);
    $coupon->set_email_restrictions(array($email));
    $coupon->set_description('Axiom popup capture coupon for ' . $email);
    $coupon->set_date_expires(strtotime('+30 days'));
    $coupon->save();

    return $code;
}

/**
 * ==========================================
 * 5. SAVE LEAD FUNCTION
 * ==========================================
 */
function axiom_save_lead($email = '', $phone = '', $source = 'unknown', $cart_data = '', $extra = array()) {
    global $wpdb;

    axiom_create_leads_table();
    axiom_leads_ensure_columns();

    $table = $wpdb->prefix . 'axiom_leads';

    $email = sanitize_email($email);
    $phone = sanitize_text_field($phone);

    if (empty($email) && empty($phone)) {
        return false;
    }

    $first_name       = isset($extra['first_name']) ? sanitize_text_field($extra['first_name']) : '';
    $discount_code    = isset($extra['discount_code']) ? sanitize_text_field($extra['discount_code']) : '';
    $discount_percent = isset($extra['discount_percent']) ? absint($extra['discount_percent']) : null;

    $ip_address = !empty($_SERVER['REMOTE_ADDR'])
        ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
        : '';

    $user_agent = !empty($_SERVER['HTTP_USER_AGENT'])
        ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))
        : '';

    $data = array(
        'email'            => $email,
        'phone'            => $phone,
        'first_name'       => $first_name,
        'source'           => sanitize_text_field($source),
        'cart_data'        => maybe_serialize($cart_data),
        'status'           => 'active',
        'discount_code'    => $discount_code,
        'discount_percent' => $discount_percent,
        'ip_address'       => $ip_address,
        'user_agent'       => $user_agent,
        'created_at'       => current_time('mysql'),
        'updated_at'       => current_time('mysql'),
    );

    $formats = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s');

    if (!empty($email)) {
        $existing_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table} WHERE email = %s ORDER BY id DESC LIMIT 1",
                $email
            )
        );

        if ($existing_id) {
            unset($data['created_at']);
            array_pop($formats);

            $wpdb->update(
                $table,
                $data,
                array('id' => absint($existing_id)),
                $formats,
                array('%d')
            );

            $lead_id = absint($existing_id);

            axiom_sync_lead_to_brevo(
                $lead_id,
                $email,
                $phone,
                $first_name,
                $source,
                $discount_code,
                $discount_percent
            );

            return $lead_id;
        }
    }

    $inserted = $wpdb->insert($table, $data, $formats);

    if (!$inserted) {
        error_log('Axiom lead insert failed: ' . $wpdb->last_error);
        return false;
    }

    $lead_id = absint($wpdb->insert_id);

    axiom_sync_lead_to_brevo(
        $lead_id,
        $email,
        $phone,
        $first_name,
        $source,
        $discount_code,
        $discount_percent
    );

    return $lead_id;
}

/**
 * ==========================================
 * 6. POPUP AJAX SAVE
 * ==========================================
 */
add_action('wp_ajax_axiom_save_lead', 'axiom_ajax_save_lead');
add_action('wp_ajax_nopriv_axiom_save_lead', 'axiom_ajax_save_lead');

function axiom_ajax_save_lead() {
    if (!empty($_POST['nonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['nonce']));

        if (!wp_verify_nonce($nonce, 'axiom_popup_capture')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
    }

    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';

    $discount_percent = isset($_POST['discount_percent'])
        ? absint($_POST['discount_percent'])
        : 10;

    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email.'));
    }

    if ($discount_percent !== 15) {
        $discount_percent = 10;
    }

    if ($discount_percent === 15) {
        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) < 10) {
            wp_send_json_error(array('message' => 'Please enter a valid phone number.'));
        }
    }

    $coupon_code = axiom_generate_popup_coupon($email, $discount_percent);

    if (!$coupon_code) {
        wp_send_json_error(array('message' => 'Could not create discount code.'));
    }

    $lead_id = axiom_save_lead(
        $email,
        $phone,
        'popup',
        '',
        array(
            'discount_code'    => $coupon_code,
            'discount_percent' => $discount_percent,
        )
    );

    if (!$lead_id) {
        wp_send_json_error(array('message' => 'Lead could not be saved.'));
    }

    wp_send_json_success(array(
        'message'          => 'Saved',
        'lead_id'          => $lead_id,
        'code'             => $coupon_code,
        'discount_percent' => $discount_percent,
    ));
}

/**
 * ==========================================
 * 7. OLD POPUP AJAX COMPATIBILITY
 * ==========================================
 */
add_action('wp_ajax_axiom_capture_lead', 'axiom_capture_lead');
add_action('wp_ajax_nopriv_axiom_capture_lead', 'axiom_capture_lead');

function axiom_capture_lead() {
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';

    if (empty($email) && empty($phone)) {
        wp_send_json_error(array('message' => 'Missing email or phone.'));
    }

    $lead_id = axiom_save_lead($email, $phone, 'popup');

    if (!$lead_id) {
        wp_send_json_error(array('message' => 'Lead could not be saved.'));
    }

    wp_send_json_success(array(
        'message' => 'Saved',
        'lead_id' => $lead_id,
    ));
}

/**
 * ==========================================
 * 8. SAVE EMAIL / PHONE ON CHECKOUT
 * ==========================================
 *
 * This stores checkout leads, but Omnisend sync marks them as nonSubscribed.
 * Do not automatically subscribe checkout customers unless you add a real
 * marketing consent checkbox later.
 */
add_action('woocommerce_checkout_update_order_meta', function($order_id) {
    if (!function_exists('wc_get_order')) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $email      = $order->get_billing_email();
    $phone      = $order->get_billing_phone();
    $first_name = $order->get_billing_first_name();

    $cart_items = array();

    foreach ($order->get_items() as $item) {
        $cart_items[] = array(
            'name'     => $item->get_name(),
            'quantity' => $item->get_quantity(),
            'total'    => $item->get_total(),
        );
    }

    axiom_save_lead(
        $email,
        $phone,
        'checkout',
        $cart_items,
        array('first_name' => $first_name)
    );
});

/**
 * ==========================================
 * 9. EXPORT CSV
 * ==========================================
 */
add_action('admin_post_axiom_export_leads', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    $table = $wpdb->prefix . 'axiom_leads';

    $results = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC", ARRAY_A);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=axiom-leads-' . date('Y-m-d') . '.csv');

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
 * 10. ADMIN PAGE
 * ==========================================
 */
add_action('admin_menu', function() {
    add_menu_page(
        'Axiom Leads',
        'Axiom Leads',
        'manage_options',
        'axiom-leads',
        'axiom_leads_page',
        'dashicons-email-alt2',
        56
    );
});

function axiom_leads_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;

    $table = $wpdb->prefix . 'axiom_leads';

    $leads = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC LIMIT 100", ARRAY_A);

    if (isset($_GET['omnisend_synced']) || isset($_GET['omnisend_failed'])) {
        echo '<div class="notice notice-info"><p>Omnisend sync complete. Synced: ' . esc_html(absint($_GET['omnisend_synced'] ?? 0)) . ' | Failed: ' . esc_html(absint($_GET['omnisend_failed'] ?? 0)) . '</p></div>';
    }

    if (isset($_GET['brevo_synced']) || isset($_GET['brevo_failed'])) {
        echo '<div class="notice notice-info"><p>Omnisend sync complete. Synced: ' . esc_html(absint($_GET['brevo_synced'] ?? 0)) . ' | Failed: ' . esc_html(absint($_GET['brevo_failed'] ?? 0)) . '</p></div>';
    }

    $has_api_key = !empty(axiom_omnisend_api_key());
    ?>
    <div class="wrap">
        <h1>Axiom Leads</h1>

        <p>
            This stores popup emails, SMS numbers, checkout leads, generated discount codes, and syncs popup subscribers to Omnisend.
        </p>

        <?php if ($has_api_key) : ?>
            <div class="notice notice-success inline">
                <p><strong>Omnisend API key:</strong> Saved in WordPress.</p>
            </div>
        <?php else : ?>
            <div class="notice notice-error inline">
                <p><strong>Omnisend API key missing.</strong> Save your key in the <code>axiom_omnisend_api_key</code> WordPress option before syncing.</p>
            </div>
        <?php endif; ?>

        <p>
            <a href="<?php echo esc_url(admin_url('admin-post.php?action=axiom_export_leads')); ?>" class="button button-primary">
                Export Leads CSV
            </a>

            <a href="<?php echo esc_url(admin_url('admin-post.php?action=axiom_sync_leads_to_omnisend')); ?>" class="button">
                Sync Unsynced Leads to Omnisend
            </a>
        </p>

        <hr>

        <h2>Recent Leads</h2>

        <?php if (!empty($leads)) : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Source</th>
                        <th>Discount</th>
                        <th>Code</th>
                        <th>Omnisend</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($leads as $lead) : ?>
                        <tr>
                            <td><?php echo esc_html($lead['id']); ?></td>
                            <td><?php echo esc_html($lead['email']); ?></td>
                            <td><?php echo esc_html($lead['phone']); ?></td>
                            <td><?php echo esc_html($lead['source']); ?></td>
                            <td>
                                <?php echo !empty($lead['discount_percent']) ? esc_html($lead['discount_percent']) . '%' : '—'; ?>
                            </td>
                            <td><?php echo !empty($lead['discount_code']) ? esc_html($lead['discount_code']) : '—'; ?></td>
                            <td>
                                <?php if (!empty($lead['brevo_synced'])) : ?>
                                    ✅ Synced
                                <?php else : ?>
                                    ❌ Not synced
                                    <?php if (!empty($lead['brevo_error'])) : ?>
                                        <br><small><?php echo esc_html(wp_trim_words($lead['brevo_error'], 16)); ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($lead['status']); ?></td>
                            <td><?php echo esc_html($lead['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No leads saved yet.</p>
        <?php endif; ?>
    </div>
    <?php
}
