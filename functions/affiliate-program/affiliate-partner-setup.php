<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =========================================================
 * Axiom Affiliate Partner Setup
 *
 * Handles:
 * - Saving payment preference from registration/settings
 * - Saving Zelle contact
 * - Saving requested partner code
 * - Creating WooCommerce coupon after affiliate approval
 * - Renaming the real linked WooCommerce coupon when partner code changes
 * - Saving/displaying partner code and referral link
 * =========================================================
 */

if (!defined('AXIOM_AFFILIATE_COUPON_PERCENT')) {
    define('AXIOM_AFFILIATE_COUPON_PERCENT', 10);
}

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
 * Reserved coupon codes affiliates cannot claim.
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
        'PAST30DAYS',
        'PAST7DAYS',
        'PAST90DAYS',
        'LAST30DAYS',
        'LAST7DAYS',
        'LAST90DAYS',
    );
}

/**
 * Check if a code is blocked/reserved.
 */
function axiom_affiliate_is_reserved_code($code) {
    $code = axiom_affiliate_clean_partner_code($code);

    if (!$code) {
        return true;
    }

    return in_array($code, axiom_affiliate_reserved_codes(), true);
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
 * Get a submitted POST value by exact key first, then fuzzy key search.
 *
 * Only use this for payment/Zelle fields.
 * Do NOT use this for partner code.
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
 *
 * IMPORTANT:
 * This intentionally does NOT fuzzy-search POST data.
 * It only accepts exact custom keys so SliceWP date filter text like
 * "Past 30 days" can never become a coupon again.
 */
function axiom_affiliate_get_submitted_partner_code() {
    $allowed_keys = array(
        'axiom_partner_code',
        'axiom_affiliate_partner_code',
        'axiom_affiliate_requested_partner_code',
        'partner_code',
        'your_partner_code',
    );

    $value = '';

    foreach ($allowed_keys as $key) {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            break;
        }
    }

    if (!$value) {
        return '';
    }

    if (is_array($value)) {
        $value = implode(' ', array_map('sanitize_text_field', wp_unslash($value)));
    }

    $code = axiom_affiliate_clean_partner_code($value);

    if (strlen($code) < 3) {
        return '';
    }

    if (axiom_affiliate_is_reserved_code($code)) {
        return '';
    }

    return $code;
}

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
 * Get current affiliate ID from user ID.
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
 * Save submitted affiliate preferences to user meta on registration.
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
 * Save payout method to SliceWP affiliate data.
 */
