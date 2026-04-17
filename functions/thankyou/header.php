<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cash App thank you box
 */

function axiom_thankyou_cashapp_btc_address() {
    return 'bc1qa2c4nfzakewrxf9jcj3m8ql3n436jhzn0spgfr';
}

function axiom_thankyou_cashapp_logo_url() {
    $file_1 = get_template_directory() . '/assets/images/cashapp.png';
    $url_1  = get_template_directory_uri() . '/assets/images/cashapp.png';

    if (file_exists($file_1)) {
        return $url_1;
    }

    $file_2 = get_template_directory() . '/assets/images/cash-app.png';
    $url_2  = get_template_directory_uri() . '/assets/images/cash-app.png';

    if (file_exists($file_2)) {
        return $url_2;
    }

    return '';
}

function axiom_thankyou_is_cashapp_order($order) {
    if (!$order instanceof WC_Order) {
        return false;
    }

    $method_id    = strtolower((string) $order->get_payment_method());
    $method_title = strtolower((string) $order->get_payment_method_title());
    $haystack     = trim($method_id . ' ' . $method_title);

    return (strpos($haystack, 'cash app') !== false || strpos($haystack, 'cashapp') !== false);
}

function axiom_thankyou_cashapp_assets_once() {
    static $printed = false;

    if ($printed) {
        return;
    }

    $printed = true;
    ?>
    <style>
      .axiom-cashapp-thankyou-wrap {
        margin: 28px 0 0;
      }

      .axiom-cashapp-thankyou-card {
        border: 1px solid #dbe6f2;
        border-radius: 28px;
        background: #f8fbff;
        padding: 24px;
      }

      .axiom-cashapp-thankyou-header {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 20px;
      }

      .axiom-cashapp-thankyou-logo {
        width: 50px;
        height: 50px;
        min-width: 50px;
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid #dbe6f2;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
      }

      .axiom-cashapp-thankyou-logo img {
        width: 34px;
        height: 34px;
        object-fit: contain;
        display: block;
      }

      .axiom-cashapp-thankyou-title-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
      }

      .axiom-cashapp-thankyou-title {
        margin: 0;
        color: #1877f2;
        font-size: 28px;
        line-height: 1.05;
        font-weight: 900;
      }

      .axiom-cashapp-thankyou-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 18px;
        border-radius: 999px;
        background: linear-gradient(135deg, #66b5ef, #3f90cf);
        color: #ffffff;
        font-size: 15px;
        font-weight: 900;
      }

      .axiom-cashapp-thankyou-subtitle {
        margin: 0 0 16px;
        color: #617189;
        font-size: 17px;
        font-weight: 900;
      }

      .axiom-cashapp-thankyou-steps {
        margin: 0 0 24px;
        padding-left: 24px;
        color: #6c7a92;
      }

      .axiom-cashapp-thankyou-steps li {
        margin-bottom: 12px;
        font-size: 17px;
        line-height: 1.7;
      }

      .axiom-cashapp-thankyou-steps strong {
        color: #617189;
        font-weight: 900;
      }

      .axiom-cashapp-thankyou-label {
        margin: 0 0 10px;
        color: #69758d;
        font-size: 15px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
      }

      .axiom-cashapp-thankyou-copy-field {
        width: 100%;
        min-height: 58px;
        padding: 0 18px;
        border: 1px solid #dbe6f2;
        border-radius: 18px;
        background: #ffffff;
        color: #0f172a;
        font-size: 18px;
        font-weight: 800;
        box-sizing: border-box;
      }

      .axiom-cashapp-thankyou-copy-btn {
        width: 100%;
        min-height: 58px;
        margin-top: 12px;
        border: 0;
        border-radius: 18px;
        background: linear-gradient(135deg, #4aa7e8, #2f84bf);
        color: #ffffff;
        font-size: 20px;
        font-weight: 900;
        cursor: pointer;
      }

      .axiom-cashapp-thankyou-copy-btn:hover {
        opacity: 0.96;
      }

      .axiom-cashapp-thankyou-note {
        margin: 16px 0 20px;
        color: #6a768d;
        font-size: 16px;
        line-height: 1.5;
        font-weight: 900;
        text-transform: uppercase;
      }

      .axiom-cashapp-thankyou-goodtoknow {
        margin-top: 16px;
        padding: 22px;
        border: 1px solid #cfe0ef;
        border-radius: 24px;
        background: #f8fbff;
      }

      .axiom-cashapp-thankyou-goodtoknow h3 {
        margin: 0 0 12px;
        color: #0f172a;
        font-size: 22px;
        font-weight: 900;
      }

      .axiom-cashapp-thankyou-goodtoknow ul {
        margin: 0;
        padding-left: 22px;
        color: #69758d;
      }

      .axiom-cashapp-thankyou-goodtoknow li {
        margin-bottom: 12px;
        font-size: 17px;
        line-height: 1.7;
      }

      .axiom-cashapp-thankyou-goodtoknow strong {
        color: #617189;
        font-weight: 900;
      }

      @media (max-width: 767px) {
        .axiom-cashapp-thankyou-card {
          padding: 20px;
          border-radius: 24px;
        }

        .axiom-cashapp-thankyou-title {
          font-size: 24px;
        }

        .axiom-cashapp-thankyou-badge {
          min-height: 36px;
          padding: 0 16px;
          font-size: 14px;
        }

        .axiom-cashapp-thankyou-subtitle {
          font-size: 16px;
        }

        .axiom-cashapp-thankyou-steps li,
        .axiom-cashapp-thankyou-goodtoknow li {
          font-size: 16px;
          line-height: 1.65;
        }

        .axiom-cashapp-thankyou-copy-field {
          font-size: 16px;
        }

        .axiom-cashapp-thankyou-copy-btn {
          font-size: 18px;
        }

        .axiom-cashapp-thankyou-goodtoknow h3 {
          font-size: 18px;
        }
      }
    </style>

    <script>
      document.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-axiom-copy-target]');
        if (!btn) return;

        const target = document.querySelector(btn.getAttribute('data-axiom-copy-target'));
        if (!target) return;

        const text = (target.value || target.textContent || '').trim();
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

