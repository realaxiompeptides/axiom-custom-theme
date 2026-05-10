<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper: safely get values from SliceWP objects.
 */
function axiom_affiliate_get_object_value($object, $key, $default = '') {
    if (is_object($object) && method_exists($object, 'get')) {
        $value = $object->get($key);
        return ($value !== null) ? $value : $default;
    }

    if (is_array($object) && isset($object[$key])) {
        return $object[$key];
    }

    if (is_object($object) && isset($object->$key)) {
        return $object->$key;
    }

    return $default;
}

/**
 * Helper: currency formatting.
 */
function axiom_affiliate_format_money($amount) {
    $amount = (float) $amount;

    if (function_exists('wc_price')) {
        return wc_price($amount);
    }

    return '$' . number_format($amount, 2);
}

/**
 * Clean partner code.
 */
function axiom_affiliate_dashboard_clean_partner_code($value) {
    $value = strtoupper((string) $value);
    $value = preg_replace('/[^A-Z0-9]/', '', $value);
    $value = substr($value, 0, 18);

    return $value;
}

/**
 * Get current user's SliceWP affiliate object.
 */
function axiom_get_current_slicewp_affiliate() {
    if (!is_user_logged_in()) {
        return false;
    }

    if (!function_exists('slicewp_get_affiliate_by_user_id')) {
        return false;
    }

    $affiliate = slicewp_get_affiliate_by_user_id(get_current_user_id());

    if (!$affiliate) {
        return false;
    }

    return $affiliate;
}

/**
 * Get current affiliate ID.
 */
function axiom_get_current_slicewp_affiliate_id() {
    $affiliate = axiom_get_current_slicewp_affiliate();

    if (!$affiliate) {
        return 0;
    }

    return (int) axiom_affiliate_get_object_value($affiliate, 'id', 0);
}

/**
 * Check if current user is an active SliceWP affiliate.
 */
function axiom_is_current_user_active_affiliate() {
    $affiliate = axiom_get_current_slicewp_affiliate();

    if (!$affiliate) {
        return false;
    }

    $status = strtolower((string) axiom_affiliate_get_object_value($affiliate, 'status', ''));

    return ($status === 'active');
}

/**
 * Get commissions for current affiliate.
 */
function axiom_get_current_affiliate_commissions() {
    $affiliate_id = axiom_get_current_slicewp_affiliate_id();

    if (!$affiliate_id || !function_exists('slicewp_get_commissions')) {
        return array();
    }

    $commissions = slicewp_get_commissions(array(
        'affiliate_id' => $affiliate_id,
        'number'       => -1,
    ));

    return is_array($commissions) ? $commissions : array();
}

/**
 * Get custom affiliate stats for the current logged-in affiliate.
 */
function axiom_get_current_affiliate_dashboard_stats() {
    global $wpdb;

    $stats = array(
        'conversions'      => 0,
        'conversion_rate'  => 0,
        'total_referrals'  => 0,
        'total_orders'     => 0,
        'total_earnings'   => 0,
        'paid_earnings'    => 0,
        'pending_earnings' => 0,
    );

    $affiliate_id = axiom_get_current_slicewp_affiliate_id();

    if (!$affiliate_id) {
        return $stats;
    }

    $commissions = axiom_get_current_affiliate_commissions();

    $valid_conversion_count = 0;
    $paid_earnings          = 0;
    $pending_earnings       = 0;
    $total_earnings         = 0;

    foreach ($commissions as $commission) {
        $status = strtolower((string) axiom_affiliate_get_object_value($commission, 'status', ''));
        $amount = (float) axiom_affiliate_get_object_value($commission, 'amount', 0);

        if ($status === 'rejected') {
            continue;
        }

        $valid_conversion_count++;
        $total_earnings += $amount;

        if ($status === 'paid') {
            $paid_earnings += $amount;
        } else {
            $pending_earnings += $amount;
        }
    }

    $visits_table = $wpdb->prefix . 'slicewp_visits';
    $visit_count  = 0;

    $table_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $visits_table
        )
    );

    if ($table_exists === $visits_table) {
        $visit_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$visits_table} WHERE affiliate_id = %d",
                $affiliate_id
            )
        );
    }

    $conversion_rate = 0;

    if ($visit_count > 0) {
        $conversion_rate = ($valid_conversion_count / $visit_count) * 100;
    }

    $stats['conversions']      = $valid_conversion_count;
    $stats['conversion_rate']  = round($conversion_rate, 2);
    $stats['total_referrals']  = $visit_count;
    $stats['total_orders']     = $valid_conversion_count;
    $stats['total_earnings']   = $total_earnings;
    $stats['paid_earnings']    = $paid_earnings;
    $stats['pending_earnings'] = $pending_earnings;

    return $stats;
}

