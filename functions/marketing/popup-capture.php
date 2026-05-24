<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Axiom Email/SMS Popup HTML only.
 *
 * CSS:
 * /assets/css/popup/popup-layout.css
 * /assets/css/popup/popup-form.css
 * /assets/css/popup/popup-success.css
 * /assets/css/popup/popup-responsive.css
 * /assets/css/popup/popup-launcher.css
 *
 * JS:
 * /assets/js/popup.js
 *
 * Lead/coupon generation:
 * /functions/marketing/leads-system.php
 *
 * SMS country data:
 * /functions/marketing/sms-capture.php
 */
add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }

    $shop_url = home_url('/shop/');

    /**
     * Do NOT render the floating launcher on:
     * - checkout
     * - account page
     * - affiliate program page
     *
     * Show it on basically every other frontend page.
     */
    $show_popup_launcher = true;

    if (
        (function_exists('is_checkout') && is_checkout()) ||
        (function_exists('is_account_page') && is_account_page()) ||
        is_page('affiliate-program')
    ) {
        $show_popup_launcher = false;
    }
    ?>

    <div id="axiom-popup" class="axiom-popup" aria-hidden="true" style="display:none;">
        <div class="axiom-popup-overlay" data-axiom-popup-close></div>

        <div class="axiom-popup-modal" role="dialog" aria-modal="true" aria-labelledby="axiomPopupTitle">

            <button type="button" class="axiom-popup-close" data-axiom-popup-close aria-label="Close popup">
                ×
            </button>

            <div class="axiom-popup-hero">

                <div class="axiom-popup-discount-row">
                    <div class="axiom-popup-discount-pill">
                        <strong>10%</strong>
                        <span>Email<br>discount</span>
                    </div>

                    <div class="axiom-popup-plus">+</div>

                    <div class="axiom-popup-discount-pill axiom-popup-discount-pill-alt">
                        <strong>5%</strong>
                        <span>Add<br>SMS</span>
                    </div>
                </div>

                <h2 id="axiomPopupTitle">
                    Unlock Up to <span>15% Off</span>
                </h2>

                <p>
                    First order only — get research updates, launch alerts, and exclusive offers.
                </p>
            </div>

            <div class="axiom-popup-body">
                <div id="axiomPopupMessage" class="axiom-popup-message" style="display:none;"></div>

                <!-- EMAIL STEP -->
                <div id="axiomStepEmail" class="axiom-popup-step">

                    <label class="axiom-popup-field" for="axiomPopupEmail">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="axiomPopupEmail" autocomplete="email" placeholder="Enter your email">
                    </label>

                    <button type="button" class="axiom-popup-sms-teaser" id="axiomShowSmsStep">
                        <span class="axiom-popup-sms-icon">+</span>

                        <span class="axiom-popup-sms-copy">
                            <strong>Add phone number for an extra 5% off</strong>
                            <small>Unlock the full 15% first-order discount</small>
                        </span>

                        <span class="axiom-popup-sms-arrow">→</span>
                    </button>

                    <button type="button" class="axiom-popup-main-btn" id="axiomClaim10">
                        Claim My 10% Discount →
                    </button>

                    <p class="axiom-popup-marketing-consent">
                        By submitting, you agree to receive email marketing from Axiom Peptides, including research-use product updates, restock alerts, launch alerts, and promotional offers. Unsubscribe anytime.
                    </p>

                    <div class="axiom-popup-trust-row">
                        <span>🔬 Research grade</span>
                        <span>🔒 No spam</span>
                        <span>✓ Unsubscribe anytime</span>
                    </div>

                    <p class="axiom-popup-limited">Limited time — first order only</p>
                </div>

                <!-- SMS STEP -->
                <div id="axiomStepSms" class="axiom-popup-step" style="display:none;">
                    <div class="axiom-popup-sms-title">
                        <h3>Unlock the extra 5%</h3>
                        <p>Add a valid phone number to unlock the full 15% first-order discount.</p>
                    </div>

                    <div class="axiom-phone-row">
                        <label class="axiom-country-wrap" for="axiomPopupCountry">
                            <select id="axiomPopupCountry"></select>
                        </label>

                        <label class="axiom-popup-field axiom-phone-field" for="axiomPopupPhone">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                            <input type="tel" id="axiomPopupPhone" autocomplete="tel" placeholder="Phone number">
                        </label>
                    </div>

                    <button type="button" class="axiom-popup-main-btn" id="axiomClaim15">
                        Unlock Full 15% Discount →
                    </button>

                    <p class="axiom-popup-marketing-consent axiom-popup-sms-consent">
                        By submitting your email and phone number, you agree to receive email and recurring SMS marketing from Axiom Peptides, including research-use product updates, restock alerts, launch alerts, and promotional offers. Message and data rates may apply. Message frequency varies. Reply STOP to opt out of SMS. Unsubscribe from emails anytime.
                    </p>

                    <button type="button" class="axiom-popup-skip-btn" id="axiomSkipSms">
                        No thanks, just give me 10%
                    </button>

                    <div class="axiom-popup-trust-row">
                        <span>🔒 No spam</span>
                        <span>✓ Unsubscribe anytime</span>
                        <span>📲 SMS alerts</span>
                    </div>
                </div>

                <!-- SUCCESS STEP -->
                <div id="axiomStepSuccess" class="axiom-popup-success" style="display:none;">
                    <div class="axiom-popup-check">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <h3>You’re In!</h3>

                    <button
                        type="button"
                        class="axiom-popup-code-box axiom-copy-code-box"
                        id="axiomCopyCodeBox"
                        aria-label="Copy discount code"
                    >
                        <span>Your one-time code</span>

                        <strong id="axiomGeneratedCode">Loading...</strong>

                        <small>
                            <i class="fa-regular fa-copy"></i>
                            <span id="axiomCopyHint">Tap to copy • one-time use • expires in 30 days</span>
                        </small>
                    </button>

                    <p id="axiomSuccessText">
                        Apply this code at checkout.
                    </p>

                    <a
                        href="<?php echo esc_url($shop_url); ?>"
                        class="axiom-popup-main-btn axiom-popup-shop-btn"
                        id="axiomShopNow"
                    >
                        <span>Shop Now</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <p class="axiom-popup-marketing-consent axiom-popup-success-consent">
                        You are subscribed to receive email marketing from Axiom Peptides, including research-use product updates, restock alerts, launch alerts, and promotional offers. Unsubscribe anytime.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($show_popup_launcher) : ?>
        <!-- Floating discount launcher -->
        <button
            type="button"
            id="axiom-popup-launcher"
            class="axiom-popup-launcher"
            aria-label="Open discount popup"
            style="display:none;"
        >
            <span class="axiom-popup-launcher-outer">
                <span class="axiom-popup-launcher-inner">
                    <span class="axiom-popup-launcher-percent" aria-hidden="true">%</span>
                </span>
            </span>
        </button>
    <?php endif; ?>

    <?php
});
