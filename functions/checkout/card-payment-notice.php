<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output the card payment notice under the Place Order button
 * and above the trust icons.
 */
add_action('woocommerce_review_order_after_submit', 'axiom_render_card_payment_notice', 12);

function axiom_render_card_payment_notice() {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }
    ?>
    <div id="axiom-card-payment-notice" class="axiom-card-payment-notice-wrap" style="display:none;" aria-hidden="true">

        <div class="axiom-card-payment-notice axiom-card-payment-notice-red">
            <div class="axiom-card-payment-notice__icon" aria-hidden="true">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <div class="axiom-card-payment-notice__text">
                <strong>Important:</strong>
                For credit or debit card payments,
                <strong>international payments</strong> and
                <strong>online purchases</strong> must be enabled with your bank to avoid declined transactions.
            </div>
        </div>

        <div class="axiom-card-payment-notice axiom-card-payment-notice-blue">
            <div class="axiom-card-payment-notice__icon" aria-hidden="true">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div class="axiom-card-payment-notice__text">
                <strong>Bank verification may be required:</strong>
                After clicking Place Order, you may need to approve the payment through your banking app, SMS code, email code, or secure verification window.
            </div>
        </div>

    </div>
    <?php
}

/**
 * Add CSS + JS for showing the notice only when the card gateway is selected.
 */
add_action('wp_footer', 'axiom_card_payment_notice_assets', 99);

function axiom_card_payment_notice_assets() {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }
    ?>
    <style>
        .axiom-card-payment-notice-wrap {
            display: none;
            margin: 18px 0 14px;
        }

        .axiom-card-payment-notice-wrap.is-visible {
            display: block !important;
        }

        .axiom-card-payment-notice {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin: 0 0 14px;
            padding: 18px;
            border-radius: 16px;
            box-sizing: border-box;
        }

        .axiom-card-payment-notice:last-child {
            margin-bottom: 0;
        }

        .axiom-card-payment-notice-red {
            border: 1px solid #f6d1d1;
            background: #fef2f2;
            color: #7f1d1d;
        }

        .axiom-card-payment-notice-blue {
            border: 1px solid #bfd4ff;
            background: #eef4ff;
            color: #0f172a;
        }

        .axiom-card-payment-notice__icon {
            flex: 0 0 auto;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            line-height: 1;
            margin-top: 2px;
        }

        .axiom-card-payment-notice-red .axiom-card-payment-notice__icon {
            color: #f4b63f;
        }

        .axiom-card-payment-notice-blue .axiom-card-payment-notice__icon {
            color: #3B6FE0;
        }

        .axiom-card-payment-notice__text {
            font-size: 15px;
            line-height: 1.65;
            font-weight: 500;
        }

        .axiom-card-payment-notice__text strong {
            font-weight: 900;
        }

        .axiom-card-payment-notice-red .axiom-card-payment-notice__text,
        .axiom-card-payment-notice-red .axiom-card-payment-notice__text strong {
            color: #7f1d1d;
        }

        .axiom-card-payment-notice-blue .axiom-card-payment-notice__text,
        .axiom-card-payment-notice-blue .axiom-card-payment-notice__text strong {
            color: #0f172a;
        }

        @media (max-width: 767px) {
            .axiom-card-payment-notice-wrap {
                margin: 16px 0 12px;
            }

            .axiom-card-payment-notice {
                gap: 12px;
                margin: 0 0 12px;
                padding: 16px 14px;
                border-radius: 14px;
            }

            .axiom-card-payment-notice__icon {
                width: 24px;
                height: 24px;
                font-size: 20px;
            }

            .axiom-card-payment-notice__text {
                font-size: 14px;
                line-height: 1.6;
            }
        }
    </style>

    <script>
    jQuery(function($) {
        function axiomIsCardGatewaySelected() {
            var $checked = $('form.checkout input[name="payment_method"]:checked');

            if (!$checked.length) {
                return false;
            }

            var gatewayId = String($checked.val() || '').toLowerCase();
            var labelText = '';

            var $paymentBox = $checked.closest('li, .wc_payment_method');

            if ($paymentBox.length) {
                labelText = String($paymentBox.text() || '').toLowerCase();
            }

            var $label = $('label[for="' + $checked.attr('id') + '"]');

            if ($label.length) {
                labelText += ' ' + String($label.text() || '').toLowerCase();
            }

            var haystack = gatewayId + ' ' + labelText;

            return (
                haystack.indexOf('card') !== -1 ||
                haystack.indexOf('credit') !== -1 ||
                haystack.indexOf('debit') !== -1 ||
                haystack.indexOf('visa') !== -1 ||
                haystack.indexOf('mastercard') !== -1 ||
                haystack.indexOf('american express') !== -1 ||
                haystack.indexOf('amex') !== -1 ||
                haystack.indexOf('bankful') !== -1 ||
                haystack.indexOf('stripe') !== -1 ||
                haystack.indexOf('link') !== -1
            );
        }

        function axiomToggleCardPaymentNotice() {
            var $notice = $('#axiom-card-payment-notice');

            if (!$notice.length) {
                return;
            }

            if (axiomIsCardGatewaySelected()) {
                $notice.addClass('is-visible').attr('aria-hidden', 'false');
            } else {
                $notice.removeClass('is-visible').attr('aria-hidden', 'true');
            }
        }

        axiomToggleCardPaymentNotice();

        $('body').on('change click', 'form.checkout input[name="payment_method"]', function() {
            axiomToggleCardPaymentNotice();
        });

        $('body').on('updated_checkout', function() {
            axiomToggleCardPaymentNotice();
        });
    });
    </script>
    <?php
}