function axiom_render_cashapp_box_on_thankyou($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order || !axiom_thankyou_is_cashapp_order($order)) {
        return;
    }

    $order_number = $order->get_order_number();
    $btc_address  = axiom_thankyou_cashapp_btc_address();
    $logo_url     = axiom_thankyou_cashapp_logo_url();
    $addr_id      = 'axiom-cashapp-btc-address-' . $order_id;
    $order_id_el  = 'axiom-cashapp-order-number-' . $order_id;

    axiom_thankyou_cashapp_assets_once();
    ?>
    <div class="axiom-cashapp-thankyou-wrap">
      <div class="axiom-cashapp-thankyou-card">
        <div class="axiom-cashapp-thankyou-header">
          <?php if ($logo_url) : ?>
            <div class="axiom-cashapp-thankyou-logo">
              <img src="<?php echo esc_url($logo_url); ?>" alt="Cash App">
            </div>
          <?php endif; ?>

          <div class="axiom-cashapp-thankyou-title-row">
            <h2 class="axiom-cashapp-thankyou-title">Cash App</h2>
            <span class="axiom-cashapp-thankyou-badge">5% OFF</span>
          </div>
        </div>

        <p class="axiom-cashapp-thankyou-subtitle">How to pay with Cash App Bitcoin:</p>

        <ol class="axiom-cashapp-thankyou-steps">
          <li>Open <strong>Cash App</strong> on your phone.</li>
          <li>Tap the <strong>Bitcoin</strong> tab inside Cash App.</li>
          <li>If you do not already have Bitcoin, tap <strong>Buy</strong> and purchase enough BTC to cover your order total.</li>
          <li>Tap <strong>Send</strong> on the Bitcoin screen.</li>
          <li>Paste our exact Bitcoin address shown below into the recipient field.</li>
          <li>Double-check the address before sending to make sure it matches exactly.</li>
          <li>After sending, save your transaction confirmation and send us your order number and payment confirmation.</li>
        </ol>

        <p class="axiom-cashapp-thankyou-label">Bitcoin (BTC) Address</p>
        <input
          type="text"
          id="<?php echo esc_attr($addr_id); ?>"
          class="axiom-cashapp-thankyou-copy-field"
          readonly
          value="<?php echo esc_attr($btc_address); ?>"
        >
        <button
          type="button"
          class="axiom-cashapp-thankyou-copy-btn"
          data-axiom-copy-target="#<?php echo esc_attr($addr_id); ?>"
        >
          Copy
        </button>

        <p class="axiom-cashapp-thankyou-note">
          After sending payment, message your order number #<?php echo esc_html($order_number); ?> and payment confirmation.
        </p>

        <p class="axiom-cashapp-thankyou-label">Order Number</p>
        <input
          type="text"
          id="<?php echo esc_attr($order_id_el); ?>"
          class="axiom-cashapp-thankyou-copy-field"
          readonly
          value="#<?php echo esc_attr($order_number); ?>"
        >
        <button
          type="button"
          class="axiom-cashapp-thankyou-copy-btn"
          data-axiom-copy-target="#<?php echo esc_attr($order_id_el); ?>"
        >
          Copy
        </button>

        <div class="axiom-cashapp-thankyou-goodtoknow">
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
add_action('woocommerce_thankyou', 'axiom_render_cashapp_box_on_thankyou', 25);
