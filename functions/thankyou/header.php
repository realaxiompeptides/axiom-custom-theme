<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Thank you page custom payment instructions
 */

function axiom_thankyou_get_btc_address() {
    return 'bc1qa2c4nfzakewrxf9jcj3m8ql3n436jhzn0spgfr';
}

function axiom_thankyou_get_cashapp_logo_url() {
    $primary_file = get_template_directory() . '/assets/images/cashapp.png';
    $primary_url  = get_template_directory_uri() . '/assets/images/cashapp.png';

    if (file_exists($primary_file)) {
        return $primary_url;
    }

    $fallback_file = get_template_directory() . '/assets/images/cash-app.png';
    $fallback_url  = get_template_directory_uri() . '/assets/images/cash-app.png';

    if (file_exists($fallback_file)) {
        return $fallback_url;
    }

    return '';
}

function axiom_thankyou_detect_payment_type($order) {
    if (!$order instanceof WC_Order) {
        return '';
    }

    $payment_method       = strtolower((string) $order->get_payment_method());
    $payment_method_title = strtolower((string) $order->get_payment_method_title());
    $haystack             = trim($payment_method . ' ' . $payment_method_title);

    if (strpos($haystack, 'cash app') !== false || strpos($haystack, 'cashapp') !== false) {
        return 'cashapp';
    }

    if (
        strpos($haystack, 'paymento') !== false ||
        strpos($haystack, 'crypto') !== false ||
        strpos($haystack, 'bitcoin') !== false ||
        strpos($haystack, 'btc') !== false
    ) {
        return 'crypto';
    }

    if (strpos($haystack, 'venmo') !== false) {
        return 'venmo';
    }

    if (strpos($haystack, 'zelle') !== false) {
        return 'zelle';
    }

    return '';
}