function axiom_affiliate_set_slicewp_payout_method($affiliate_id, $payout_method) {
    global $wpdb;

    $affiliate_id  = absint($affiliate_id);
    $payout_method = sanitize_key($payout_method);

    if (!$affiliate_id || !$payout_method) {
        return false;
    }

    if (function_exists('slicewp_update_affiliate')) {
        slicewp_update_affiliate($affiliate_id, array(
            'payout_method'  => $payout_method,
            'payment_method' => $payout_method,
        ));
    }

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

    if (!$requested_code || axiom_affiliate_is_reserved_code($requested_code)) {
        $user = get_userdata($user_id);
        $requested_code = $user ? axiom_affiliate_clean_partner_code($user->user_login . '10') : '';
    }

    if (!$requested_code || axiom_affiliate_is_reserved_code($requested_code)) {
        $requested_code = 'AXIOM' . absint($user_id);
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
 * Link coupon to affiliate using every common key.
 */
function axiom_affiliate_link_coupon_to_affiliate($coupon_id, $user_id, $affiliate_id) {
    if (!$coupon_id || !$user_id || !$affiliate_id) {
        return;
    }

    update_post_meta($coupon_id, 'axiom_affiliate_user_id', absint($user_id));
    update_post_meta($coupon_id, 'axiom_affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, 'axiom_affiliate_coupon', 'yes');

    update_post_meta($coupon_id, 'slicewp_affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, '_slicewp_affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, 'slicewp_coupon_affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, '_slicewp_coupon_affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, 'affiliate_id', absint($affiliate_id));
    update_post_meta($coupon_id, '_affiliate_id', absint($affiliate_id));

    update_user_meta($user_id, 'axiom_affiliate_coupon_id', absint($coupon_id));
    update_user_meta($user_id, 'axiom_affiliate_coupon_code', get_the_title($coupon_id));

    if (function_exists('slicewp_update_affiliate_meta')) {
        slicewp_update_affiliate_meta($affiliate_id, 'axiom_affiliate_coupon_code', get_the_title($coupon_id));
        slicewp_update_affiliate_meta($affiliate_id, 'axiom_affiliate_coupon_id', absint($coupon_id));
    }
}

/**
 * Find the actual WooCommerce coupon connected to this affiliate.
 */
function axiom_affiliate_find_existing_coupon_id_for_user($user_id, $affiliate_id) {
    global $wpdb;

    if (!$user_id || !$affiliate_id) {
        return 0;
    }

    $coupon_id = absint(get_user_meta($user_id, 'axiom_affiliate_coupon_id', true));

    if ($coupon_id && get_post($coupon_id) && get_post_type($coupon_id) === 'shop_coupon') {
        return $coupon_id;
    }

    $saved_code = get_user_meta($user_id, 'axiom_affiliate_coupon_code', true);

    if ($saved_code && function_exists('wc_get_coupon_id_by_code')) {
        $coupon_id = absint(wc_get_coupon_id_by_code($saved_code));

        if ($coupon_id && get_post($coupon_id) && get_post_type($coupon_id) === 'shop_coupon') {
            update_user_meta($user_id, 'axiom_affiliate_coupon_id', $coupon_id);
            return $coupon_id;
        }
    }

    if (function_exists('slicewp_get_affiliate_coupons')) {
        $linked_coupons = slicewp_get_affiliate_coupons($affiliate_id);

        if (is_array($linked_coupons) && !empty($linked_coupons)) {
            foreach ($linked_coupons as $linked_coupon) {
                $possible_coupon_id = 0;
                $possible_code      = '';

                if (is_object($linked_coupon) && method_exists($linked_coupon, 'get')) {
                    $possible_coupon_id = absint($linked_coupon->get('coupon_id'));
                    $possible_code      = (string) $linked_coupon->get('code');

                    if (!$possible_coupon_id) {
                        $possible_coupon_id = absint($linked_coupon->get('id'));
                    }

                    if (!$possible_code) {
                        $possible_code = (string) $linked_coupon->get('coupon_code');
                    }
                } elseif (is_array($linked_coupon)) {
                    if (!empty($linked_coupon['coupon_id'])) {
                        $possible_coupon_id = absint($linked_coupon['coupon_id']);
                    }

                    if (!$possible_coupon_id && !empty($linked_coupon['id'])) {
                        $possible_coupon_id = absint($linked_coupon['id']);
                    }

                    if (!empty($linked_coupon['code'])) {
                        $possible_code = (string) $linked_coupon['code'];
                    }

                    if (!$possible_code && !empty($linked_coupon['coupon_code'])) {
                        $possible_code = (string) $linked_coupon['coupon_code'];
                    }
                } elseif (is_object($linked_coupon)) {
                    if (!empty($linked_coupon->coupon_id)) {
                        $possible_coupon_id = absint($linked_coupon->coupon_id);
                    }

                    if (!$possible_coupon_id && !empty($linked_coupon->id)) {
                        $possible_coupon_id = absint($linked_coupon->id);
                    }

                    if (!empty($linked_coupon->code)) {
                        $possible_code = (string) $linked_coupon->code;
                    }

                    if (!$possible_code && !empty($linked_coupon->coupon_code)) {
                        $possible_code = (string) $linked_coupon->coupon_code;
                    }
                }

                if ($possible_code && function_exists('wc_get_coupon_id_by_code')) {
                    $code_coupon_id = absint(wc_get_coupon_id_by_code($possible_code));

                    if ($code_coupon_id && get_post($code_coupon_id) && get_post_type($code_coupon_id) === 'shop_coupon') {
                        update_user_meta($user_id, 'axiom_affiliate_coupon_id', $code_coupon_id);
                        update_user_meta($user_id, 'axiom_affiliate_coupon_code', get_the_title($code_coupon_id));
                        return $code_coupon_id;
                    }
                }

                if ($possible_coupon_id && get_post($possible_coupon_id) && get_post_type($possible_coupon_id) === 'shop_coupon') {
                    update_user_meta($user_id, 'axiom_affiliate_coupon_id', $possible_coupon_id);
                    update_user_meta($user_id, 'axiom_affiliate_coupon_code', get_the_title($possible_coupon_id));
                    return $possible_coupon_id;
                }
            }
        }
    }

    $coupon_query = new WP_Query(array(
        'post_type'      => 'shop_coupon',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => 'axiom_affiliate_user_id',
                'value'   => absint($user_id),
                'compare' => '=',
            ),
            array(
                'key'     => 'axiom_affiliate_id',
                'value'   => absint($affiliate_id),
                'compare' => '=',
            ),
            array(
                'key'     => 'slicewp_affiliate_id',
                'value'   => absint($affiliate_id),
                'compare' => '=',
            ),
            array(
                'key'     => '_slicewp_affiliate_id',
                'value'   => absint($affiliate_id),
                'compare' => '=',
            ),
            array(
                'key'     => 'slicewp_coupon_affiliate_id',
                'value'   => absint($affiliate_id),
                'compare' => '=',
            ),
            array(
                'key'     => '_slicewp_coupon_affiliate_id',
                'value'   => absint($affiliate_id),
                'compare' => '=',
            ),
            array(
                'key'     => 'affiliate_id',
                'value'   => absint($affiliate_id),
                'compare' => '=',
            ),
            array(
                'key'     => '_affiliate_id',
                'value'   => absint($affiliate_id),
                'compare' => '=',
            ),
        ),
    ));

    if (!empty($coupon_query->posts[0])) {
        $coupon_id = absint($coupon_query->posts[0]);

        update_user_meta($user_id, 'axiom_affiliate_coupon_id', $coupon_id);
        update_user_meta($user_id, 'axiom_affiliate_coupon_code', get_the_title($coupon_id));

        return $coupon_id;
    }

    $coupon_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "
            SELECT p.ID
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_coupon'
            AND (
                pm.meta_key LIKE %s
                OR pm.meta_key LIKE %s
                OR pm.meta_key LIKE %s
            )
            AND (
                pm.meta_value = %s
                OR pm.meta_value = %s
            )
            ORDER BY p.ID ASC
            LIMIT 1
            ",
            '%affiliate%',
            '%slicewp%',
            '%coupon%',
            (string) $affiliate_id,
            (string) $user_id
        )
    );

    if ($coupon_id && get_post($coupon_id) && get_post_type($coupon_id) === 'shop_coupon') {
        update_user_meta($user_id, 'axiom_affiliate_coupon_id', $coupon_id);
        update_user_meta($user_id, 'axiom_affiliate_coupon_code', get_the_title($coupon_id));
        return $coupon_id;
    }

    return 0;
}

