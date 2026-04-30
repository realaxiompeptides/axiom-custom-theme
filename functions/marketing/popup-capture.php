<?php
if (!defined('ABSPATH')) exit;

add_action('wp_footer', function () {
?>
<div id="axiom-popup" style="display:none;">

    <div class="axiom-overlay"></div>

    <div class="axiom-modal">

        <!-- HEADER -->
        <div class="axiom-header">
            <h2>Unlock Up to 15% Off</h2>
            <p>10% email + 5% SMS — first order only</p>
        </div>

        <!-- EMAIL STEP -->
        <div id="axiom-step-email">
            <input type="email" id="axiom-email" placeholder="Enter your email">
            <button onclick="axiomNextStep()">Claim 10% Discount →</button>
        </div>

        <!-- SMS STEP -->
        <div id="axiom-step-sms" style="display:none;">
            <input type="tel" id="axiom-phone" placeholder="Add phone for +5%">
            <button onclick="axiomSubmitLead()">Unlock Full 15%</button>
        </div>

        <!-- SUCCESS -->
        <div id="axiom-step-done" style="display:none;">
            <div class="axiom-check">✔</div>
            <h3>You're In</h3>

            <div class="axiom-code">WELCOME10</div>

            <p>Apply at checkout</p>
        </div>

        <span class="axiom-close" onclick="axiomClose()">×</span>

    </div>
</div>
<?php
});
