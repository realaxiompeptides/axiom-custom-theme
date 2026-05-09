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
 * Get custom affiliate stats for the current logged-in user.
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

    if (!is_user_logged_in()) {
        return $stats;
    }

    if (!function_exists('slicewp_get_affiliate_by_user_id')) {
        return $stats;
    }

    $affiliate = slicewp_get_affiliate_by_user_id(get_current_user_id());

    if (!$affiliate) {
        return $stats;
    }

    $affiliate_id = (int) axiom_affiliate_get_object_value($affiliate, 'id', 0);

    if (!$affiliate_id) {
        return $stats;
    }

    /**
     * Get commissions via SliceWP.
     * Every non-rejected commission counts as a conversion/order.
     */
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

    /**
     * Get referred visits from SliceWP visits table.
     */
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
 * Output custom affiliate dashboard + default SliceWP dashboard below it.
 *
 * Use shortcode:
 * [axiom_affiliate_dashboard]
 */
add_shortcode('axiom_affiliate_dashboard', 'axiom_render_affiliate_dashboard');

function axiom_render_affiliate_dashboard() {
    if (!function_exists('slicewp_get_affiliate_by_user_id')) {
        return '<p>SliceWP is not active.</p>';
    }

    $stats = axiom_get_current_affiliate_dashboard_stats();

    ob_start();
    ?>
    <div class="axiom-affiliate-dashboard-modern">

        <div class="axiom-affiliate-dashboard-header">
            <h2>Affiliate Dashboard</h2>
            <p>Track your performance, referrals, and earnings in one place.</p>
        </div>

        <div class="axiom-affiliate-stats-grid">

            <div class="axiom-affiliate-stat-card">
                <div class="axiom-affiliate-stat-icon">✅</div>
                <div class="axiom-affiliate-stat-value"><?php echo esc_html($stats['conversions']); ?></div>
                <div class="axiom-affiliate-stat-label">Conversions</div>
            </div>

            <div class="axiom-affiliate-stat-card">
                <div class="axiom-affiliate-stat-icon">📈</div>
                <div class="axiom-affiliate-stat-value"><?php echo esc_html($stats['conversion_rate']); ?>%</div>
                <div class="axiom-affiliate-stat-label">Conversion Rate</div>
            </div>

            <div class="axiom-affiliate-stat-card">
                <div class="axiom-affiliate-stat-icon">👥</div>
                <div class="axiom-affiliate-stat-value"><?php echo esc_html($stats['total_referrals']); ?></div>
                <div class="axiom-affiliate-stat-label">Total Referrals</div>
            </div>

            <div class="axiom-affiliate-stat-card">
                <div class="axiom-affiliate-stat-icon">🛍️</div>
                <div class="axiom-affiliate-stat-value"><?php echo esc_html($stats['total_orders']); ?></div>
                <div class="axiom-affiliate-stat-label">Total Orders</div>
            </div>

            <div class="axiom-affiliate-stat-card axiom-affiliate-stat-card--accent">
                <div class="axiom-affiliate-stat-icon">💰</div>
                <div class="axiom-affiliate-stat-value"><?php echo wp_kses_post(axiom_affiliate_format_money($stats['total_earnings'])); ?></div>
                <div class="axiom-affiliate-stat-label">Total Earnings</div>
            </div>

            <div class="axiom-affiliate-stat-card">
                <div class="axiom-affiliate-stat-icon">⏳</div>
                <div class="axiom-affiliate-stat-value"><?php echo wp_kses_post(axiom_affiliate_format_money($stats['paid_earnings'])); ?></div>
                <div class="axiom-affiliate-stat-label">Paid Earnings</div>
            </div>

        </div>

        <div class="axiom-affiliate-default-dashboard">
            <?php echo do_shortcode('[slicewp_affiliate_account]'); ?>
        </div>

    </div>
    <?php
    return ob_get_clean();
}

/**
 * Enqueue modern affiliate dashboard CSS.
 */
add_action('wp_enqueue_scripts', 'axiom_enqueue_affiliate_dashboard_modern_styles', 30);

function axiom_enqueue_affiliate_dashboard_modern_styles() {
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
        is_page('affiliate-dashboard')
    ) {
        $css_path = get_template_directory() . '/assets/css/affiliate-program/affiliate-dashboard-modern.css';

        wp_enqueue_style(
            'axiom-affiliate-dashboard-modern',
            get_template_directory_uri() . '/assets/css/affiliate-program/affiliate-dashboard-modern.css',
            array(),
            file_exists($css_path) ? filemtime($css_path) : '1.0.0'
        );
    }
}