/**
 * Update possible SliceWP coupon relationship tables.
 */
function axiom_affiliate_update_slicewp_coupon_tables($affiliate_id, $coupon_id, $new_code) {
    global $wpdb;

    $affiliate_id = absint($affiliate_id);
    $coupon_id    = absint($coupon_id);
    $new_code     = axiom_affiliate_clean_partner_code($new_code);

    if (!$affiliate_id || !$coupon_id || !$new_code || axiom_affiliate_is_reserved_code($new_code)) {
        return;
    }

    $tables = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}slicewp%coupon%'");

    if (empty($tables)) {
        return;
    }

    foreach ($tables as $table) {
        $columns = $wpdb->get_col("SHOW COLUMNS FROM {$table}", 0);

        if (empty($columns) || !in_array('affiliate_id', $columns, true)) {
            continue;
        }

        $data = array();

        if (in_array('coupon_id', $columns, true)) {
            $data['coupon_id'] = $coupon_id;
        }

        if (in_array('code', $columns, true)) {
            $data['code'] = $new_code;
        }

        if (in_array('coupon_code', $columns, true)) {
            $data['coupon_code'] = $new_code;
        }

        if (in_array('name', $columns, true)) {
            $data['name'] = $new_code;
        }

        if (empty($data)) {
            continue;
        }

        $existing_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT affiliate_id FROM {$table} WHERE affiliate_id = %d LIMIT 1",
                $affiliate_id
            )
        );

        if ($existing_id) {
            $wpdb->update(
                $table,
                $data,
                array('affiliate_id' => $affiliate_id)
            );
        }
    }
}

