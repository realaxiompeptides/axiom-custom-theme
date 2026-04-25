<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', 'axiom_enqueue_card_3ds_popup_assets', 30);

function axiom_enqueue_card_3ds_popup_assets() {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }

    $theme_uri  = get_template_directory_uri();
    $theme_path = get_template_directory();

    wp_enqueue_style(
        'axiom-card-3ds-popup',
        $theme_uri . '/assets/css/checkout/card-3ds-popup.css',
        array('axiom-base', 'axiom-checkout-layout'),
        file_exists($theme_path . '/assets/css/checkout/card-3ds-popup.css')
            ? filemtime($theme_path . '/assets/css/checkout/card-3ds-popup.css')
            : '1.0.0'
    );

    wp_enqueue_script(
        'axiom-card-3ds-popup',
        $theme_uri . '/assets/js/checkout/card-3ds-popup.js',
        array('jquery'),
        file_exists($theme_path . '/assets/js/checkout/card-3ds-popup.js')
            ? filemtime($theme_path . '/assets/js/checkout/card-3ds-popup.js')
            : '1.0.0',
        true
    );
}

add_action('woocommerce_review_order_before_submit', 'axiom_render_card_3ds_popup_html', 20);

function axiom_render_card_3ds_popup_html() {
    ?>
    <div id="axiomCard3dsNotice" class="axiom-card-3ds-inline" style="display:none;">
        <strong>Card payment verification required.</strong>
        After placing your order, your bank may ask you to approve the payment. Please do not close the checkout page until verification is complete.
    </div>

    <div id="axiomCard3dsModal" class="axiom-card-3ds-modal" aria-hidden="true">
        <div class="axiom-card-3ds-overlay" data-axiom-3ds-close></div>

        <div class="axiom-card-3ds-box" role="dialog" aria-modal="true" aria-labelledby="axiomCard3dsTitle">
            <button type="button" class="axiom-card-3ds-close" data-axiom-3ds-close aria-label="Close">×</button>

            <div class="axiom-card-3ds-icon">🔐</div>

            <p class="axiom-card-3ds-kicker">Secure Card Payment</p>

            <h2 id="axiomCard3dsTitle">Bank verification is required</h2>

            <p class="axiom-card-3ds-lead">
                To complete your order, your card payment may require approval through your bank’s 3D Secure verification.
            </p>

            <div class="axiom-card-3ds-panel">
                <h3>After clicking “Place Order”</h3>
                <ul>
                    <li>Keep this checkout page open.</li>
                    <li>Check your banking app for an approval request.</li>
                    <li>Complete any SMS, email, or browser verification code.</li>
                    <li>Wait until the payment fully finishes before closing the page.</li>
                </ul>
            </div>

            <div class="axiom-card-3ds-panel axiom-card-3ds-panel-blue">
                <h3>For international cards</h3>
                <ul>
                    <li>Make sure international payments are enabled.</li>
                    <li>Make sure online purchases are enabled.</li>
                    <li>If your bank blocks the payment, approve it in your bank app and try again.</li>
                </ul>
            </div>

            <label class="axiom-card-3ds-check">
                <input type="checkbox" id="axiomCard3dsConfirm">
                <span>I understand I must complete bank verification so my order is received.</span>
            </label>

            <button type="button" id="axiomCard3dsContinue" class="axiom-card-3ds-button" disabled>
                I Understand — Continue
            </button>

            <p class="axiom-card-3ds-small">
                If verification is skipped or closed too early, the payment may fail automatically.
            </p>
        </div>
    </div>
    <?php
}
