<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =========================================================
 * Axiom Affiliate Partner Setup
 *
 * Handles:
 * - Saving payment preference from registration
 * - Saving Zelle contact
 * - Saving requested partner code
 * - Creating WooCommerce coupon after affiliate approval
 * - Saving/displaying partner code and referral link
 * =========================================================
 */

define('AXIOM_AFFILIATE_COUPON_PERCENT', 10);

/**
 * Normalize plain text.
 */
function axiom_affiliate_clean_text($value) {
    return sanitize_text_field(wp_unslash((string) $value));
}

/**
 * Normalize partner coupon code.
 */
function axiom_affiliate_clean_partner_code($value) {
    $value = strtoupper(wp_unslash((string) $value));
    $value = preg_replace('/[^A-Z0-9]/', '', $value);
    $value = substr($value, 0, 18);

    return $value;
}

/**
 * Reserved coupon codes that affiliates cannot claim.
 */
function axiom_affiliate_reserved_codes() {
    return array(
        'AXIOM',
        'AXIOM10',
        'AXIOM15',
        'WELCOME10',
        'WELCOME15',
        'FREE',
        'FREEBIE',
        '100OFF',
        'ADMIN',
        'SUPPORT',
        'TEST',
        'COUPON',
        'DISCOUNT',
        'SALE',
        'PEPTIDE',
        'PEPTIDES',
    );
}

/**
 * Get a submitted POST value by a preferred exact key,
 * with fallback search by key name.
 */
function axiom_affiliate_get_post_value_by_key($preferred_keys = array()) {
    foreach ($preferred_keys as $key) {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
    }

    foreach ($_POST as $key => $value) {
        if (!is_string($key)) {
            continue;
        }

        $key_lower = strtolower($key);

        foreach ($preferred_keys as $preferred_key) {
            $preferred_key_lower = strtolower($preferred_key);

            if (strpos($key_lower, $preferred_key_lower) !== false) {
                return $value;
            }
        }
    }

    return '';
}

/**
 * Detect payment preference from POST.
 */
function axiom_affiliate_get_submitted_payment_preference() {
    $value = axiom_affiliate_get_post_value_by_key(array(
        'axiom_payment_preference',
        'payment_preference',
        'slicewp_payment_preference',
    ));

    if (is_array($value)) {
        $value = implode(' ', array_map('sanitize_text_field', wp_unslash($value)));
    }

    $value = strtolower(axiom_affiliate_clean_text($value));

    if (strpos($value, 'store') !== false) {
        return 'store_credit';
    }

    if (
        strpos($value, 'zelle') !== false ||
        strpos($value, 'manual') !== false ||
        strpos($value, 'bank') !== false
    ) {
        return 'manual';
    }

    /**
     * Fallback: scan all POST values.
     */
    foreach ($_POST as $post_value) {
        if (is_array($post_value)) {
            $post_value = implode(' ', array_map('sanitize_text_field', wp_unslash($post_value)));
        }

        $text = strtolower(axiom_affiliate_clean_text($post_value));

        if (strpos($text, 'store credit') !== false) {
            return 'store_credit';
        }

        if (
            strpos($text, 'manual / zelle payout') !== false ||
            strpos($text, 'zelle') !== false ||
            strpos($text, 'bank deposit') !== false
        ) {
            return 'manual';
        }
    }

    return '';
}

/**
 * Detect Zelle contact from POST.
 */
function axiom_affiliate_get_submitted_zelle_contact() {
    $value = axiom_affiliate_get_post_value_by_key(array(
        'axiom_zelle_contact',
        'zelle_email_or_phone',
        'zelle',
        'zelle_contact',
    ));

    if (is_array($value)) {
        $value = implode(' ', array_map('sanitize_text_field', wp_unslash($value)));
    }

    return axiom_affiliate_clean_text($value);
}

/**
 * Detect requested partner code from POST.
 */
function axiom_affiliate_get_submitted_partner_code() {
    $value = axiom_affiliate_get_post_value_by_key(array(
        'axiom_partner_code',
        'partner_code',
        'your_partner_code',
        'coupon_code',
    ));

    if (is_array($value)) {
        $value = implode(' ', array_map('sanitize_text_field', wp_unslash($value)));
    }

    return axiom_affiliate_clean_partner_code($value);
}

/**
 * Save submitted affiliate preferences to user meta.
 */
