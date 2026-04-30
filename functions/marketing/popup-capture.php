<?php
if (!defined('ABSPATH')) exit;

/**
 * Inject popup HTML
 */
add_action('wp_footer', function () {
?>
<div id="axiom-popup" class="axiom-hidden">

    <div class="axiom-overlay"></div>

    <div class="axiom-modal">

        <!-- STEP 1 EMAIL -->
        <div id="axiom-step-email">
            <h2>Unlock 10% Off</h2>
            <p>Join Axiom Peptides for exclusive research offers.</p>

            <input type="email" id="axiom-email" placeholder="Enter your email">
            <button onclick="axiomNextStep()">Continue</button>
        </div>

        <!-- STEP 2 SMS -->
        <div id="axiom-step-sms" style="display:none;">
            <h2>Unlock Extra 5%</h2>
            <p>Add your phone to get VIP access (15% total)</p>

            <input type="tel" id="axiom-phone" placeholder="Enter your phone">
            <button onclick="axiomSubmitLead()">Get My Discount</button>
        </div>

        <!-- STEP 3 SUCCESS -->
        <div id="axiom-step-done" style="display:none;">
            <h2>Your Code</h2>
            <div class="axiom-code">AXIOM15</div>
            <p>Use at checkout</p>
        </div>

        <span class="axiom-close" onclick="axiomClose()">×</span>
    </div>

</div>

<style>
.axiom-hidden { display:none; }

#axiom-popup {
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    z-index:9999;
}

.axiom-overlay {
    position:absolute;
    width:100%; height:100%;
    background:rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
}

.axiom-modal {
    position:absolute;
    top:50%; left:50%;
    transform:translate(-50%, -50%);
    background:#0c1220;
    padding:30px;
    border-radius:16px;
    width:90%;
    max-width:420px;
    text-align:center;
    color:#fff;
    border:1px solid rgba(59,111,224,0.3);
    box-shadow:0 30px 80px rgba(0,0,0,0.6);
}

.axiom-modal h2 {
    font-size:24px;
    font-weight:800;
}

.axiom-modal p {
    font-size:14px;
    opacity:0.8;
}

.axiom-modal input {
    width:100%;
    padding:14px;
    margin-top:12px;
    border-radius:10px;
    border:none;
    background:#121a30;
    color:#fff;
}

.axiom-modal button {
    width:100%;
    padding:14px;
    margin-top:14px;
    border:none;
    border-radius:10px;
    font-weight:700;
    background:linear-gradient(135deg,#3B6FE0,#5A8CFF);
    color:#fff;
    transition:0.2s;
}

.axiom-modal button:hover {
    transform:scale(1.03);
    box-shadow:0 10px 25px rgba(59,111,224,0.5);
}

.axiom-code {
    font-size:30px;
    font-weight:900;
    margin-top:10px;
    color:#5A8CFF;
}

.axiom-close {
    position:absolute;
    top:12px;
    right:14px;
    cursor:pointer;
    font-size:22px;
}
</style>

<script>
/**
 * Show popup AFTER age gate
 */
function axiomWaitForAgeGate() {
    let check = setInterval(() => {
        if (!document.querySelector('.age-gate-overlay')) {
            clearInterval(check);

            // Only show if not already shown
            if (!localStorage.getItem('axiom_popup_seen')) {
                setTimeout(() => {
                    document.getElementById('axiom-popup').classList.remove('axiom-hidden');
                }, 3000);
            }
        }
    }, 500);
}

document.addEventListener("DOMContentLoaded", axiomWaitForAgeGate);


/**
 * Step 1 → Step 2
 */
function axiomNextStep() {
    let email = document.getElementById('axiom-email').value;

    if (!email || !email.includes('@')) {
        alert('Enter a valid email');
        return;
    }

    document.getElementById('axiom-step-email').style.display = 'none';
    document.getElementById('axiom-step-sms').style.display = 'block';
}

/**
 * Submit BOTH email + SMS
 */
function axiomSubmitLead() {
    let email = document.getElementById('axiom-email').value;
    let phone = document.getElementById('axiom-phone').value;

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=axiom_save_lead&email=${email}&phone=${phone}`
    });

    document.getElementById('axiom-step-sms').style.display = 'none';
    document.getElementById('axiom-step-done').style.display = 'block';

    localStorage.setItem('axiom_popup_seen', '1');
}

/**
 * Close popup
 */
function axiomClose() {
    document.getElementById('axiom-popup').style.display = 'none';
    localStorage.setItem('axiom_popup_seen', '1');
}
</script>

<?php
});


/**
 * Save lead to DB
 */
add_action('wp_ajax_axiom_save_lead', 'axiom_save_lead');
add_action('wp_ajax_nopriv_axiom_save_lead', 'axiom_save_lead');

function axiom_save_lead() {
    global $wpdb;

    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);

    if (!$email) wp_die();

    $wpdb->insert(
        $wpdb->prefix . 'axiom_leads',
        [
            'email' => $email,
            'phone' => $phone,
            'source' => 'popup'
        ]
    );

    wp_die();
}