/**
 * Create WooCommerce affiliate coupon.
 */
function axiom_affiliate_create_coupon_for_user($user_id, $affiliate_id) {
    if (!$user_id || !$affiliate_id || !function_exists('wc_get_coupon_id_by_code')) {
        return '';
    }

    $requested_code = axiom_affiliate_clean_partner_code(get_user_meta($user_id, 'axiom_affiliate_requested_partner_code', true));

    $existing_coupon_id = axiom_affiliate_find_existing_coupon_id_for_user($user_id, $affiliate_id);

    if ($existing_coupon_id && get_post($existing_coupon_id)) {
        $existing_code = axiom_affiliate_clean_partner_code(get_the_title($existing_coupon_id));

        /**
         * If the existing coupon is broken/reserved like PAST30DAYS,
         * rename it immediately to the saved requested partner code.
         */
        if ($requested_code && !axiom_affiliate_is_reserved_code($requested_code) && axiom_affiliate_is_reserved_code($existing_code)) {
            axiom_affiliate_change_user_coupon_code($user_id, $requested_code);
            return $requested_code;
        }

        update_user_meta($user_id, 'axiom_affiliate_coupon_code', $existing_code);
        update_user_meta($user_id, 'axiom_affiliate_coupon_id', absint($existing_coupon_id));

        axiom_affiliate_link_coupon_to_affiliate($existing_coupon_id, $user_id, $affiliate_id);

        return $existing_code;
    }

    $coupon_code = axiom_affiliate_get_unique_coupon_code($requested_code, $user_id);

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

    update_post_meta($coupon_id, 'discount_type', 'percent');
    update_post_meta($coupon_id, 'coupon_amount', AXIOM_AFFILIATE_COUPON_PERCENT);
    update_post_meta($coupon_id, 'individual_use', 'yes');
    update_post_meta($coupon_id, 'exclude_sale_items', 'no');
    update_post_meta($coupon_id, 'free_shipping', 'no');
    update_post_meta($coupon_id, 'usage_limit', '');
    update_post_meta($coupon_id, 'usage_limit_per_user', '');
    update_post_meta($coupon_id, 'limit_usage_to_x_items', '');

    axiom_affiliate_link_coupon_to_affiliate($coupon_id, $user_id, $affiliate_id);
    axiom_affiliate_update_slicewp_coupon_tables($affiliate_id, $coupon_id, $coupon_code);

    update_user_meta($user_id, 'axiom_affiliate_coupon_code', $coupon_code);
    update_user_meta($user_id, 'axiom_affiliate_coupon_id', absint($coupon_id));

    return $coupon_code;
}

/**
 * Change/rename the actual linked WooCommerce coupon.
 */
