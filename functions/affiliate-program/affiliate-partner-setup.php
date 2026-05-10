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
 * - Creating WooCommerce coupon after affiliate approval
 * - Existing affiliates get a code automatically on login/dashboard load
 * - Code format = email username + commission percent
 * - Saving/displaying partner code and REAL SliceWP referral link
 *
 * IMPORTANT:
 * Affiliates cannot edit their own code from the dashboard anymore.
 * If they want a custom code, admin changes the WooCommerce coupon manually.
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
 * Reserved/bad coupon codes.
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
 * Check if code is reserved/broken.
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
 * Do NOT use this for affiliate code.
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
 * Generate code from email name + commission percent.
 *
 * Example:
 * johnsmith@gmail.com = JOHNSMITH10
 * real.axiom@gmail.com = REALAXIOM10
 */
function axiom_affiliate_generate_code_from_email($user_id) {
    $user_id = absint($user_id);
    $user    = get_userdata($user_id);

    if ($user && !empty($user->user_email)) {
        $email_parts = explode('@', $user->user_email);
        $base        = isset($email_parts[0]) ? $email_parts[0] : '';
    } elseif ($user && !empty($user->user_login)) {
        $base = $user->user_login;
    } else {
        $base = 'AXIOM' . $user_id;
    }

    $base = strtoupper($base);
    $base = preg_replace('/[^A-Z0-9]/', '', $base);

    /**
     * Keep code short enough to add 10 at the end.
     */
    $base = substr($base, 0, 14);

    if (!$base) {
        $base = 'AXIOM' . $user_id;
    }

    $code = $base . AXIOM_AFFILIATE_COUPON_PERCENT;
    $code = axiom_affiliate_clean_partner_code($code);

    if (!$code || axiom_affiliate_is_reserved_code($code)) {
        $code = 'AXIOM' . $user_id . AXIOM_AFFILIATE_COUPON_PERCENT;
    }

    return axiom_affiliate_clean_partner_code($code);
}

/**
 * Get unique email-based code.
 */
