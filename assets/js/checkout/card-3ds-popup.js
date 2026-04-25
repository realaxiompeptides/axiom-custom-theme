(function ($) {
    'use strict';

    var modalShown = false;

    function isCardGatewaySelected() {
        var selected = $('input[name="payment_method"]:checked');
        if (!selected.length) return false;

        var gatewayId = String(selected.val() || '').toLowerCase();
        var labelText = '';

        var paymentBox = selected.closest('li, .wc_payment_method');
        if (paymentBox.length) {
            labelText = paymentBox.text().toLowerCase();
        }

        return (
            gatewayId.includes('card') ||
            gatewayId.includes('credit') ||
            gatewayId.includes('bankful') ||
            gatewayId.includes('stripe') ||
            gatewayId.includes('link') ||
            labelText.includes('card') ||
            labelText.includes('credit') ||
            labelText.includes('debit') ||
            labelText.includes('visa') ||
            labelText.includes('mastercard')
        );
    }

    function openModal() {
        $('#axiomCard3dsModal').addClass('is-active').attr('aria-hidden', 'false');
        $('body').addClass('axiom-card-3ds-open');
    }

    function closeModal() {
        $('#axiomCard3dsModal').removeClass('is-active').attr('aria-hidden', 'true');
        $('body').removeClass('axiom-card-3ds-open');
    }

    function showInlineNoticeOnly() {
        if (isCardGatewaySelected()) {
            $('#axiomCard3dsNotice').stop(true, true).show();
        } else {
            $('#axiomCard3dsNotice').stop(true, true).hide();
            closeModal();
        }
    }

    function handlePaymentMethodClick() {
        if (isCardGatewaySelected()) {
            $('#axiomCard3dsNotice').stop(true, true).slideDown(150);

            if (!modalShown) {
                modalShown = true;
                openModal();
            }
        } else {
            $('#axiomCard3dsNotice').stop(true, true).slideUp(150);
            closeModal();
        }
    }

    $(document).on('change click', 'input[name="payment_method"]', function () {
        handlePaymentMethodClick();
    });

    $(document.body).on('updated_checkout', function () {
        showInlineNoticeOnly();
    });

    $(document).on('change', '#axiomCard3dsConfirm', function () {
        $('#axiomCard3dsContinue').prop('disabled', !$(this).is(':checked'));
    });

    $(document).on('click', '#axiomCard3dsContinue', function () {
        closeModal();
    });

    $(document).on('click', '.axiom-card-3ds-close', function () {
        closeModal();
    });

    $(document).on('checkout_place_order', function () {
        if (isCardGatewaySelected()) {
            $('body').addClass('axiom-processing-card-payment');
            $('#place_order').text('Processing secure verification...');
        }

        return true;
    });

    $(function () {
        showInlineNoticeOnly();
    });

})(jQuery);