function axiom_thankyou_render_styles_once() {
    static $printed = false;

    if ($printed) {
        return;
    }

    $printed = true;
    ?>
    <style>
      .axiom-thankyou-payment-wrap{
        margin:24px 0 0;
      }

      .axiom-thankyou-payment-card{
        border:1px solid #dbe6f2;
        border-radius:30px;
        background:#f8fbff;
        padding:28px;
      }

      .axiom-thankyou-payment-header{
        display:flex;
        align-items:center;
        gap:14px;
        flex-wrap:wrap;
        margin:0 0 24px;
      }

      .axiom-thankyou-payment-header-logo{
        width:52px;
        height:52px;
        min-width:52px;
        border-radius:14px;
        background:#ffffff;
        border:1px solid #dbe6f2;
        display:flex;
        align-items:center;
        justify-content:center;
        overflow:hidden;
      }

      .axiom-thankyou-payment-header-logo img{
        width:34px;
        height:34px;
        object-fit:contain;
        display:block;
      }

      .axiom-thankyou-payment-title{
        display:flex;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
      }

      .axiom-thankyou-payment-title h2{
        margin:0;
        color:#1877f2;
        font-size:28px;
        line-height:1.05;
        font-weight:900;
      }

      .axiom-thankyou-payment-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:42px;
        padding:0 18px;
        border-radius:999px;
        background:linear-gradient(135deg,#66b5ef,#3f90cf);
        color:#ffffff;
        font-size:15px;
        font-weight:900;
        letter-spacing:.02em;
      }

      .axiom-thankyou-payment-section-title{
        margin:0 0 16px;
        color:#617189;
        font-size:17px;
        font-weight:900;
        line-height:1.4;
      }

      .axiom-thankyou-steps{
        margin:0 0 26px 0;
        padding:0;
        list-style:none;
        display:grid;
        gap:14px;
      }

      .axiom-thankyou-steps li{
        display:grid;
        grid-template-columns:40px minmax(0,1fr);
        gap:14px;
        align-items:start;
        color:#6c7a92;
        font-size:18px;
        line-height:1.75;
        font-weight:500;
      }

      .axiom-thankyou-step-number{
        color:#6c7a92;
        font-size:18px;
        font-weight:500;
        line-height:1.75;
      }

      .axiom-thankyou-steps strong{
        color:#617189;
        font-weight:900;
      }

      .axiom-thankyou-label{
        margin:0 0 12px;
        color:#69758d;
        font-size:15px;
        font-weight:900;
        letter-spacing:.04em;
        text-transform:uppercase;
      }

      .axiom-thankyou-copy-row{
        margin:0 0 22px;
      }

      .axiom-thankyou-copy-field{
        width:100%;
        min-height:60px;
        padding:0 24px;
        border:1px solid #dbe6f2;
        border-radius:20px;
        background:#ffffff;
        color:#0f172a;
        font-size:22px;
        font-weight:800;
        box-sizing:border-box;
      }

      .axiom-thankyou-copy-btn{
        width:100%;
        min-height:62px;
        margin-top:14px;
        border:0;
        border-radius:22px;
        background:linear-gradient(135deg,#4aa7e8,#2f84bf);
        color:#ffffff;
        font-size:22px;
        font-weight:900;
        cursor:pointer;
        box-shadow:0 10px 18px rgba(47,132,191,0.14);
      }

      .axiom-thankyou-after-copy{
        margin:14px 0 22px;
        color:#6a768d;
        font-size:18px;
        line-height:1.45;
        font-weight:900;
        text-transform:uppercase;
      }

      .axiom-thankyou-contact-actions{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:18px;
        margin:0 0 22px;
      }

      .axiom-thankyou-contact-btn{
        min-height:62px;
        border:1px solid #dbe6f2;
        border-radius:999px;
        background:#ffffff;
        color:#2c57b7;
        font-size:20px;
        font-weight:900;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        text-decoration:none;
      }

      .axiom-thankyou-note-box{
        margin-top:18px;
        padding:28px;
        border:1px solid #cfe0ef;
        border-radius:28px;
        background:#f8fbff;
      }

      .axiom-thankyou-note-box h3{
        margin:0 0 14px;
        color:#0f172a;
        font-size:22px;
        font-weight:900;
      }

      .axiom-thankyou-note-box ul{
        margin:0;
        padding-left:28px;
        color:#69758d;
      }

      .axiom-thankyou-note-box li{
        margin:0 0 14px;
        font-size:18px;
        line-height:1.75;
      }

      .axiom-thankyou-note-box strong{
        color:#617189;
        font-weight:900;
      }

      @media (max-width: 767px){
        .axiom-thankyou-payment-card{
          padding:20px;
          border-radius:24px;
        }

        .axiom-thankyou-payment-title h2{
          font-size:24px;
        }

        .axiom-thankyou-payment-badge{
          min-height:38px;
          padding:0 16px;
          font-size:14px;
        }

        .axiom-thankyou-payment-section-title{
          font-size:16px;
        }

        .axiom-thankyou-steps li{
          grid-template-columns:34px minmax(0,1fr);
          gap:12px;
          font-size:16px;
          line-height:1.7;
        }

        .axiom-thankyou-step-number{
          font-size:16px;
          line-height:1.7;
        }

        .axiom-thankyou-copy-field{
          min-height:56px;
          padding:0 18px;
          font-size:17px;
        }

        .axiom-thankyou-copy-btn{
          min-height:56px;
          font-size:18px;
          border-radius:20px;
        }

        .axiom-thankyou-after-copy{
          font-size:16px;
        }

        .axiom-thankyou-contact-actions{
          gap:12px;
        }

        .axiom-thankyou-contact-btn{
          min-height:56px;
          font-size:17px;
        }

        .axiom-thankyou-note-box{
          padding:20px;
          border-radius:24px;
        }

        .axiom-thankyou-note-box h3{
          font-size:18px;
        }

        .axiom-thankyou-note-box li{
          font-size:16px;
          line-height:1.65;
        }
      }
    </style>
    <?php
}

function axiom_thankyou_render_copy_script_once() {
    static $printed = false;

    if ($printed) {
        return;
    }

    $printed = true;
    ?>
    <script>
      document.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-axiom-copy-target]');
        if (!btn) return;

        const selector = btn.getAttribute('data-axiom-copy-target');
        const target = document.querySelector(selector);
        if (!target) return;

        let text = '';

        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
          text = target.value || '';
        } else {
          text = target.textContent || '';
        }

        text = text.trim();
        if (!text) return;

        const original = btn.textContent;

        try {
          await navigator.clipboard.writeText(text);
          btn.textContent = 'Copied';
          setTimeout(function () {
            btn.textContent = original;
          }, 1400);
        } catch (err) {
          btn.textContent = 'Copy failed';
          setTimeout(function () {
            btn.textContent = original;
          }, 1400);
        }
      });
    </script>
    <?php
}

