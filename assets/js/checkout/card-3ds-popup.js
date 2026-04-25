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

    $(document).on('change click', 'input[name="payment_method"]', function () {
        if (isCardGatewaySelected() && !modalShown) {
            modalShown = true;
            openModal();
        }
    });

    $(document).on('click', '.axiom-card-3ds-close, #axiomCard3dsContinue', function () {
        closeModal();
    });

    $(document).on('checkout_place_order', function () {
        if (isCardGatewaySelected()) {
            $('body').addClass('axiom-processing-card-payment');
            $('#place_order').text('Processing secure verification...');
        }

        return true;
    });

})(jQuery);