/**
 * Get affiliate coupon code.
 */
function axiom_get_current_affiliate_coupon_code() {
    $user_id = get_current_user_id();

    if (!$user_id) {
        return 'PENDING';
    }

    if (function_exists('axiom_affiliate_sync_user_setup')) {
        axiom_affiliate_sync_user_setup($user_id);
    }

    $coupon_code = get_user_meta($user_id, 'axiom_affiliate_coupon_code', true);

    if (!$coupon_code) {
        $coupon_code = get_user_meta($user_id, 'axiom_affiliate_requested_partner_code', true);
    }

    if (!$coupon_code) {
        $user = get_userdata($user_id);
        $coupon_code = $user ? strtoupper(preg_replace('/[^A-Z0-9]/', '', $user->user_login)) . '10' : 'PENDING';
    }

    return axiom_affiliate_dashboard_clean_partner_code($coupon_code);
}

/**
 * Get affiliate referral link.
 */
function axiom_get_current_affiliate_referral_link() {
    $user_id      = get_current_user_id();
    $affiliate_id = axiom_get_current_slicewp_affiliate_id();

    if (!$user_id || !$affiliate_id) {
        return home_url('/');
    }

    /**
     * Use SliceWP official function if available.
     */
    if (function_exists('slicewp_get_affiliate_url')) {
        $url = slicewp_get_affiliate_url($affiliate_id, home_url('/'));

        if ($url) {
            return $url;
        }
    }

    /**
     * Fallback.
     * Your SliceWP setting previously showed affiliate keyword as "aff".
     */
    return add_query_arg('aff', $affiliate_id, home_url('/'));
}

/**
 * Get payment preference label.
 */
function axiom_get_current_affiliate_payment_label() {
    $user_id = get_current_user_id();

    $payment_preference = get_user_meta($user_id, 'axiom_affiliate_payment_preference', true);
    $zelle_contact      = get_user_meta($user_id, 'axiom_affiliate_zelle_contact', true);

    if ($payment_preference === 'store_credit') {
        return array(
            'title' => 'Store Credit',
            'sub'   => 'Added to account wallet',
            'icon'  => '🛍️',
        );
    }

    return array(
        'title' => 'Bank Deposit via Zelle',
        'sub'   => $zelle_contact ? $zelle_contact : 'Zelle contact not added yet',
        'icon'  => '🏦',
    );
}

/**
 * Save dashboard settings.
 */
