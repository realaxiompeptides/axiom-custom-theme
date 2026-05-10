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
 * Get custom affiliate stats for the current logged-in affiliate.
 */
function axiom_get_current_affiliate_dashboard_stats() {
    global $wpdb;

    $stats = array(
        'conversions'     => 0,
        'conversion_rate' => 0,
        'total_referrals' => 0,
        'total_orders'    => 0,
        'total_earnings'  => 0,
        'paid_earnings'   => 0,
    );

    $affiliate = axiom_get_current_slicewp_affiliate();

    if (!$affiliate) {
        return $stats;
    }

    $affiliate_id = (int) axiom_affiliate_get_object_value($affiliate, 'id', 0);

    if (!$affiliate_id) {
        return $stats;
    }

    $commissions = array();

    if (function_exists('slicewp_get_commissions')) {
        $commissions = slicewp_get_commissions(array(
            'affiliate_id' => $affiliate_id,
            'number'       => -1,
        ));
    }

    if (!is_array($commissions)) {
        $commissions = array();
    }

    $valid_conversion_count = 0;
    $paid_earnings          = 0;
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

    $stats['conversions']     = $valid_conversion_count;
    $stats['conversion_rate'] = round($conversion_rate, 2);
    $stats['total_referrals'] = $visit_count;
    $stats['total_orders']    = $valid_conversion_count;
    $stats['total_earnings']  = $total_earnings;
    $stats['paid_earnings']   = $paid_earnings;

    return $stats;
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

    /**
     * Logged out visitors should only see the SliceWP login/account area.
     */
    if (!is_user_logged_in()) {
        return do_shortcode('[slicewp_affiliate_account]');
    }

    /**
     * Logged in but not active affiliates should only see SliceWP's normal message.
     */
    if (!axiom_is_current_user_active_affiliate()) {
        return do_shortcode('[slicewp_affiliate_account]');
    }

    $stats = axiom_get_current_affiliate_dashboard_stats();

    ob_start();
    ?>
    <div class="axiom-affiliate-dashboard-modern axiom-affiliate-home-active">

        <div class="axiom-affiliate-dashboard-header">
            <h2>Affiliate Dashboard</h2>
            <p>Track your performance, referrals, earnings, and program details in one place.</p>
        </div>

        <?php
        /**
         * Partner code/referral card.
         * This shortcode comes from:
         * functions/affiliate-program/affiliate-partner-setup.php
         */
        if (shortcode_exists('axiom_affiliate_partner_card')) {
            echo do_shortcode('[axiom_affiliate_partner_card]');
        }
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

        <div class="axiom-affiliate-default-dashboard">
            <?php echo do_shortcode('[slicewp_affiliate_account]'); ?>
        </div>

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
