<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email/SMS popup HTML only.
 * Save logic is handled in:
 * /functions/marketing/leads-system.php
 */
add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <div id="axiom-popup" class="axiom-popup" aria-hidden="true" style="display:none;">
        <div class="axiom-popup__overlay" data-axiom-popup-close></div>

        <div class="axiom-popup__shell" role="dialog" aria-modal="true" aria-labelledby="axiomPopupTitle">
            <button type="button" class="axiom-popup__close" data-axiom-popup-close aria-label="Close popup">×</button>

            <div class="axiom-popup__hero">
                <div class="axiom-popup__brand">
                    <span class="axiom-popup__mark">AX</span>
                    <span>Axiom Peptides</span>
                </div>

                <div class="axiom-popup__offer-row">
                    <div class="axiom-popup__pill">
                        <strong>10%</strong>
                        <span>Email<br>discount</span>
                    </div>

                    <span class="axiom-popup__plus">+</span>

                    <div class="axiom-popup__pill axiom-popup__pill--ghost">
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

            <div class="axiom-popup__body">
                <div id="axiomPopupMessage" class="axiom-popup__message" style="display:none;"></div>

                <div id="axiomStepEmail" class="axiom-popup__step">
                    <label class="axiom-popup__input-wrap" for="axiomPopupEmail">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="axiomPopupEmail" autocomplete="email" placeholder="Enter your email">
                    </label>

                    <button type="button" class="axiom-popup__sms-teaser" id="axiomShowSmsStep">
                        + Add phone number for an extra 5% off
                    </button>

                    <button type="button" class="axiom-popup__btn" id="axiomClaim10">
                        Claim My 10% Discount →
                    </button>
                </div>

                <div id="axiomStepSms" class="axiom-popup__step" style="display:none;">
                    <label class="axiom-popup__input-wrap" for="axiomPopupPhone">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <input type="tel" id="axiomPopupPhone" autocomplete="tel" placeholder="Enter your phone number">
                    </label>

                    <button type="button" class="axiom-popup__btn" id="axiomClaim15">
                        Unlock Full 15% Discount →
                    </button>

                    <button type="button" class="axiom-popup__skip" id="axiomSkipSms">
                        No thanks, just give me 10%
                    </button>
                </div>

                <div id="axiomStepSuccess" class="axiom-popup__success" style="display:none;">
                    <div class="axiom-popup__check">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <h3>You’re In!</h3>

                    <div class="axiom-popup__code-box">
                        <span>Your code</span>
                        <strong id="axiomGeneratedCode">AXIOM</strong>
                        <small>One-time use • expires in 30 days</small>
                    </div>

                    <p id="axiomSuccessText">
                        Apply this code at checkout.
                    </p>

                    <button type="button" class="axiom-popup__btn" id="axiomCopyCode">
                        Copy Code
                    </button>
                </div>

                <div class="axiom-popup__trust">
                    <span>🔬 Research grade</span>
                    <span>🔒 No spam</span>
                    <span>✓ Unsubscribe anytime</span>
                </div>

                <p class="axiom-popup__limited">Limited time — first order only</p>
            </div>
        </div>
    </div>
    <?php
});