function axiom_affiliate_change_user_coupon_code($user_id, $new_code) {
    if (!$user_id || !$new_code || !function_exists('wc_get_coupon_id_by_code')) {
        return false;
    }

    $new_code = axiom_affiliate_clean_partner_code($new_code);

    if (!$new_code || strlen($new_code) < 3) {
        update_user_meta($user_id, 'axiom_affiliate_coupon_code_error', 'Invalid partner code.');
        return false;
    }

    if (axiom_affiliate_is_reserved_code($new_code)) {
        update_user_meta($user_id, 'axiom_affiliate_coupon_code_error', 'That partner code is reserved.');
        return false;
    }

    $affiliate_id = axiom_affiliate_get_affiliate_id_by_user($user_id);

    if (!$affiliate_id) {
        update_user_meta($user_id, 'axiom_affiliate_coupon_code_error', 'Affiliate account was not found.');
        return false;
    }

    $coupon_id = axiom_affiliate_find_existing_coupon_id_for_user($user_id, $affiliate_id);

    if (!$coupon_id || !get_post($coupon_id)) {
        $coupon_id = wp_insert_post(array(
            'post_title'   => $new_code,
            'post_name'    => sanitize_title($new_code),
            'post_content' => 'Axiom affiliate coupon for affiliate ID ' . absint($affiliate_id),
            'post_status'  => 'publish',
            'post_author'  => absint($user_id),
            'post_type'    => 'shop_coupon',
        ));
    }

    if (is_wp_error($coupon_id) || !$coupon_id || !get_post($coupon_id)) {
        update_user_meta($user_id, 'axiom_affiliate_coupon_code_error', 'Could not find or create affiliate coupon.');
        return false;
    }

    $conflicting_coupon_id = absint(wc_get_coupon_id_by_code($new_code));

    if ($conflicting_coupon_id && $conflicting_coupon_id !== absint($coupon_id)) {
        update_user_meta($user_id, 'axiom_affiliate_coupon_code_error', 'That partner code is already taken.');
        return false;
    }

    wp_update_post(array(
        'ID'          => absint($coupon_id),
        'post_title'  => $new_code,
        'post_name'   => sanitize_title($new_code),
        'post_author' => absint($user_id),
        'post_status' => 'publish',
        'post_type'   => 'shop_coupon',
    ));

    update_post_meta($coupon_id, 'discount_type', 'percent');
    update_post_meta($coupon_id, 'coupon_amount', AXIOM_AFFILIATE_COUPON_PERCENT);
    update_post_meta($coupon_id, 'individual_use', 'yes');
    update_post_meta($coupon_id, 'exclude_sale_items', 'no');
    update_post_meta($coupon_id, 'free_shipping', 'no');
    update_post_meta($coupon_id, 'usage_limit', '');
    update_post_meta($coupon_id, 'usage_limit_per_user', '');
    update_post_meta($coupon_id, 'limit_usage_to_x_items', '');

    axiom_affiliate_link_coupon_to_affiliate($coupon_id, $user_id, $affiliate_id);
    axiom_affiliate_update_slicewp_coupon_tables($affiliate_id, $coupon_id, $new_code);

    update_user_meta($user_id, 'axiom_affiliate_requested_partner_code', $new_code);
    update_user_meta($user_id, 'axiom_affiliate_coupon_code', $new_code);
    update_user_meta($user_id, 'axiom_affiliate_coupon_id', absint($coupon_id));

    delete_user_meta($user_id, 'axiom_affiliate_coupon_code_error');

    clean_post_cache($coupon_id);

    return true;
}

/**
 * Repair a broken coupon like PAST30DAYS.
 */
function axiom_affiliate_repair_reserved_coupon_code($user_id) {
    $user_id = absint($user_id);

    if (!$user_id || !function_exists('wc_get_coupon_id_by_code')) {
        return;
    }

    $affiliate_id = axiom_affiliate_get_affiliate_id_by_user($user_id);

    if (!$affiliate_id) {
        return;
    }

    $requested_code = axiom_affiliate_clean_partner_code(get_user_meta($user_id, 'axiom_affiliate_requested_partner_code', true));

    if (!$requested_code || axiom_affiliate_is_reserved_code($requested_code)) {
        return;
    }

    $coupon_id = axiom_affiliate_find_existing_coupon_id_for_user($user_id, $affiliate_id);

    if (!$coupon_id || !get_post($coupon_id)) {
        return;
    }

    $current_code = axiom_affiliate_clean_partner_code(get_the_title($coupon_id));

    if (axiom_affiliate_is_reserved_code($current_code)) {
        axiom_affiliate_change_user_coupon_code($user_id, $requested_code);
    }
}

/**
 * Sync affiliate setup:
 * - Save payout preference to affiliate
 * - Create coupon when active
 * - Repair broken PAST30DAYS coupon if needed
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

    if (axiom_affiliate_is_active($user_id)) {
        axiom_affiliate_create_coupon_for_user($user_id, $affiliate_id);
        axiom_affiliate_repair_reserved_coupon_code($user_id);
    }
}

/**
 * Capture settings updates from SliceWP settings form.
 */
