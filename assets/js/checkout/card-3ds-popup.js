(function ($) {
    'use strict';

    var modalShown = false;
    var confirmed = false;

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

    function updateInlineNotice() {
        if (isCardGatewaySelected()) {
            $('#axiomCard3dsNotice').slideDown(150);

            if (!modalShown) {
                modalShown = true;
                openModal();
            }
        } else {
            $('#axiomCard3dsNotice').slideUp(150);
        }
    }

    $(document).on('change', 'input[name="payment_method"]', function () {
        updateInlineNotice();
    });

    $(document.body).on('updated_checkout', function () {
        updateInlineNotice();
    });

    $(document).on('change', '#axiomCard3dsConfirm', function () {
        confirmed = $(this).is(':checked');
        $('#axiomCard3dsContinue').prop('disabled', !confirmed);
    });

    $(document).on('click', '#axiomCard3dsContinue', function () {
        confirmed = true;
        closeModal();
    });

    $(document).on('click', '[data-axiom-3ds-close]', function () {
        closeModal();
    });

    $(document).on('checkout_place_order', function () {
        if (isCardGatewaySelected() && !confirmed) {
            openModal();
            return false;
        }

        if (isCardGatewaySelected()) {
            $('body').addClass('axiom-processing-card-payment');
            $('#place_order').text('Processing secure verification...');
        }

        return true;
    });

    $(function () {
        updateInlineNotice();
    });

})(jQuery);
