<?php
if (!defined('ABSPATH')) exit;

/**
 * Inject popup into site
 */
add_action('wp_footer', function() {
?>
<div id="axiom-popup" style="display:none;">
    <div class="axiom-popup-overlay"></div>

    <div class="axiom-popup-box">
        <div id="step1">
            <h2>Unlock 10% Off</h2>
            <p>Join Axiom Peptides for exclusive offers.</p>

            <input type="email" id="axiom-email" placeholder="Enter your email">
            <button onclick="axiomStep2()">Continue</button>
        </div>

        <div id="step2" style="display:none;">
            <h2>Unlock Extra 5%</h2>
            <p>Add your phone to get VIP access + total 15% off</p>

            <input type="tel" id="axiom-phone" placeholder="Enter your phone">
            <button onclick="axiomSubmit()">Get My Discount</button>
        </div>

        <div id="step3" style="display:none;">
            <h2>Your Code:</h2>
            <div class="axiom-code">AXIOM15</div>
            <p>Use at checkout</p>
        </div>
    </div>
</div>

<style>
#axiom-popup {
    position: fixed;
    top:0; left:0; right:0; bottom:0;
    z-index:9999;
}

.axiom-popup-overlay {
    position:absolute;
    width:100%; height:100%;
    background:rgba(0,0,0,0.7);
}

.axiom-popup-box {
    position:absolute;
    top:50%; left:50%;
    transform:translate(-50%,-50%);
    background:#111;
    color:#fff;
    padding:30px;
    border-radius:12px;
    width:90%;
    max-width:400px;
    text-align:center;
}

.axiom-popup-box input {
    width:100%;
    padding:12px;
    margin-top:10px;
    border:none;
    border-radius:6px;
}

.axiom-popup-box button {
    margin-top:15px;
    width:100%;
    padding:12px;
    background:#3B6FE0;
    border:none;
    color:#fff;
    font-weight:bold;
    border-radius:6px;
}

.axiom-code {
    font-size:28px;
    font-weight:bold;
    margin-top:10px;
}
</style>

<script>
setTimeout(() => {
    document.getElementById('axiom-popup').style.display = 'block';
}, 5000);

function axiomStep2(){
    document.getElementById('step1').style.display='none';
    document.getElementById('step2').style.display='block';
}

function axiomSubmit(){
    let email = document.getElementById('axiom-email').value;
    let phone = document.getElementById('axiom-phone').value;

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=axiom_save_lead&email=${email}&phone=${phone}`
    });

    document.getElementById('step2').style.display='none';
    document.getElementById('step3').style.display='block';
}
</script>

<?php
});

/**
 * Save lead via AJAX
 */
add_action('wp_ajax_axiom_save_lead', 'axiom_save_lead');
add_action('wp_ajax_nopriv_axiom_save_lead', 'axiom_save_lead');

function axiom_save_lead() {
    global $wpdb;

    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);

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
