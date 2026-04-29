<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SAVE EMAIL INPUT
 */
add_action('woocommerce_after_checkout_form', function () {
?>
<script>
document.addEventListener('input', function(e){
    if(e.target.name === "billing_email"){
        localStorage.setItem('axiom_email', e.target.value);
    }
});
</script>
<?php
});

/**
 * SAVE CART ON EXIT
 */
add_action('wp_footer', function () {
    if (!is_checkout()) return;
?>
<script>
window.addEventListener('beforeunload', function () {

    let email = localStorage.getItem('axiom_email');
    if (!email) return;

    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=axiom_save_abandoned_cart&email=' + encodeURIComponent(email)
    });

});
</script>
<?php
});

/**
 * SAVE CART DATA
 */
add_action('wp_ajax_nopriv_axiom_save_abandoned_cart', 'axiom_save_abandoned_cart');
add_action('wp_ajax_axiom_save_abandoned_cart', 'axiom_save_abandoned_cart');

function axiom_save_abandoned_cart() {

    if (!function_exists('WC')) return;

    $email = sanitize_email($_POST['email']);
    if (!$email) return;

    $cart = WC()->cart->get_cart();

    update_option('axiom_cart_' . md5($email), array(
        'email' => $email,
        'cart'  => $cart,
        'time'  => time(),
        'sent'  => array() // track sent emails
    ));
}

/**
 * CRON
 */
add_filter('cron_schedules', function ($schedules) {
    $schedules['minute'] = array(
        'interval' => 60,
        'display'  => 'Every Minute'
    );
    return $schedules;
});

add_action('init', function () {
    if (!wp_next_scheduled('axiom_abandoned_cart_cron')) {
        wp_schedule_event(time(), 'minute', 'axiom_abandoned_cart_cron');
    }
});

add_action('axiom_abandoned_cart_cron', function () {

    global $wpdb;

    $rows = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'axiom_cart_%'");

    foreach ($rows as $row) {

        $data = maybe_unserialize($row->option_value);
        if (!$data) continue;

        $email = $data['email'];
        $cart  = $data['cart'];
        $time  = $data['time'];
        $sent  = $data['sent'];

        $elapsed = time() - $time;

        // STAGE 1 (15 MIN)
        if ($elapsed > 900 && !in_array(1, $sent)) {
            axiom_send_abandoned_email($email, $cart, 1);
            $sent[] = 1;
        }

        // STAGE 2 (2 HOURS)
        if ($elapsed > 7200 && !in_array(2, $sent)) {
            axiom_send_abandoned_email($email, $cart, 2);
            $sent[] = 2;
        }

        // STAGE 3 (24 HOURS)
        if ($elapsed > 86400 && !in_array(3, $sent)) {
            axiom_send_abandoned_email($email, $cart, 3);
            $sent[] = 3;
        }

        update_option($row->option_name, array(
            'email' => $email,
            'cart'  => $cart,
            'time'  => $time,
            'sent'  => $sent
        ));
    }
});

/**
 * SEND EMAIL
 */
function axiom_send_abandoned_email($email, $cart, $stage) {

    if (!$email || empty($cart)) return;

    $email_data = axiom_abandoned_cart_email_template($email, $cart, $stage);

    wp_mail(
        $email,
        $email_data['subject'],
        $email_data['message'],
        array('Content-Type: text/html; charset=UTF-8')
    );
}