function axiom_affiliate_save_registration_meta($user_id) {
    if (!$user_id) {
        return;
    }

    $payment_preference = axiom_affiliate_get_submitted_payment_preference();
    $zelle_contact      = axiom_affiliate_get_submitted_zelle_contact();
    $partner_code       = axiom_affiliate_get_submitted_partner_code();

    if ($payment_preference) {
        update_user_meta($user_id, 'axiom_affiliate_payment_preference', $payment_preference);
    }

    if ($zelle_contact) {
        update_user_meta($user_id, 'axiom_affiliate_zelle_contact', $zelle_contact);
    }

    if ($partner_code) {
        update_user_meta($user_id, 'axiom_affiliate_requested_partner_code', $partner_code);
    }
}
add_action('user_register', 'axiom_affiliate_save_registration_meta', 20);

/**
 * Get affiliate object for user.
 */
function axiom_affiliate_get_affiliate_by_user($user_id) {
    if (!$user_id || !function_exists('slicewp_get_affiliate_by_user_id')) {
        return false;
    }

    $affiliate = slicewp_get_affiliate_by_user_id($user_id);

    return $affiliate ? $affiliate : false;
}

/**
 * Safely read value from SliceWP object.
 */
function axiom_affiliate_obj_get($object, $key, $default = '') {
    if (is_object($object) && method_exists($object, 'get')) {
        $value = $object->get($key);
        return ($value !== null) ? $value : $default;
    }

    if (is_object($object) && isset($object->$key)) {
        return $object->$key;
    }

    if (is_array($object) && isset($object[$key])) {
        return $object[$key];
    }

    return $default;
}

/**
 * Get current affiliate id from user id.
 */
function axiom_affiliate_get_affiliate_id_by_user($user_id) {
    $affiliate = axiom_affiliate_get_affiliate_by_user($user_id);

    if (!$affiliate) {
        return 0;
    }

    return absint(axiom_affiliate_obj_get($affiliate, 'id', 0));
}

/**
 * Check if affiliate is active.
 */
function axiom_affiliate_is_active($user_id) {
    $affiliate = axiom_affiliate_get_affiliate_by_user($user_id);

    if (!$affiliate) {
        return false;
    }

    $status = strtolower((string) axiom_affiliate_obj_get($affiliate, 'status', ''));

    return ($status === 'active');
}

/**
 * Try to save payout method to SliceWP affiliate data.
 *
 * Manual/Zelle = manual
 * Store Credit = store_credit
 */
function axiom_affiliate_set_slicewp_payout_method($affiliate_id, $payout_method) {
    global $wpdb;

    $affiliate_id   = absint($affiliate_id);
    $payout_method  = sanitize_key($payout_method);

    if (!$affiliate_id || !$payout_method) {
        return false;
    }

    /**
     * Try official-style update function first, if available.
     */
    if (function_exists('slicewp_update_affiliate')) {
        slicewp_update_affiliate($affiliate_id, array(
            'payout_method'  => $payout_method,
            'payment_method' => $payout_method,
        ));
    }

    /**
     * Then write defensively to SliceWP affiliate table if the columns exist.
     */
    $table = $wpdb->prefix . 'slicewp_affiliates';

    $table_exists = $wpdb->get_var(
        $wpdb->prepare('SHOW TABLES LIKE %s', $table)
    );

    if ($table_exists === $table) {
        $columns = $wpdb->get_col("SHOW COLUMNS FROM {$table}", 0);

        if (in_array('payout_method', $columns, true)) {
            $wpdb->update(
                $table,
                array('payout_method' => $payout_method),
                array('id' => $affiliate_id),
                array('%s'),
                array('%d')
            );
        }

        if (in_array('payment_method', $columns, true)) {
            $wpdb->update(
                $table,
                array('payment_method' => $payout_method),
                array('id' => $affiliate_id),
                array('%s'),
                array('%d')
            );
        }
    }

    /**
     * Also save as affiliate meta/user-safe fallback.
     */
    if (function_exists('slicewp_update_affiliate_meta')) {
        slicewp_update_affiliate_meta($affiliate_id, 'axiom_payment_preference', $payout_method);
        slicewp_update_affiliate_meta($affiliate_id, 'payout_method', $payout_method);
        slicewp_update_affiliate_meta($affiliate_id, 'payment_method', $payout_method);
    }

    return true;
}

/**
 * Get a unique coupon code.
 */
