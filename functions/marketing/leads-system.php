<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==========================================
 * AXIOM LEADS SYSTEM
 * Stores popup emails, SMS numbers, checkout leads,
 * coupon codes, and allows CSV export.
 * ==========================================
 */


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
        ip_address VARCHAR(100) NULL,
        user_agent TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL,
        PRIMARY KEY (id),
        KEY email (email),
        KEY phone (phone),
        KEY source (source),
        KEY discount_code (discount_code)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('after_setup_theme', 'axiom_create_leads_table');
add_action('admin_init', 'axiom_create_leads_table');


/**
 * ==========================================
 * 2. SAFE COLUMN CHECKER
 * Adds missing columns if old table already exists.
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
    axiom_leads_maybe_add_column('ip_address', 'VARCHAR(100) NULL');
    axiom_leads_maybe_add_column('user_agent', 'TEXT NULL');
    axiom_leads_maybe_add_column('updated_at', 'DATETIME NULL');
}

add_action('admin_init', 'axiom_leads_ensure_columns');


/**
 * ==========================================
 * 3. CREATE UNIQUE ONE-TIME WOOCOMMERCE COUPON
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
 * 4. SAVE LEAD FUNCTION
 * Used by checkout and popup.
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

    $ip_address = '';
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip_address = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    }

    $user_agent = '';
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
    }

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

    $formats = array(
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%d',
        '%s',
        '%s',
        '%s',
        '%s',
    );

    /**
     * If email already exists, update it instead of creating messy duplicates.
     */
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

            return absint($existing_id);
        }
    }

    $inserted = $wpdb->insert($table, $data, $formats);

    if (!$inserted) {
        error_log('Axiom lead insert failed: ' . $wpdb->last_error);
        return false;
    }

    return absint($wpdb->insert_id);
}


/**
 * ==========================================
 * 5. POPUP AJAX SAVE
 * This matches your popup.js:
 * action=axiom_save_lead
 * ==========================================
 */
add_action('wp_ajax_axiom_save_lead', 'axiom_ajax_save_lead');
add_action('wp_ajax_nopriv_axiom_save_lead', 'axiom_ajax_save_lead');

function axiom_ajax_save_lead() {
    /**
     * Nonce is recommended, but this will still work if your JS forgot to pass it.
     * Once everything is confirmed working, you can make nonce required.
     */
    if (!empty($_POST['nonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['nonce']));

        if (!wp_verify_nonce($nonce, 'axiom_popup_capture')) {
            wp_send_json_error(array(
                'message' => 'Security check failed.',
            ));
        }
    }

    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';

    $discount_percent = isset($_POST['discount_percent'])
        ? absint($_POST['discount_percent'])
        : 10;

    if (!is_email($email)) {
        wp_send_json_error(array(
            'message' => 'Please enter a valid email.',
        ));
    }

    if ($discount_percent !== 15) {
        $discount_percent = 10;
    }

    if ($discount_percent === 15) {
        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) < 10) {
            wp_send_json_error(array(
                'message' => 'Please enter a valid phone number.',
            ));
        }
    }

    $coupon_code = axiom_generate_popup_coupon($email, $discount_percent);

    if (!$coupon_code) {
        wp_send_json_error(array(
            'message' => 'Could not create discount code.',
        ));
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
        wp_send_json_error(array(
            'message' => 'Lead could not be saved.',
        ));
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
 * 6. OLD POPUP AJAX COMPATIBILITY
 * Supports older JS using action=axiom_capture_lead
 * ==========================================
 */
add_action('wp_ajax_axiom_capture_lead', 'axiom_capture_lead');
add_action('wp_ajax_nopriv_axiom_capture_lead', 'axiom_capture_lead');

function axiom_capture_lead() {
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';

    if (empty($email) && empty($phone)) {
        wp_send_json_error(array(
            'message' => 'Missing email or phone.',
        ));
    }

    $lead_id = axiom_save_lead($email, $phone, 'popup');

    if (!$lead_id) {
        wp_send_json_error(array(
            'message' => 'Lead could not be saved.',
        ));
    }

    wp_send_json_success(array(
        'message' => 'Saved',
        'lead_id' => $lead_id,
    ));
}


/**
 * ==========================================
 * 7. SAVE EMAIL / PHONE ON CHECKOUT
 * ==========================================
 */
add_action('woocommerce_checkout_update_order_meta', function($order_id) {
    if (!function_exists('wc_get_order')) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $email = $order->get_billing_email();
    $phone = $order->get_billing_phone();
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
        array(
            'first_name' => $first_name,
        )
    );
});


/**
 * ==========================================
 * 8. EXPORT CSV
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
    } else {
        fputcsv($output, array(
            'id',
            'email',
            'phone',
            'first_name',
            'source',
            'cart_data',
            'status',
            'discount_code',
            'discount_percent',
            'ip_address',
            'user_agent',
            'created_at',
            'updated_at',
        ));
    }

    fclose($output);
    exit;
});


/**
 * ==========================================
 * 9. ADMIN PAGE
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
    ?>
    <div class="wrap">
        <h1>Axiom Leads</h1>

        <p>
            This stores popup emails, SMS numbers, checkout leads, and generated discount codes.
        </p>

        <p>
            <a href="<?php echo esc_url(admin_url('admin-post.php?action=axiom_export_leads')); ?>"
               class="button button-primary">
                Export Leads CSV
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
                                <?php
                                echo !empty($lead['discount_percent'])
                                    ? esc_html($lead['discount_percent']) . '%'
                                    : '—';
                                ?>
                            </td>
                            <td><?php echo !empty($lead['discount_code']) ? esc_html($lead['discount_code']) : '—'; ?></td>
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
