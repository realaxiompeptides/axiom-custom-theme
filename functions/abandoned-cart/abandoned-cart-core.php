<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Axiom Abandoned Cart Core
 *
 * Saves checkout emails/carts and sends staged abandoned cart reminders.
 */

/**
 * SAVE EMAIL INPUT
 */
add_action('woocommerce_after_checkout_form', 'axiom_abandoned_cart_capture_email_script');

function axiom_abandoned_cart_capture_email_script() {
    ?>
    <script>
    document.addEventListener('input', function(e) {
        if (e.target && e.target.name === 'billing_email') {
            localStorage.setItem('axiom_email', e.target.value);
        }
    });
    </script>
    <?php
}

/**
 * SAVE CART ON CHECKOUT EXIT
 */
add_action('wp_footer', 'axiom_abandoned_cart_save_on_exit_script');

function axiom_abandoned_cart_save_on_exit_script() {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }

    $nonce = wp_create_nonce('axiom_abandoned_cart');
    ?>
    <script>
    window.addEventListener('beforeunload', function() {
        var email = localStorage.getItem('axiom_email');

        if (!email) {
            return;
        }

        var body = new URLSearchParams();
        body.append('action', 'axiom_save_abandoned_cart');
        body.append('email', email);
        body.append('nonce', '<?php echo esc_js($nonce); ?>');

        if (navigator.sendBeacon) {
            navigator.sendBeacon('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', body);
            return;
        }

        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: body,
            keepalive: true
        });
    });
    </script>
    <?php
}

/**
 * SAVE CART DATA
 */
add_action('wp_ajax_nopriv_axiom_save_abandoned_cart', 'axiom_save_abandoned_cart');
add_action('wp_ajax_axiom_save_abandoned_cart', 'axiom_save_abandoned_cart');

function axiom_save_abandoned_cart() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'axiom_abandoned_cart')) {
        wp_send_json_error(array('message' => 'Invalid nonce.'), 403);
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce cart unavailable.'), 400);
    }

    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

    if (!$email || !is_email($email)) {
        wp_send_json_error(array('message' => 'Invalid email.'), 400);
    }

    $cart = WC()->cart->get_cart();

    if (empty($cart)) {
        wp_send_json_error(array('message' => 'Cart empty.'), 400);
    }

    $cart_items = array();

    foreach ($cart as $cart_item) {
        if (empty($cart_item['product_id'])) {
            continue;
        }

        $product_id   = absint($cart_item['product_id']);
        $variation_id = !empty($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0;
        $quantity     = !empty($cart_item['quantity']) ? absint($cart_item['quantity']) : 1;

        $product = wc_get_product($variation_id ?: $product_id);

        if (!$product) {
            continue;
        }

        $cart_items[] = array(
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'name'         => $product->get_name(),
            'quantity'     => $quantity,
            'price'        => wc_get_price_to_display($product),
            'url'          => get_permalink($product_id),
        );
    }

    if (empty($cart_items)) {
        wp_send_json_error(array('message' => 'No valid cart items.'), 400);
    }

    update_option(
        'axiom_cart_' . md5(strtolower($email)),
        array(
            'email' => $email,
            'cart'  => $cart_items,
            'time'  => time(),
            'sent'  => array(),
        ),
        false
    );

    wp_send_json_success(array('message' => 'Cart saved.'));
}

/**
 * CRON SCHEDULE
 */
add_filter('cron_schedules', 'axiom_abandoned_cart_cron_schedules');

function axiom_abandoned_cart_cron_schedules($schedules) {
    if (!isset($schedules['axiom_every_15_minutes'])) {
        $schedules['axiom_every_15_minutes'] = array(
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display'  => 'Every 15 Minutes',
        );
    }

    return $schedules;
}

add_action('init', 'axiom_abandoned_cart_schedule_cron');

function axiom_abandoned_cart_schedule_cron() {
    if (!wp_next_scheduled('axiom_abandoned_cart_cron')) {
        wp_schedule_event(time() + 5 * MINUTE_IN_SECONDS, 'axiom_every_15_minutes', 'axiom_abandoned_cart_cron');
    }
}

/**
 * CRON RUNNER
 */
add_action('axiom_abandoned_cart_cron', 'axiom_abandoned_cart_cron_runner');