function axiom_affiliate_capture_settings_post() {
    if (!is_user_logged_in()) {
        return;
    }

    if (empty($_POST) || !is_array($_POST)) {
        return;
    }

    $user_id      = get_current_user_id();
    $affiliate_id = axiom_affiliate_get_affiliate_id_by_user($user_id);

    if (!$affiliate_id) {
        return;
    }

    $payment_preference = axiom_affiliate_get_submitted_payment_preference();
    $zelle_contact      = axiom_affiliate_get_submitted_zelle_contact();
    $partner_code       = axiom_affiliate_get_submitted_partner_code();

    if ($payment_preference) {
        update_user_meta($user_id, 'axiom_affiliate_payment_preference', $payment_preference);
        axiom_affiliate_set_slicewp_payout_method($affiliate_id, $payment_preference);
    }

    if ($zelle_contact) {
        update_user_meta($user_id, 'axiom_affiliate_zelle_contact', $zelle_contact);
    }

    if ($partner_code) {
        update_user_meta($user_id, 'axiom_affiliate_requested_partner_code', $partner_code);
        axiom_affiliate_change_user_coupon_code($user_id, $partner_code);
    }

    axiom_affiliate_repair_reserved_coupon_code($user_id);
}
add_action('init', 'axiom_affiliate_capture_settings_post', 5);

/**
 * Run sync on frontend/admin account loads.
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
 * Sync on login.
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

    $coupon_id   = axiom_affiliate_find_existing_coupon_id_for_user($user_id, $affiliate_id);
    $coupon_code = $coupon_id ? axiom_affiliate_clean_partner_code(get_the_title($coupon_id)) : '';

    if (!$coupon_code || axiom_affiliate_is_reserved_code($coupon_code)) {
        $requested_code = axiom_affiliate_clean_partner_code(get_user_meta($user_id, 'axiom_affiliate_requested_partner_code', true));

        if ($requested_code && !axiom_affiliate_is_reserved_code($requested_code)) {
            axiom_affiliate_change_user_coupon_code($user_id, $requested_code);
            $coupon_code = $requested_code;
        }
    }

    if (!$coupon_code) {
        $coupon_code = 'PENDING';
    }

    $user = get_userdata($user_id);
    $ref  = $user ? $user->user_login : $user_id;

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
 * Axiom Emergency Affiliate Coupon Repair
 *
 * Use while logged in as admin:
 * /?axiom_fix_affiliate_coupon=1&old_code=PAST30DAYS&new_code=YOURCODE
 *
 * Example:
 * https://axiomresearch.shop/?axiom_fix_affiliate_coupon=1&old_code=PAST30DAYS&new_code=NOAPAPA
 * =========================================================
 */

add_action('init', 'axiom_emergency_fix_affiliate_coupon_code', 1);