function axiom_thankyou_render_cashapp_box($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $order_id      = $order->get_id();
    $btc_address   = axiom_thankyou_get_btc_address();
    $cashapp_logo  = axiom_thankyou_get_cashapp_logo_url();
    $copy_addr_id  = 'axiom-copy-btc-address-' . $order_id;
    $copy_order_id = 'axiom-copy-order-number-' . $order_id;

    axiom_thankyou_render_styles_once();
    axiom_thankyou_render_copy_script_once();
    ?>
    <div class="axiom-thankyou-payment-wrap">
      <div class="axiom-thankyou-payment-card">
        <div class="axiom-thankyou-payment-header">
          <?php if ($cashapp_logo) : ?>
            <div class="axiom-thankyou-payment-header-logo">
              <img src="<?php echo esc_url($cashapp_logo); ?>" alt="Cash App">
            </div>
          <?php endif; ?>

          <div class="axiom-thankyou-payment-title">
            <h2>Cash App</h2>
            <span class="axiom-thankyou-payment-badge">5% OFF</span>
          </div>
        </div>

        <p class="axiom-thankyou-payment-section-title">How to pay with Cash App Bitcoin:</p>

        <ol class="axiom-thankyou-steps">
          <li>
            <span class="axiom-thankyou-step-number">1.</span>
            <span>Open <strong>Cash App</strong> on your phone.</span>
          </li>
          <li>
            <span class="axiom-thankyou-step-number">2.</span>
            <span>Tap the <strong>Bitcoin</strong> tab inside Cash App.</span>
          </li>
          <li>
            <span class="axiom-thankyou-step-number">3.</span>
            <span>If you do not already have Bitcoin, tap <strong>Buy</strong> and purchase enough BTC to cover your order total.</span>
          </li>
          <li>
            <span class="axiom-thankyou-step-number">4.</span>
            <span>Tap <strong>Send</strong> on the Bitcoin screen.</span>
          </li>
          <li>
            <span class="axiom-thankyou-step-number">5.</span>
            <span>Paste our exact Bitcoin address shown below into the recipient field.</span>
          </li>
          <li>
            <span class="axiom-thankyou-step-number">6.</span>
            <span>Double-check the address before sending to make sure it matches exactly.</span>
          </li>
          <li>
            <span class="axiom-thankyou-step-number">7.</span>
            <span>After sending, save your transaction confirmation and send us your order number and payment confirmation.</span>
          </li>
        </ol>

        <p class="axiom-thankyou-label">Bitcoin (BTC) Address</p>

        <div class="axiom-thankyou-copy-row">
          <input
            type="text"
            id="<?php echo esc_attr($copy_addr_id); ?>"
            class="axiom-thankyou-copy-field"
            readonly
            value="<?php echo esc_attr($btc_address); ?>"
          >
          <button
            type="button"
            class="axiom-thankyou-copy-btn"
            data-axiom-copy-target="#<?php echo esc_attr($copy_addr_id); ?>"
          >
            Copy
          </button>
        </div>

        <div class="axiom-thankyou-after-copy">
          After sending payment, message your order number #<?php echo esc_html($order_id); ?> and payment confirmation.
        </div>

        <div class="axiom-thankyou-contact-actions">
          <a class="axiom-thankyou-contact-btn" href="<?php echo esc_url(home_url('/contact-us/')); ?>">WhatsApp</a>
          <a class="axiom-thankyou-contact-btn" href="<?php echo esc_url(home_url('/contact-us/')); ?>">Telegram</a>
        </div>

        <p class="axiom-thankyou-label">Order Number</p>

        <div class="axiom-thankyou-copy-row">
          <input
            type="text"
            id="<?php echo esc_attr($copy_order_id); ?>"
            class="axiom-thankyou-copy-field"
            readonly
            value="#<?php echo esc_attr($order_id); ?>"
          >
          <button
            type="button"
            class="axiom-thankyou-copy-btn"
            data-axiom-copy-target="#<?php echo esc_attr($copy_order_id); ?>"
          >
            Copy
          </button>
        </div>

        <div class="axiom-thankyou-note-box">
          <h3>Good to know</h3>
          <ul>
            <li>You get <strong>5% off</strong> because crypto payments save us processing fees.</li>
            <li>Cash App Bitcoin is usually one of the easiest ways to pay with crypto.</li>
            <li>If you need to buy BTC first in Cash App, it usually only takes a moment before you can send it.</li>
            <li>Confirmation usually takes a short time after sending. Message us with your order number and transaction ID so we can confirm it faster.</li>
          </ul>
        </div>
      </div>
    </div>
    <?php
}