function axiom_affiliate_get_unique_email_coupon_code($user_id, $current_coupon_id = 0) {
    $base_code = axiom_affiliate_generate_code_from_email($user_id);
    $final     = $base_code;
    $counter   = 2;

    if (!function_exists('wc_get_coupon_id_by_code')) {
        return $final;
    }

    while (true) {
        $existing_coupon_id = absint(wc_get_coupon_id_by_code($final));

        if (!$existing_coupon_id || ($current_coupon_id && $existing_coupon_id === absint($current_coupon_id))) {
            break;
        }

        $final = substr($base_code, 0, 16) . $counter;
        $counter++;

        if ($counter > 99) {
            $final = 'AXIOM' . absint($user_id) . AXIOM_AFFILIATE_COUPON_PERCENT;
            break;
        }
    }

    return axiom_affiliate_clean_partner_code($final);
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
 *
 * NOTE:
 * Partner code is NOT saved from registration/settings anymore.
 * Code is generated automatically from email + commission rate.
 */
function axiom_affiliate_save_registration_meta($user_id) {
    if (!$user_id) {
        return;
    }

    $payment_preference = axiom_affiliate_get_submitted_payment_preference();
    $zelle_contact      = axiom_affiliate_get_submitted_zelle_contact();

    if ($payment_preference) {
        update_user_meta($user_id, 'axiom_affiliate_payment_preference', $payment_preference);
    }

    if ($zelle_contact) {
        update_user_meta($user_id, 'axiom_affiliate_zelle_contact', $zelle_contact);
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
 *
 * IMPORTANT:
 * Code is always generated from email + commission percent.
 * Affiliates cannot choose/edit it.
 */
function axiom_affiliate_create_coupon_for_user($user_id, $affiliate_id) {
    if (!$user_id || !$affiliate_id || !function_exists('wc_get_coupon_id_by_code')) {
        return '';
    }

    $existing_coupon_id = axiom_affiliate_find_existing_coupon_id_for_user($user_id, $affiliate_id);
    $wanted_code        = axiom_affiliate_get_unique_email_coupon_code($user_id, $existing_coupon_id);

    if ($existing_coupon_id && get_post($existing_coupon_id)) {
        $existing_code = axiom_affiliate_clean_partner_code(get_the_title($existing_coupon_id));

        /**
         * If existing code is broken/reserved, rename to email-based code.
         * Otherwise leave existing real coupon alone so admin manual edits stay.
         */
        if (!$existing_code || axiom_affiliate_is_reserved_code($existing_code)) {
            axiom_affiliate_admin_rename_coupon($existing_coupon_id, $user_id, $affiliate_id, $wanted_code);
            return $wanted_code;
        }

        update_user_meta($user_id, 'axiom_affiliate_coupon_code', $existing_code);
        update_user_meta($user_id, 'axiom_affiliate_coupon_id', absint($existing_coupon_id));

        axiom_affiliate_link_coupon_to_affiliate($existing_coupon_id, $user_id, $affiliate_id);

        return $existing_code;
    }

    $coupon_code = $wanted_code;

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

    axiom_affiliate_apply_coupon_settings($coupon_id);
    axiom_affiliate_link_coupon_to_affiliate($coupon_id, $user_id, $affiliate_id);
    axiom_affiliate_update_slicewp_coupon_tables($affiliate_id, $coupon_id, $coupon_code);

    update_user_meta($user_id, 'axiom_affiliate_coupon_code', $coupon_code);
    update_user_meta($user_id, 'axiom_affiliate_coupon_id', absint($coupon_id));

    return $coupon_code;
}

/**
 * Apply standard coupon settings.
 */
function axiom_affiliate_apply_coupon_settings($coupon_id) {
    update_post_meta($coupon_id, 'discount_type', 'percent');
    update_post_meta($coupon_id, 'coupon_amount', AXIOM_AFFILIATE_COUPON_PERCENT);
    update_post_meta($coupon_id, 'individual_use', 'yes');
    update_post_meta($coupon_id, 'exclude_sale_items', 'no');
    update_post_meta($coupon_id, 'free_shipping', 'no');
    update_post_meta($coupon_id, 'usage_limit', '');
    update_post_meta($coupon_id, 'usage_limit_per_user', '');
    update_post_meta($coupon_id, 'limit_usage_to_x_items', '');
}

/**
 * Admin/system rename only.
 * Affiliates do not call this directly.
 */
function axiom_affiliate_admin_rename_coupon($coupon_id, $user_id, $affiliate_id, $new_code) {
    if (!$coupon_id || !$new_code || !function_exists('wc_get_coupon_id_by_code')) {
        return false;
    }

    $new_code = axiom_affiliate_clean_partner_code($new_code);

    if (!$new_code || axiom_affiliate_is_reserved_code($new_code)) {
        return false;
    }

    $conflicting_coupon_id = absint(wc_get_coupon_id_by_code($new_code));

    if ($conflicting_coupon_id && $conflicting_coupon_id !== absint($coupon_id)) {
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

    axiom_affiliate_apply_coupon_settings($coupon_id);
    axiom_affiliate_link_coupon_to_affiliate($coupon_id, $user_id, $affiliate_id);
    axiom_affiliate_update_slicewp_coupon_tables($affiliate_id, $coupon_id, $new_code);

    update_user_meta($user_id, 'axiom_affiliate_coupon_code', $new_code);
    update_user_meta($user_id, 'axiom_affiliate_coupon_id', absint($coupon_id));

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

    $coupon_id = axiom_affiliate_find_existing_coupon_id_for_user($user_id, $affiliate_id);

    if (!$coupon_id || !get_post($coupon_id)) {
        return;
    }

    $current_code = axiom_affiliate_clean_partner_code(get_the_title($coupon_id));

    if (!$current_code || axiom_affiliate_is_reserved_code($current_code)) {
        $new_code = axiom_affiliate_get_unique_email_coupon_code($user_id, $coupon_id);
        axiom_affiliate_admin_rename_coupon($coupon_id, $user_id, $affiliate_id, $new_code);
    }
}

/**
 * Sync affiliate setup.
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
 *
 * IMPORTANT:
 * This only saves payout/Zelle.
 * It does NOT save or edit affiliate coupon code anymore.
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

    if ($payment_preference) {
        update_user_meta($user_id, 'axiom_affiliate_payment_preference', $payment_preference);
        axiom_affiliate_set_slicewp_payout_method($affiliate_id, $payment_preference);
    }

    if ($zelle_contact) {
        update_user_meta($user_id, 'axiom_affiliate_zelle_contact', $zelle_contact);
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
        $coupon_code = axiom_affiliate_create_coupon_for_user($user_id, $affiliate_id);
    }

    if (!$coupon_code || axiom_affiliate_is_reserved_code($coupon_code)) {
        $coupon_code = 'PENDING';
    }

    /**
     * REAL SliceWP tracking link for every affiliate.
     * Example:
     * Affiliate ID 3 = https://axiomresearch.shop/?aff=3
     */
    $referral_link = add_query_arg('aff', absint($affiliate_id), home_url('/'));

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