function axiom_handle_affiliate_dashboard_settings_save() {
    if (!is_user_logged_in()) {
        return '';
    }

    if (
        empty($_POST['axiom_affiliate_settings_nonce']) ||
        !wp_verify_nonce($_POST['axiom_affiliate_settings_nonce'], 'axiom_save_affiliate_settings')
    ) {
        return '';
    }

    $user_id      = get_current_user_id();
    $affiliate_id = axiom_get_current_slicewp_affiliate_id();

    $payment_preference = isset($_POST['axiom_payment_preference'])
        ? sanitize_text_field(wp_unslash($_POST['axiom_payment_preference']))
        : 'manual';

    $zelle_contact = isset($_POST['axiom_zelle_contact'])
        ? sanitize_text_field(wp_unslash($_POST['axiom_zelle_contact']))
        : '';

    $partner_code = isset($_POST['axiom_partner_code'])
        ? axiom_affiliate_dashboard_clean_partner_code(wp_unslash($_POST['axiom_partner_code']))
        : '';

    if (!in_array($payment_preference, array('manual', 'store_credit'), true)) {
        $payment_preference = 'manual';
    }

    update_user_meta($user_id, 'axiom_affiliate_payment_preference', $payment_preference);
    update_user_meta($user_id, 'axiom_affiliate_zelle_contact', $zelle_contact);

    if ($partner_code) {
        update_user_meta($user_id, 'axiom_affiliate_requested_partner_code', $partner_code);
        update_user_meta($user_id, 'axiom_affiliate_coupon_code', $partner_code);

        $coupon_id = (int) get_user_meta($user_id, 'axiom_affiliate_coupon_id', true);

        /**
         * If a coupon already exists, rename it to the requested code if available.
         */
        if ($coupon_id && get_post($coupon_id)) {
            $existing_coupon_id = function_exists('wc_get_coupon_id_by_code') ? wc_get_coupon_id_by_code($partner_code) : 0;

            if (!$existing_coupon_id || (int) $existing_coupon_id === $coupon_id) {
                wp_update_post(array(
                    'ID'         => $coupon_id,
                    'post_title' => $partner_code,
                    'post_name'  => sanitize_title($partner_code),
                ));
            }
        } elseif (function_exists('axiom_affiliate_create_coupon_for_user') && $affiliate_id) {
            axiom_affiliate_create_coupon_for_user($user_id, $affiliate_id);
        }
    }

    if (function_exists('axiom_affiliate_set_slicewp_payout_method') && $affiliate_id) {
        axiom_affiliate_set_slicewp_payout_method($affiliate_id, $payment_preference);
    }

    return 'Settings saved successfully.';
}

/**
 * Dashboard nav.
 */
