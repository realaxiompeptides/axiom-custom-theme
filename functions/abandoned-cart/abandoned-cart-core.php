<?php
if (!defined('ABSPATH')) exit;

/**
 * CAPTURE CART + EMAIL
 */
add_action('woocommerce_checkout_update_order_review', function($post_data) {
    parse_str($post_data, $data);

    if (empty($data['billing_email'])) return;

    $email = sanitize_email($data['billing_email']);

    if (!WC()->cart) return;

    $cart = WC()->cart->get_cart();
    if (empty($cart)) return;

    $key = md5($email);

    update_option('axiom_abandoned_' . $key, [
        'email' => $email,
        'time' => time(),
        'step1' => false,
        'step2' => false,
        'step3' => false,
    ]);
});

/**
 * CRON JOB
 */
add_action('axiom_abandoned_cart_cron', function() {

    global $wpdb;

    $rows = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'axiom_abandoned_%'");

    foreach ($rows as $row) {
        $data = maybe_unserialize($row->option_value);

        if (empty($data['email'])) continue;

        $elapsed = time() - $data['time'];

        if ($elapsed > 900 && !$data['step1']) {
            axiom_send_abandoned_cart_email($data['email'], 1);
            $data['step1'] = true;
        }

        if ($elapsed > 7200 && !$data['step2']) {
            axiom_send_abandoned_cart_email($data['email'], 2);
            $data['step2'] = true;
        }

        if ($elapsed > 86400 && !$data['step3']) {
            axiom_send_abandoned_cart_email($data['email'], 3);
            $data['step3'] = true;
        }

        update_option($row->option_name, $data);
    }
});

/**
 * CRON INTERVAL
 */
add_filter('cron_schedules', function($schedules) {
    $schedules['every_minute'] = [
        'interval' => 60,
        'display' => 'Every Minute'
    ];
    return $schedules;
});

/**
 * REGISTER CRON
 */
if (!wp_next_scheduled('axiom_abandoned_cart_cron')) {
    wp_schedule_event(time(), 'every_minute', 'axiom_abandoned_cart_cron');
}