function axiom_affiliate_get_unique_coupon_code($requested_code, $user_id) {
    $requested_code = axiom_affiliate_clean_partner_code($requested_code);

    if (!$requested_code) {
        $user = get_userdata($user_id);
        $requested_code = $user ? axiom_affiliate_clean_partner_code($user->user_login . '10') : '';
    }

    if (!$requested_code) {
        $requested_code = 'AXIOM' . absint($user_id);
    }

    if (in_array($requested_code, axiom_affiliate_reserved_codes(), true)) {
        $requested_code = $requested_code . absint($user_id);
    }

    $base_code = $requested_code;
    $final     = $base_code;
    $counter   = 2;

    while (function_exists('wc_get_coupon_id_by_code') && wc_get_coupon_id_by_code($final)) {
        $final = $base_code . $counter;
        $counter++;

        if ($counter > 99) {
            $final = $base_code . absint($user_id);
            break;
        }
    }

    return $final;
}

/**
 * Create WooCommerce affiliate coupon.
 */
function axiom_affiliate_create_coupon_for_user($user_id, $affiliate_id) {
    if (!$user_id || !$affiliate_id || !function_exists('wc_get_coupon_id_by_code')) {
        return '';
    }

    $existing_code = get_user_meta($user_id, 'axiom_affiliate_coupon_code', true);

    if ($existing_code && wc_get_coupon_id_by_code($existing_code)) {
        return $existing_code;
    }

    $requested_code = get_user_meta($user_id, 'axiom_affiliate_requested_partner_code', true);
    $coupon_code    = axiom_affiliate_get_unique_coupon_code($requested_code, $user_id);

    if (!$coupon_code) {
        return '';
    }

    $coupon_id = wp_insert_post(array(
        'post_title'   => $coupon_code,
        'post_name'    => sanitize_title($coupon_code),
        'post_content' => 'Axiom affiliate coupon for affiliate ID ' . absint($affiliate_id),
        'post_status'  => 'publish',
        'post_author'  => absint($user_id),
        'post_type'    => 'shop_coupon',
    ));

    if (is_wp_error($coupon_id) || !$coupon_id) {
        return '';
    }

    /**
     * WooCommerce coupon settings.
     */
    update_post_meta($coupon_id, 'discount_type', 'percent');
    update_post_meta($coupon_id, 'coupon_amount', AXIOM_AFFILIATE_COUPON_PERCENT);
    update_post_meta($coupon_id, 'individual_use', 'yes');
    update_post_meta($coupon_id, 'exclude_sale_items', 'no');
    update_post_meta($coupon_id, 'free_shipping', 'no');
    update_post_meta($coupon_id, 'usage_limit', '');
    update_post_meta($coupon_id, 'usage_limit_per_user', '');
    update_post_meta($coupon_id, 'limit_usage_to_x_items', '');

    /**
     * Axiom tracking meta.
     */
    update_post_meta($coupon_id, 'axiom_affiliate_user_id', absint($user_id));
    update_post_meta($coupon_id, 'axiom_affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, 'axiom_affiliate_coupon', 'yes');

    /**
     * Best-effort SliceWP affiliate coupon linking.
     *
     * SliceWP's Affiliate Coupons add-on stores the affiliate link on the coupon.
     * These common meta keys make the association visible/usable in many setups.
     * If your SliceWP coupon field still does not show the affiliate, manually assign
     * the coupon once in Marketing → Coupons, then we can mirror the exact meta key.
     */
    update_post_meta($coupon_id, 'slicewp_affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, '_slicewp_affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, 'affiliate_id', absint($affiliate_id));

    update_user_meta($user_id, 'axiom_affiliate_coupon_code', $coupon_code);
    update_user_meta($user_id, 'axiom_affiliate_coupon_id', absint($coupon_id));

    if (function_exists('slicewp_update_affiliate_meta')) {
        slicewp_update_affiliate_meta($affiliate_id, 'axiom_affiliate_coupon_code', $coupon_code);
        slicewp_update_affiliate_meta($affiliate_id, 'axiom_affiliate_coupon_id', absint($coupon_id));
    }

    return $coupon_code;
}

/**
 * Sync affiliate setup:
 * - Save payout preference to affiliate
 * - Create coupon when active
 */
function axiom_affiliate_sync_user_setup($user_id) {
    if (!$user_id) {
        return;
    }

    $affiliate_id = axiom_affiliate_get_affiliate_id_by_user($user_id);

    if (!$affiliate_id) {
        return;
    }

    $payment_preference = get_user_meta($user_id, 'axiom_affiliate_payment_preference', true);

    if ($payment_preference) {
        axiom_affiliate_set_slicewp_payout_method($affiliate_id, $payment_preference);
    }

    /**
     * Only create coupon once affiliate is active/approved.
     */
    if (axiom_affiliate_is_active($user_id)) {
        axiom_affiliate_create_coupon_for_user($user_id, $affiliate_id);
    }
}

/**
 * Run sync on login and frontend account loads.
 */
function axiom_affiliate_sync_current_user_setup() {
    if (!is_user_logged_in()) {
        return;
    }

    axiom_affiliate_sync_user_setup(get_current_user_id());
}
add_action('wp', 'axiom_affiliate_sync_current_user_setup', 20);
add_action('admin_init', 'axiom_affiliate_sync_current_user_setup', 20);

/**
 * Sync when user logs in.
 */
function axiom_affiliate_sync_on_login($user_login, $user) {
    if ($user instanceof WP_User) {
        axiom_affiliate_sync_user_setup($user->ID);
    }
}
add_action('wp_login', 'axiom_affiliate_sync_on_login', 20, 2);

/**
 * Shortcode: [axiom_affiliate_partner_card]
 */
function axiom_render_affiliate_partner_card_shortcode() {
    if (!is_user_logged_in()) {
        return '';
    }

    $user_id      = get_current_user_id();
    $affiliate_id = axiom_affiliate_get_affiliate_id_by_user($user_id);

    if (!$affiliate_id) {
        return '';
    }

    axiom_affiliate_sync_user_setup($user_id);

    $coupon_code = get_user_meta($user_id, 'axiom_affiliate_coupon_code', true);

    if (!$coupon_code) {
        $requested = get_user_meta($user_id, 'axiom_affiliate_requested_partner_code', true);
        $coupon_code = $requested ? $requested : 'PENDING';
    }

    $user = get_userdata($user_id);
    $ref  = $user ? $user->user_login : $user_id;

    /**
     * If your SliceWP affiliate keyword is "aff", change "ref" below to "aff".
     * You currently showed "aff" in settings before, so use aff if that is still your setting.
     */
    $referral_link = add_query_arg('aff', rawurlencode($ref), home_url('/'));

    ob_start();
    ?>
    <div class="axiom-partner-card">
        <div class="axiom-partner-card-row axiom-partner-card-row--top">
            <div>
                <div class="axiom-partner-card-label">Your Affiliate Code</div>
                <div class="axiom-partner-card-code"><?php echo esc_html($coupon_code); ?></div>
            </div>

            <button type="button" class="axiom-copy-btn" data-copy="<?php echo esc_attr($coupon_code); ?>">
                <i class="fa-regular fa-copy"></i>
                Copy Code
            </button>
        </div>

        <div class="axiom-partner-card-link-wrap">
            <div class="axiom-partner-card-label">Your Referral Link</div>

            <div class="axiom-partner-link-box">
                <span><?php echo esc_html($referral_link); ?></span>

                <button type="button" class="axiom-copy-btn axiom-copy-btn--small" data-copy="<?php echo esc_attr($referral_link); ?>">
                    <i class="fa-regular fa-copy"></i>
                    Copy Link
                </button>
            </div>
        </div>

        <p class="axiom-partner-card-note">
            Share your code or referral link with your audience. When they make a qualifying purchase, your commission will appear in your dashboard.
        </p>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('axiom_affiliate_partner_card', 'axiom_render_affiliate_partner_card_shortcode');

/**
 * JS for copy buttons.
 */
function axiom_affiliate_partner_card_copy_script() {
    if (!is_page('affiliate-account') && !is_page('affiliate-dashboard')) {
        return;
    }
    ?>
    <script>
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.axiom-copy-btn');
        if (!btn) return;

        var value = btn.getAttribute('data-copy') || '';
        if (!value) return;

        navigator.clipboard.writeText(value).then(function() {
            var old = btn.innerHTML;
            btn.innerHTML = 'Copied!';
            setTimeout(function() {
                btn.innerHTML = old;
            }, 1400);
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'axiom_affiliate_partner_card_copy_script', 50);

/**
 * =========================================================
 * Axiom Affiliate Settings Partner Code Sync
 *
 * If affiliate changes "Your Partner Code" inside SliceWP settings,
 * update their saved code and rename their WooCommerce coupon.
 * =========================================================
 */

add_action('init', 'axiom_affiliate_capture_partner_code_from_settings', 30);

function axiom_affiliate_capture_partner_code_from_settings() {
    if (!is_user_logged_in()) {
        return;
    }

    if (empty($_POST) || !is_array($_POST)) {
        return;
    }

    $user_id = get_current_user_id();

    $submitted_partner_code = axiom_affiliate_find_partner_code_in_post();

    if (!$submitted_partner_code) {
        return;
    }

    update_user_meta($user_id, 'axiom_affiliate_requested_partner_code', $submitted_partner_code);
    update_user_meta($user_id, 'axiom_affiliate_coupon_code', $submitted_partner_code);

    axiom_affiliate_rename_existing_coupon_for_user($user_id, $submitted_partner_code);
}

/**
 * Find partner code from SliceWP settings POST.
 */
function axiom_affiliate_find_partner_code_in_post() {
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            $value = implode(' ', array_map('sanitize_text_field', wp_unslash($value)));
        }

        $key_clean = strtolower((string) $key);

        /**
         * Try to catch keys like:
         * partner_code
         * your_partner_code
         * axiom_partner_code
         * slicewp...partner...
         */
        $looks_like_partner_code_key =
            strpos($key_clean, 'partner') !== false ||
            strpos($key_clean, 'coupon') !== false ||
            strpos($key_clean, 'code') !== false;

        if (!$looks_like_partner_code_key) {
            continue;
        }

        $code = axiom_affiliate_normalize_dashboard_partner_code($value);

        if ($code) {
            return $code;
        }
    }

    return '';
}

/**
 * Normalize dashboard partner code.
 */
function axiom_affiliate_normalize_dashboard_partner_code($value) {
    $value = strtoupper(wp_unslash((string) $value));
    $value = preg_replace('/[^A-Z0-9]/', '', $value);
    $value = substr($value, 0, 18);

    /**
     * Ignore tiny/invalid values.
     */
    if (strlen($value) < 3) {
        return '';
    }

    return $value;
}

/**
 * Rename existing WooCommerce affiliate coupon for user.
 */
function axiom_affiliate_rename_existing_coupon_for_user($user_id, $new_code) {
    if (!$user_id || !$new_code) {
        return false;
    }

    if (!function_exists('wc_get_coupon_id_by_code')) {
        return false;
    }

    $coupon_id = (int) get_user_meta($user_id, 'axiom_affiliate_coupon_id', true);

    /**
     * If coupon ID was never saved, try to find the user's old saved coupon code.
     */
    if (!$coupon_id) {
        $old_code = get_user_meta($user_id, 'axiom_affiliate_coupon_code', true);

        if ($old_code) {
            $coupon_id = (int) wc_get_coupon_id_by_code($old_code);
        }
    }

    /**
     * If still no coupon, create one if the helper exists.
     */
    if (!$coupon_id) {
        $affiliate_id = 0;

        if (function_exists('axiom_affiliate_get_affiliate_id_by_user')) {
            $affiliate_id = (int) axiom_affiliate_get_affiliate_id_by_user($user_id);
        } elseif (function_exists('slicewp_get_affiliate_by_user_id')) {
            $affiliate = slicewp_get_affiliate_by_user_id($user_id);

            if ($affiliate && function_exists('axiom_affiliate_obj_get')) {
                $affiliate_id = (int) axiom_affiliate_obj_get($affiliate, 'id', 0);
            }
        }

        if ($affiliate_id && function_exists('axiom_affiliate_create_coupon_for_user')) {
            $created_code = axiom_affiliate_create_coupon_for_user($user_id, $affiliate_id);

            if ($created_code) {
                $coupon_id = (int) wc_get_coupon_id_by_code($created_code);
            }
        }
    }

    if (!$coupon_id || !get_post($coupon_id)) {
        return false;
    }

    /**
     * Do not overwrite another coupon if the requested code already belongs to a different coupon.
     */
    $conflicting_coupon_id = (int) wc_get_coupon_id_by_code($new_code);

    if ($conflicting_coupon_id && $conflicting_coupon_id !== $coupon_id) {
        update_user_meta($user_id, 'axiom_affiliate_coupon_code_error', 'That partner code is already taken.');
        return false;
    }

    wp_update_post(array(
        'ID'         => $coupon_id,
        'post_title' => $new_code,
        'post_name'  => sanitize_title($new_code),
    ));

    update_user_meta($user_id, 'axiom_affiliate_coupon_code', $new_code);
    update_user_meta($user_id, 'axiom_affiliate_coupon_id', $coupon_id);

    update_post_meta($coupon_id, 'axiom_affiliate_user_id', (int) $user_id);

    return true;
}
