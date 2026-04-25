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

    function modalExists() {
        return $('#axiomCard3dsModal').length > 0;
    }

    function openModal() {
        if (!modalExists()) {
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
        }
    }

    $(document).on('change', 'input[name="payment_method"]', function () {
        showCardPopupOnce();
        resetIfNonCardSelected();
    });

    $(document).on('click', 'input[name="payment_method"]', function () {
        showCardPopupOnce();
        resetIfNonCardSelected();
    });

    $(document.body).on('updated_checkout', function () {
        /*
         * Important:
         * Do NOT close or reopen the modal here.
         * WooCommerce refreshes checkout after payment method changes.
         * The popup is rendered in wp_footer so it stays on screen.
         */
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