function axiom_abandoned_cart_cron_runner() {
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'axiom_cart_%'"
    );

    if (empty($rows)) {
        return;
    }

    foreach ($rows as $row) {
        $data = maybe_unserialize($row->option_value);

        if (empty($data) || empty($data['email']) || empty($data['cart']) || empty($data['time'])) {
            continue;
        }

        $email = sanitize_email($data['email']);
        $cart  = is_array($data['cart']) ? $data['cart'] : array();
        $time  = absint($data['time']);
        $sent  = !empty($data['sent']) && is_array($data['sent']) ? $data['sent'] : array();

        if (!$email || !is_email($email) || empty($cart) || !$time) {
            continue;
        }

        $elapsed = time() - $time;

        if ($elapsed > 900 && !in_array(1, $sent, true)) {
            axiom_send_abandoned_email($email, $cart, 1);
            $sent[] = 1;
        }

        if ($elapsed > 7200 && !in_array(2, $sent, true)) {
            axiom_send_abandoned_email($email, $cart, 2);
            $sent[] = 2;
        }

        if ($elapsed > 86400 && !in_array(3, $sent, true)) {
            axiom_send_abandoned_email($email, $cart, 3);
            $sent[] = 3;
        }

        update_option(
            $row->option_name,
            array(
                'email' => $email,
                'cart'  => $cart,
                'time'  => $time,
                'sent'  => array_values(array_unique($sent)),
            ),
            false
        );
    }
}

/**
 * EMAIL TEMPLATE FALLBACK
 *
 * This prevents the fatal error:
 * Call to undefined function axiom_abandoned_cart_email_template()
 */
if (!function_exists('axiom_abandoned_cart_email_template')) {
    function axiom_abandoned_cart_email_template($email, $cart, $stage = 1) {
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');

        $subjects = array(
            1 => 'Your research cart is still saved',
            2 => 'Your Axiom Research cart is still waiting',
            3 => 'Final reminder: your cart may expire soon',
        );

        $headline = array(
            1 => 'Your cart is still saved',
            2 => 'Still thinking it over?',
            3 => 'Final cart reminder',
        );

        $intro = array(
            1 => 'You left items in your cart. Inventory may change, so complete checkout while items are still available.',
            2 => 'Your cart is still available. Return to checkout to complete your order.',
            3 => 'This is your final reminder before your saved cart session may expire.',
        );

        ob_start();
        ?>
        <div style="margin:0;padding:0;background:#f6f8fc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
            <div style="max-width:640px;margin:0 auto;padding:24px;">
                <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:20px;overflow:hidden;">
                    <div style="background:#0f172a;padding:26px 22px;text-align:center;color:#ffffff;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#93c5fd;">
                            Axiom Research
                        </div>
                        <h1 style="margin:10px 0 0;font-size:28px;line-height:1.2;color:#ffffff;">
                            <?php echo esc_html($headline[$stage] ?? $headline[1]); ?>
                        </h1>
                    </div>

                    <div style="padding:26px 22px;">
                        <p style="margin:0 0 18px;font-size:16px;line-height:1.65;color:#334155;">
                            <?php echo esc_html($intro[$stage] ?? $intro[1]); ?>
                        </p>

                        <div style="margin:22px 0;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;">
                            <?php foreach ($cart as $item) : ?>
                                <div style="padding:14px 16px;border-bottom:1px solid #e5e7eb;">
                                    <div style="font-weight:800;color:#0f172a;font-size:15px;">
                                        <?php echo esc_html($item['name'] ?? 'Research Product'); ?>
                                    </div>
                                    <div style="font-size:13px;color:#64748b;margin-top:4px;">
                                        Quantity: <?php echo esc_html($item['quantity'] ?? 1); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p style="text-align:center;margin:28px 0;">
                            <a href="<?php echo esc_url($checkout_url); ?>" style="display:inline-block;background:#3B6FE0;color:#ffffff;text-decoration:none;font-weight:900;padding:14px 24px;border-radius:999px;">
                                Return to Checkout
                            </a>
                        </p>

                        <p style="margin:22px 0 0;font-size:13px;line-height:1.65;color:#64748b;">
                            21+ only. Research use only. Not for human consumption. Not intended to diagnose, treat, cure, or prevent any disease.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php

        return array(
            'subject' => $subjects[$stage] ?? $subjects[1],
            'message' => ob_get_clean(),
        );
    }
}

/**
 * SEND EMAIL
 */
function axiom_send_abandoned_email($email, $cart, $stage) {
    if (!$email || !is_email($email) || empty($cart)) {
        return false;
    }

    $email_data = axiom_abandoned_cart_email_template($email, $cart, $stage);

    if (empty($email_data['subject']) || empty($email_data['message'])) {
        return false;
    }

    return wp_mail(
        $email,
        $email_data['subject'],
        $email_data['message'],
        array('Content-Type: text/html; charset=UTF-8')
    );
}