function axiom_render_affiliate_dashboard_nav($active_tab) {
    $base_url = get_permalink();

    $tabs = array(
        'dashboard'   => array('label' => 'Dashboard',   'icon' => 'fa-solid fa-house'),
        'commissions' => array('label' => 'Commissions', 'icon' => 'fa-solid fa-clock-rotate-left'),
        'wallet'      => array('label' => 'Wallet',      'icon' => 'fa-solid fa-wallet'),
        'settings'    => array('label' => 'Settings',    'icon' => 'fa-solid fa-sliders'),
    );

    ob_start();
    ?>
    <div class="axiom-affiliate-custom-tabs">
        <?php foreach ($tabs as $tab_key => $tab) : ?>
            <a class="<?php echo esc_attr($active_tab === $tab_key ? 'is-active' : ''); ?>"
               href="<?php echo esc_url(add_query_arg('axiom_tab', $tab_key, $base_url)); ?>">
                <i class="<?php echo esc_attr($tab['icon']); ?>"></i>
                <span><?php echo esc_html($tab['label']); ?></span>
            </a>
        <?php endforeach; ?>

        <a class="axiom-affiliate-logout-tab" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Partner card.
 */
function axiom_render_affiliate_partner_card_custom() {
    $coupon_code   = axiom_get_current_affiliate_coupon_code();
    $referral_link = axiom_get_current_affiliate_referral_link();

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
            Share your code or referral link with your audience. When customers make qualifying purchases, your commissions will appear inside your dashboard.
        </p>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Stats cards.
 */
function axiom_render_affiliate_stats_cards($stats) {
    ob_start();
    ?>
    <div class="axiom-affiliate-stats-grid">

        <div class="axiom-affiliate-stat-card">
            <div class="axiom-affiliate-stat-icon">
                <i class="fa-solid fa-check"></i>
            </div>
            <div class="axiom-affiliate-stat-value"><?php echo esc_html($stats['conversions']); ?></div>
            <div class="axiom-affiliate-stat-label">Conversions</div>
        </div>

        <div class="axiom-affiliate-stat-card">
            <div class="axiom-affiliate-stat-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="axiom-affiliate-stat-value"><?php echo esc_html($stats['conversion_rate']); ?>%</div>
            <div class="axiom-affiliate-stat-label">Conversion Rate</div>
        </div>

        <div class="axiom-affiliate-stat-card">
            <div class="axiom-affiliate-stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="axiom-affiliate-stat-value"><?php echo esc_html($stats['total_referrals']); ?></div>
            <div class="axiom-affiliate-stat-label">Total Referrals</div>
        </div>

        <div class="axiom-affiliate-stat-card">
            <div class="axiom-affiliate-stat-icon">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div class="axiom-affiliate-stat-value"><?php echo esc_html($stats['total_orders']); ?></div>
            <div class="axiom-affiliate-stat-label">Total Orders</div>
        </div>

        <div class="axiom-affiliate-stat-card axiom-affiliate-stat-card--accent">
            <div class="axiom-affiliate-stat-icon">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="axiom-affiliate-stat-value"><?php echo wp_kses_post(axiom_affiliate_format_money($stats['total_earnings'])); ?></div>
            <div class="axiom-affiliate-stat-label">Total Earnings</div>
        </div>

        <div class="axiom-affiliate-stat-card">
            <div class="axiom-affiliate-stat-icon">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="axiom-affiliate-stat-value"><?php echo wp_kses_post(axiom_affiliate_format_money($stats['paid_earnings'])); ?></div>
            <div class="axiom-affiliate-stat-label">Paid Earnings</div>
        </div>

    </div>
    <?php
    return ob_get_clean();
}

/**
 * Program details and payout schedule.
 */
function axiom_render_affiliate_program_details() {
    ob_start();
    ?>
    <div class="axiom-affiliate-program-details">
        <h3>Program Details</h3>

        <div class="axiom-affiliate-program-grid">
            <div class="axiom-affiliate-program-card">
                <div class="axiom-affiliate-program-icon">
                    <i class="fa-solid fa-percent"></i>
                </div>
                <div>
                    <div class="axiom-affiliate-program-label">Commission Rate</div>
                    <div class="axiom-affiliate-program-value">10% per sale</div>
                </div>
            </div>

            <div class="axiom-affiliate-program-card">
                <div class="axiom-affiliate-program-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div class="axiom-affiliate-program-label">Cookie Duration</div>
                    <div class="axiom-affiliate-program-value">30 days</div>
                </div>
            </div>
        </div>
    </div>

    <div class="axiom-affiliate-payout-schedule">
        <h3>Payout Schedule</h3>

        <ul>
            <li>Payouts are processed on the <strong>1st and 15th</strong> of each month.</li>
            <li>Only <strong>completed and delivered orders</strong> count toward commissions.</li>
            <li>Cancelled orders are automatically removed from your commission balance.</li>
            <li>Pending commissions are reviewed before payout.</li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Commissions section.
 */
function axiom_render_affiliate_commissions_section() {
    $commissions = axiom_get_current_affiliate_commissions();

    ob_start();
    ?>
    <div class="axiom-affiliate-panel">
        <div class="axiom-affiliate-panel-header">
            <h3>Commission History</h3>
            <p>All commissions from referred customers will appear here.</p>
        </div>

        <?php if (empty($commissions)) : ?>
            <div class="axiom-affiliate-empty-state">
                <div class="axiom-affiliate-empty-icon">💵</div>
                <h4>No commissions yet</h4>
                <p>When your referrals make purchases, your commissions will appear here.</p>
            </div>
        <?php else : ?>
            <div class="axiom-affiliate-table-wrap">
                <table class="axiom-affiliate-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commissions as $commission) : ?>
                            <?php
                            $date   = axiom_affiliate_get_object_value($commission, 'date_created', '');
                            $ref    = axiom_affiliate_get_object_value($commission, 'reference', '');
                            $status = axiom_affiliate_get_object_value($commission, 'status', '');
                            $amount = axiom_affiliate_get_object_value($commission, 'amount', 0);

                            if (!$date) {
                                $date = axiom_affiliate_get_object_value($commission, 'date', '');
                            }
                            ?>
                            <tr>
                                <td><?php echo esc_html($date ? date_i18n('M j, Y', strtotime($date)) : '-'); ?></td>
                                <td><?php echo esc_html($ref ? $ref : '-'); ?></td>
                                <td><?php echo esc_html(ucfirst((string) $status)); ?></td>
                                <td><?php echo wp_kses_post(axiom_affiliate_format_money($amount)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Wallet section.
 */
function axiom_render_affiliate_wallet_section($stats) {
    $payment = axiom_get_current_affiliate_payment_label();

    ob_start();
    ?>
    <div class="axiom-affiliate-wallet-grid">
        <div class="axiom-affiliate-wallet-card">
            <div class="axiom-affiliate-wallet-icon">⏳</div>
            <div>
                <div class="axiom-affiliate-wallet-value"><?php echo wp_kses_post(axiom_affiliate_format_money($stats['pending_earnings'])); ?></div>
                <div class="axiom-affiliate-wallet-label">Pending Payout</div>
                <p>Commissions awaiting the next payout cycle.</p>
            </div>
        </div>

        <div class="axiom-affiliate-wallet-card">
            <div class="axiom-affiliate-wallet-icon">✅</div>
            <div>
                <div class="axiom-affiliate-wallet-value"><?php echo wp_kses_post(axiom_affiliate_format_money($stats['paid_earnings'])); ?></div>
                <div class="axiom-affiliate-wallet-label">Total Paid Out</div>
                <p>Lifetime earnings already paid to you.</p>
            </div>
        </div>
    </div>

    <div class="axiom-affiliate-panel">
        <div class="axiom-affiliate-panel-header">
            <h3>Payout Method</h3>
            <p>How you receive your affiliate earnings.</p>
        </div>

        <div class="axiom-affiliate-payment-method-card">
            <div class="axiom-affiliate-payment-method-icon"><?php echo esc_html($payment['icon']); ?></div>
            <div>
                <h4><?php echo esc_html($payment['title']); ?></h4>
                <p><?php echo esc_html($payment['sub']); ?></p>
            </div>
        </div>
    </div>

    <?php echo axiom_render_affiliate_program_details(); ?>
    <?php
    return ob_get_clean();
}

/**
 * Settings section.
 */
function axiom_render_affiliate_settings_section($message = '') {
    $user_id = get_current_user_id();

    $payment_preference = get_user_meta($user_id, 'axiom_affiliate_payment_preference', true);
    $zelle_contact      = get_user_meta($user_id, 'axiom_affiliate_zelle_contact', true);
    $partner_code       = axiom_get_current_affiliate_coupon_code();

    if (!$payment_preference) {
        $payment_preference = 'manual';
    }

    ob_start();
    ?>
    <div class="axiom-affiliate-panel">
        <div class="axiom-affiliate-panel-header">
            <h3>Affiliate Settings</h3>
            <p>Update your payout preference, Zelle contact, and partner code.</p>
        </div>

        <?php if ($message) : ?>
            <div class="axiom-affiliate-settings-message">
                <?php echo esc_html($message); ?>
            </div>
        <?php endif; ?>

        <form class="axiom-affiliate-settings-form" method="post">
            <?php wp_nonce_field('axiom_save_affiliate_settings', 'axiom_affiliate_settings_nonce'); ?>

            <div class="axiom-affiliate-settings-field">
                <label>Payment Preference</label>

                <div class="axiom-affiliate-settings-options">
                    <label class="<?php echo esc_attr($payment_preference === 'manual' ? 'is-selected' : ''); ?>">
                        <input type="radio" name="axiom_payment_preference" value="manual" <?php checked($payment_preference, 'manual'); ?>>
                        <span>🏦</span>
                        <strong>Bank Deposit</strong>
                        <small>Via Zelle</small>
                    </label>

                    <label class="<?php echo esc_attr($payment_preference === 'store_credit' ? 'is-selected' : ''); ?>">
                        <input type="radio" name="axiom_payment_preference" value="store_credit" <?php checked($payment_preference, 'store_credit'); ?>>
                        <span>🛍️</span>
                        <strong>Store Credit</strong>
                        <small>Added to account wallet</small>
                    </label>
                </div>
            </div>

            <div class="axiom-affiliate-settings-field">
                <label for="axiom_zelle_contact">Zelle Email or Phone</label>
                <input id="axiom_zelle_contact" type="text" name="axiom_zelle_contact" value="<?php echo esc_attr($zelle_contact); ?>" placeholder="john@email.com or 813-555-0000">
            </div>

            <div class="axiom-affiliate-settings-field">
                <label for="axiom_partner_code">Partner Code</label>
                <input id="axiom_partner_code" type="text" name="axiom_partner_code" value="<?php echo esc_attr($partner_code); ?>" placeholder="JOHN10">
                <p>Your partner code is the discount code you share with customers.</p>
            </div>

            <button type="submit" class="axiom-affiliate-settings-save">
                Save Settings
            </button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Shortcode:
 * [axiom_affiliate_dashboard]
 */
add_shortcode('axiom_affiliate_dashboard', 'axiom_render_affiliate_dashboard');

function axiom_render_affiliate_dashboard() {
    if (!function_exists('slicewp_get_affiliate_by_user_id')) {
        return '<p>SliceWP is not active.</p>';
    }

    if (!is_user_logged_in()) {
        return do_shortcode('[slicewp_affiliate_account]');
    }

    if (!axiom_is_current_user_active_affiliate()) {
        return do_shortcode('[slicewp_affiliate_account]');
    }

    $settings_message = axiom_handle_affiliate_dashboard_settings_save();

    $active_tab = isset($_GET['axiom_tab'])
        ? sanitize_key(wp_unslash($_GET['axiom_tab']))
        : 'dashboard';

    if (!in_array($active_tab, array('dashboard', 'commissions', 'wallet', 'settings'), true)) {
        $active_tab = 'dashboard';
    }

    $stats = axiom_get_current_affiliate_dashboard_stats();

    ob_start();
    ?>
    <div class="axiom-affiliate-dashboard-modern axiom-affiliate-home-active">

        <div class="axiom-affiliate-dashboard-header">
            <h2>Affiliate Dashboard</h2>
            <p>Track your partner code, commissions, payouts, and account settings in one place.</p>
        </div>

        <?php echo axiom_render_affiliate_dashboard_nav($active_tab); ?>

        <?php if ($active_tab === 'dashboard') : ?>

            <?php echo axiom_render_affiliate_partner_card_custom(); ?>
            <?php echo axiom_render_affiliate_stats_cards($stats); ?>
            <?php echo axiom_render_affiliate_program_details(); ?>

        <?php elseif ($active_tab === 'commissions') : ?>

            <?php echo axiom_render_affiliate_commissions_section(); ?>

        <?php elseif ($active_tab === 'wallet') : ?>

            <?php echo axiom_render_affiliate_wallet_section($stats); ?>

        <?php elseif ($active_tab === 'settings') : ?>

            <?php echo axiom_render_affiliate_settings_section($settings_message); ?>

        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}

/**
 * Enqueue split affiliate dashboard CSS files + cleanup JS.
 */
add_action('wp_enqueue_scripts', 'axiom_enqueue_affiliate_dashboard_modern_assets', 30);

function axiom_enqueue_affiliate_dashboard_modern_assets() {
    if (!is_singular()) {
        return;
    }

    global $post;

    if (!$post instanceof WP_Post) {
        return;
    }

    if (
        has_shortcode($post->post_content, 'axiom_affiliate_dashboard') ||
        is_page('affiliate-program') ||
        is_page('affiliate-area') ||
        is_page('affiliate-dashboard') ||
        is_page('affiliate-account')
    ) {
        $css_files = array(
            'axiom-affiliate-dashboard-base'     => '/assets/css/affiliate-program/dashboard-base.css',
            'axiom-affiliate-dashboard-cards'    => '/assets/css/affiliate-program/dashboard-cards.css',
            'axiom-affiliate-dashboard-slicewp'  => '/assets/css/affiliate-program/dashboard-slicewp.css',
            'axiom-affiliate-dashboard-metrics'  => '/assets/css/affiliate-program/dashboard-metrics.css',
        );

        foreach ($css_files as $handle => $path) {
            $full_path = get_template_directory() . $path;

            wp_enqueue_style(
                $handle,
                get_template_directory_uri() . $path,
                array(),
                file_exists($full_path) ? filemtime($full_path) : '1.0.0'
            );
        }

        $js_path = get_template_directory() . '/assets/js/affiliate-program/dashboard-cleanup.js';

        wp_enqueue_script(
            'axiom-affiliate-dashboard-cleanup',
            get_template_directory_uri() . '/assets/js/affiliate-program/dashboard-cleanup.js',
            array(),
            file_exists($js_path) ? filemtime($js_path) : '1.0.0',
            true
        );
    }
}
