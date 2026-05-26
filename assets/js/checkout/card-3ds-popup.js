(function ($) {
    'use strict';

    var modalShown = false;

    function normalizeText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function isCardGatewaySelected() {
        var selected = $('input[name="payment_method"]:checked');

        if (!selected.length) {
            return false;
        }

        var gatewayId = normalizeText(selected.val());
        var paymentMethod = selected.closest('li, .wc_payment_method, .payment_method');
        var methodClass = normalizeText(paymentMethod.attr('class'));
        var labelText = normalizeText(paymentMethod.children('label').first().text());
        var methodText = normalizeText(paymentMethod.text());

        var labelImages = normalizeText(
            paymentMethod.children('label').first().find('img').map(function () {
                return ($(this).attr('src') || '') + ' ' + ($(this).attr('alt') || '');
            }).get().join(' ')
        );

        /*
         * HARD EXCLUSIONS:
         * These payment methods should NEVER trigger the card 3DS popup.
         */
        var isNonCardGateway = (
            gatewayId.indexOf('link') !== -1 ||
            gatewayId.indexOf('bank') !== -1 ||
            gatewayId.indexOf('ach') !== -1 ||
            gatewayId.indexOf('zelle') !== -1 ||
            gatewayId.indexOf('venmo') !== -1 ||
            gatewayId.indexOf('crypto') !== -1 ||
            gatewayId.indexOf('bitcoin') !== -1 ||
            gatewayId.indexOf('manual') !== -1 ||
            gatewayId.indexOf('bacs') !== -1 ||
            gatewayId.indexOf('cod') !== -1 ||

            methodClass.indexOf('link') !== -1 ||
            methodClass.indexOf('bank') !== -1 ||
            methodClass.indexOf('ach') !== -1 ||
            methodClass.indexOf('zelle') !== -1 ||
            methodClass.indexOf('venmo') !== -1 ||
            methodClass.indexOf('crypto') !== -1 ||
            methodClass.indexOf('bitcoin') !== -1 ||
            methodClass.indexOf('manual') !== -1 ||
            methodClass.indexOf('bacs') !== -1 ||
            methodClass.indexOf('cod') !== -1 ||

            labelText.indexOf('same-day bank') !== -1 ||
            labelText.indexOf('bank payment') !== -1 ||
            labelText.indexOf('pay by bank') !== -1 ||
            labelText.indexOf('zelle') !== -1 ||
            labelText.indexOf('venmo') !== -1 ||
            labelText.indexOf('crypto') !== -1 ||
            labelText.indexOf('bitcoin') !== -1 ||
            labelText.indexOf('cash on delivery') !== -1 ||
            labelText.indexOf('bank transfer') !== -1
        );

        if (isNonCardGateway) {
            return false;
        }

        /*
         * CARD ONLY:
         * Quiklie / Lupa / Pay by Card should trigger the popup.
         */
        return (
            gatewayId.indexOf('quik') !== -1 ||
            gatewayId.indexOf('quick') !== -1 ||
            gatewayId.indexOf('quiklie') !== -1 ||
            gatewayId.indexOf('qpay') !== -1 ||
            gatewayId.indexOf('lupa') !== -1 ||
            gatewayId.indexOf('card') !== -1 ||
            gatewayId.indexOf('credit') !== -1 ||
            gatewayId.indexOf('debit') !== -1 ||
            gatewayId.indexOf('stripe') !== -1 ||
            gatewayId.indexOf('bankful') !== -1 ||

            methodClass.indexOf('quik') !== -1 ||
            methodClass.indexOf('quick') !== -1 ||
            methodClass.indexOf('quiklie') !== -1 ||
            methodClass.indexOf('qpay') !== -1 ||
            methodClass.indexOf('lupa') !== -1 ||
            methodClass.indexOf('card') !== -1 ||
            methodClass.indexOf('credit') !== -1 ||
            methodClass.indexOf('debit') !== -1 ||
            methodClass.indexOf('stripe') !== -1 ||
            methodClass.indexOf('bankful') !== -1 ||

            labelText.indexOf('pay by card') !== -1 ||
            labelText.indexOf('credit card') !== -1 ||
            labelText.indexOf('debit card') !== -1 ||
            labelText.indexOf('card payment') !== -1 ||
            labelText.indexOf('visa') !== -1 ||
            labelText.indexOf('mastercard') !== -1 ||
            labelText.indexOf('american express') !== -1 ||
            labelText.indexOf('discover') !== -1 ||

            labelImages.indexOf('quik') !== -1 ||
            labelImages.indexOf('quick') !== -1 ||
            labelImages.indexOf('quiklie') !== -1 ||
            labelImages.indexOf('qpay') !== -1 ||
            labelImages.indexOf('lupa') !== -1 ||
            labelImages.indexOf('visa') !== -1 ||
            labelImages.indexOf('mastercard') !== -1 ||
            labelImages.indexOf('amex') !== -1 ||
            labelImages.indexOf('discover') !== -1 ||

            methodText.indexOf('cardholder name') !== -1 ||
            methodText.indexOf('card number') !== -1 ||
            methodText.indexOf('cvv') !== -1 ||
            methodText.indexOf('cvc') !== -1
        );
    }

    function modalExists() {
        return $('#axiomCard3dsModal').length > 0;
    }

    function openModal() {
        if (!modalExists()) {
            return;
        }

        if (!isCardGatewaySelected()) {
            closeModal();
            return;
        }

        $('#axiomCard3dsModal')
            .addClass('is-active')
            .attr('aria-hidden', 'false');

        $('body').addClass('axiom-card-3ds-open');
    }

    function closeModal() {
        $('#axiomCard3dsModal')
            .removeClass('is-active')
            .attr('aria-hidden', 'true');

        $('body').removeClass('axiom-card-3ds-open');
    }

    function showCardPopupOnce() {
        if (!isCardGatewaySelected()) {
            closeModal();
            return;
        }

        if (modalShown) {
            return;
        }

        modalShown = true;
        openModal();
    }

    function resetIfNonCardSelected() {
        if (!isCardGatewaySelected()) {
            closeModal();
            $('body').removeClass('axiom-processing-card-payment');
            $('#place_order').text('Place order');
        }
    }

    $(document).on('change', 'input[name="payment_method"]', function () {
        resetIfNonCardSelected();
        showCardPopupOnce();
    });

    $(document).on('click', 'input[name="payment_method"]', function () {
        resetIfNonCardSelected();
        showCardPopupOnce();
    });

    $(document.body).on('payment_method_selected updated_checkout', function () {
        setTimeout(function () {
            resetIfNonCardSelected();
        }, 50);
    });

    $(document).on('click', '.axiom-card-3ds-close', function () {
        closeModal();
    });

    $(document).on('click', '#axiomCard3dsContinue', function () {
        closeModal();
    });

    $(document).on('click', '.axiom-card-3ds-overlay', function (event) {
        event.preventDefault();
        event.stopPropagation();

        /*
         * Do nothing.
         * Customer must close with X or the Continue button.
         */
        return false;
    });

    $(document).on('keydown', function (event) {
        if (event.key === 'Escape' && $('#axiomCard3dsModal').hasClass('is-active')) {
            event.preventDefault();

            /*
             * Do nothing.
             * Customer must close with X or the Continue button.
             */
            return false;
        }
    });

    $(document).on('checkout_place_order', function () {
        if (isCardGatewaySelected()) {
            $('body').addClass('axiom-processing-card-payment');
            $('#place_order').text('Processing secure verification...');
        } else {
            $('body').removeClass('axiom-processing-card-payment');
        }

        return true;
    });

    $(document.body).on('checkout_error', function () {
        $('body').removeClass('axiom-processing-card-payment');
        $('#place_order').text('Place order');
    });

    $(function () {
        /*
         * Do not auto-open on checkout page load.
         * Popup only opens when customer clicks/selects card payment.
         */
        resetIfNonCardSelected();
    });

})(jQuery);