function axiom_emergency_fix_affiliate_coupon_code() {
    if (!is_user_logged_in()) {
        return;
    }

    if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
        return;
    }

    if (empty($_GET['axiom_fix_affiliate_coupon'])) {
        return;
    }

    if (empty($_GET['old_code']) || empty($_GET['new_code'])) {
        wp_die('Missing old_code or new_code.');
    }

    if (!function_exists('wc_get_coupon_id_by_code')) {
        wp_die('WooCommerce coupon functions are not available.');
    }

    $old_code = strtoupper(sanitize_text_field(wp_unslash($_GET['old_code'])));
    $new_code = strtoupper(sanitize_text_field(wp_unslash($_GET['new_code'])));

    $old_code = preg_replace('/[^A-Z0-9]/', '', $old_code);
    $new_code = preg_replace('/[^A-Z0-9]/', '', $new_code);

    $old_code = substr($old_code, 0, 18);
    $new_code = substr($new_code, 0, 18);

    if (!$old_code || !$new_code || strlen($new_code) < 3) {
        wp_die('Invalid old_code or new_code.');
    }

    $coupon_id = (int) wc_get_coupon_id_by_code($old_code);

    if (!$coupon_id || !get_post($coupon_id)) {
        wp_die('Could not find coupon with code: ' . esc_html($old_code));
    }

    $conflict_id = (int) wc_get_coupon_id_by_code($new_code);

    if ($conflict_id && $conflict_id !== $coupon_id) {
        wp_die('The new code is already used by another coupon: ' . esc_html($new_code));
    }

    /**
     * Rename the real WooCommerce coupon.
     */
    wp_update_post(array(
        'ID'         => $coupon_id,
        'post_title' => $new_code,
        'post_name'  => sanitize_title($new_code),
    ));

    /**
     * Keep coupon settings correct.
     */
    update_post_meta($coupon_id, 'discount_type', 'percent');
    update_post_meta($coupon_id, 'coupon_amount', AXIOM_AFFILIATE_COUPON_PERCENT);
    update_post_meta($coupon_id, 'individual_use', 'yes');
    update_post_meta($coupon_id, 'exclude_sale_items', 'no');
    update_post_meta($coupon_id, 'free_shipping', 'no');

    /**
     * Find attached affiliate/user from coupon meta.
     */
    $user_id = 0;
    $affiliate_id = 0;

    $possible_user_id = (int) get_post_meta($coupon_id, 'axiom_affiliate_user_id', true);
    if ($possible_user_id) {
        $user_id = $possible_user_id;
    }

    $possible_affiliate_id = (int) get_post_meta($coupon_id, 'axiom_affiliate_id', true);
    if ($possible_affiliate_id) {
        $affiliate_id = $possible_affiliate_id;
    }

    if (!$affiliate_id) {
        $affiliate_id = (int) get_post_meta($coupon_id, 'slicewp_affiliate_id', true);
    }

    if (!$affiliate_id) {
        $affiliate_id = (int) get_post_meta($coupon_id, '_slicewp_affiliate_id', true);
    }

    if (!$affiliate_id) {
        $affiliate_id = (int) get_post_meta($coupon_id, 'affiliate_id', true);
    }

    if (!$affiliate_id) {
        $affiliate_id = (int) get_post_meta($coupon_id, '_affiliate_id', true);
    }

    /**
     * If we have affiliate ID but no user ID, find the user through SliceWP.
     */
    if (!$user_id && $affiliate_id && function_exists('slicewp_get_affiliate')) {
        $affiliate = slicewp_get_affiliate($affiliate_id);

        if ($affiliate && function_exists('axiom_affiliate_obj_get')) {
            $user_id = (int) axiom_affiliate_obj_get($affiliate, 'user_id', 0);
        }
    }

    /**
     * Save corrected user meta.
     */
    if ($user_id) {
        update_user_meta($user_id, 'axiom_affiliate_requested_partner_code', $new_code);
        update_user_meta($user_id, 'axiom_affiliate_coupon_code', $new_code);
        update_user_meta($user_id, 'axiom_affiliate_coupon_id', $coupon_id);
        delete_user_meta($user_id, 'axiom_affiliate_coupon_code_error');
    }

    /**
     * Save corrected coupon meta.
     */
    if ($user_id) {
        update_post_meta($coupon_id, 'axiom_affiliate_user_id', $user_id);
    }

    if ($affiliate_id) {
        update_post_meta($coupon_id, 'axiom_affiliate_id', $affiliate_id);
        update_post_meta($coupon_id, 'slicewp_affiliate_id', $affiliate_id);
        update_post_meta($coupon_id, '_slicewp_affiliate_id', $affiliate_id);
        update_post_meta($coupon_id, 'slicewp_coupon_affiliate_id', $affiliate_id);
        update_post_meta($coupon_id, '_slicewp_coupon_affiliate_id', $affiliate_id);
        update_post_meta($coupon_id, 'affiliate_id', $affiliate_id);
        update_post_meta($coupon_id, '_affiliate_id', $affiliate_id);
    }

    update_post_meta($coupon_id, 'axiom_affiliate_coupon', 'yes');

    /**
     * Update SliceWP coupon relationship tables if they exist.
     */
    if ($affiliate_id && function_exists('axiom_affiliate_update_slicewp_coupon_tables')) {
        axiom_affiliate_update_slicewp_coupon_tables($affiliate_id, $coupon_id, $new_code);
    }

    clean_post_cache($coupon_id);

    wp_die(
        'Fixed coupon successfully.<br><br>' .
        'Old code: <strong>' . esc_html($old_code) . '</strong><br>' .
        'New code: <strong>' . esc_html($new_code) . '</strong><br>' .
        'Coupon ID: <strong>' . esc_html($coupon_id) . '</strong><br><br>' .
        '<a href="' . esc_url(admin_url('edit.php?post_type=shop_coupon')) . '">Go back to coupons</a>'
    );
}