function axiom_thankyou_render_crypto_box($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $order_id      = $order->get_id();
    $btc_address   = axiom_thankyou_get_btc_address();
    $copy_addr_id  = 'axiom-copy-crypto-btc-address-' . $order_id;
    $copy_order_id = 'axiom-copy-crypto-order-number-' . $order_id;

    axiom_thankyou_render_styles_once();
    axiom_thankyou_render_copy_script_once();
    ?>
    <div class="axiom-thankyou-payment-wrap">
      <div class="axiom-thankyou-payment-card">
        <div class="axiom-thankyou-payment-header">
          <div class="axiom-thankyou-payment-title">
            <h2>Bitcoin / Crypto</h2>
            <span class="axiom-thankyou-payment-badge">5% OFF</span>
          </div>
        </div>

        <p class="axiom-thankyou-payment-section-title">Send your Bitcoin payment using the address below:</p>

        <p class="axiom-thankyou-label">Bitcoin (BTC) Address</p>

        <div class="axiom-thankyou-copy-row">
          <input
            type="text"
            id="<?php echo esc_attr($copy_addr_id); ?>"
            class="axiom-thankyou-copy-field"
            readonly
            value="<?php echo esc_attr($btc_address); ?>"
          >
          <button
            type="button"
            class="axiom-thankyou-copy-btn"
            data-axiom-copy-target="#<?php echo esc_attr($copy_addr_id); ?>"
          >
            Copy
          </button>
        </div>

        <div class="axiom-thankyou-after-copy">
          After sending payment, message your order number #<?php echo esc_html($order_id); ?> and payment confirmation.
        </div>

        <p class="axiom-thankyou-label">Order Number</p>

        <div class="axiom-thankyou-copy-row">
          <input
            type="text"
            id="<?php echo esc_attr($copy_order_id); ?>"
            class="axiom-thankyou-copy-field"
            readonly
            value="#<?php echo esc_attr($order_id); ?>"
          >
          <button
            type="button"
            class="axiom-thankyou-copy-btn"
            data-axiom-copy-target="#<?php echo esc_attr($copy_order_id); ?>"
          >
            Copy
          </button>
        </div>

        <div class="axiom-thankyou-note-box">
          <h3>Good to know</h3>
          <ul>
            <li>You get <strong>5% off</strong> because crypto payments save us processing fees.</li>
            <li>Network confirmation can take a short time after sending.</li>
            <li>Message us with your order number and transaction ID so we can confirm it faster.</li>
          </ul>
        </div>
      </div>
    </div>
    <?php
}

function axiom_render_custom_payment_box_on_thankyou($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $payment_type = axiom_thankyou_detect_payment_type($order);

    if ($payment_type === 'cashapp') {
        axiom_thankyou_render_cashapp_box($order);
        return;
    }

    if ($payment_type === 'crypto') {
        axiom_thankyou_render_crypto_box($order);
        return;
    }
}
add_action('woocommerce_thankyou', 'axiom_render_custom_payment_box_on_thankyou', 25);
