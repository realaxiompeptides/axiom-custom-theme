(function () {
    'use strict';

    function axiomText(el) {
        return (el && el.textContent ? el.textContent : '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function axiomFindFieldByLabel(labelText) {
        var labels = Array.prototype.slice.call(document.querySelectorAll('label'));
        labelText = labelText.toLowerCase();

        for (var i = 0; i < labels.length; i++) {
            var label = labels[i];
            var text = axiomText(label);

            if (text.indexOf(labelText) !== -1) {
                return (
                    label.closest('.slicewp-field-wrapper') ||
                    label.closest('.slicewp-form-field') ||
                    label.closest('.slicewp-field') ||
                    label.closest('p') ||
                    label.parentElement
                );
            }
        }

        return null;
    }

    function axiomFindPaymentField() {
        return (
            axiomFindFieldByLabel('payment preference') ||
            document.querySelector('[name*="payment_preference"]')?.closest('.slicewp-field-wrapper') ||
            document.querySelector('[name*="axiom_payment_preference"]')?.closest('.slicewp-field-wrapper')
        );
    }

    function axiomFindZelleField() {
        return (
            axiomFindFieldByLabel('zelle email or phone') ||
            document.querySelector('[name*="zelle"]')?.closest('.slicewp-field-wrapper') ||
            document.querySelector('[name*="axiom_zelle_contact"]')?.closest('.slicewp-field-wrapper')
        );
    }

    function axiomFindPartnerCodeField() {
        return (
            axiomFindFieldByLabel('your partner code') ||
            axiomFindFieldByLabel('partner code') ||
            document.querySelector('[name*="partner_code"]')?.closest('.slicewp-field-wrapper') ||
            document.querySelector('[name*="axiom_partner_code"]')?.closest('.slicewp-field-wrapper')
        );
    }

    function axiomFindPromoteField() {
        return (
            axiomFindFieldByLabel('how will you promote us') ||
            document.querySelector('[name*="promote"]')?.closest('.slicewp-field-wrapper')
        );
    }

    function axiomBuildPaymentCards(paymentField) {
        if (!paymentField || paymentField.classList.contains('axiom-payment-cards-ready')) {
            return;
        }

        var radios = Array.prototype.slice.call(paymentField.querySelectorAll('input[type="radio"]'));

        if (!radios.length) {
            return;
        }

        paymentField.classList.add('axiom-payment-cards-ready');

        var wrapper = radios[0].closest('ul') || radios[0].parentElement.parentElement || paymentField;
        wrapper.classList.add('axiom-payment-preference-cards');

        radios.forEach(function (radio) {
            var label = null;

            if (radio.id) {
                label = paymentField.querySelector('label[for="' + radio.id + '"]');
            }

            if (!label) {
                label = radio.closest('label');
            }

            if (!label) {
                label = radio.nextElementSibling;
            }

            if (!label) {
                return;
            }

            var raw = (label.textContent || radio.value || '').toLowerCase();
            var isStoreCredit = raw.indexOf('store') !== -1;

            var title = isStoreCredit ? 'Store Credit' : 'Bank Deposit';
            var subtitle = isStoreCredit ? 'Added to wallet' : 'Via Zelle';
            var icon = isStoreCredit ? '🛍️' : '🏦';

            label.classList.add('axiom-payment-preference-card');

            label.innerHTML =
                '<span class="axiom-payment-preference-card-icon">' + icon + '</span>' +
                '<span class="axiom-payment-preference-card-title">' + title + '</span>' +
                '<span class="axiom-payment-preference-card-subtitle">' + subtitle + '</span>' +
                (isStoreCredit ? '<span class="axiom-payment-preference-bonus">+10% BONUS</span>' : '');

            if (radio.parentElement !== label) {
                label.insertBefore(radio, label.firstChild);
            }

            radio.checked = false;
            label.classList.remove('is-selected');

            radio.addEventListener('change', function () {
                axiomUpdatePaymentState();
            });
        });
    }

    function axiomSelectedPaymentType(paymentField) {
        if (!paymentField) {
            return '';
        }

        var checked = paymentField.querySelector('input[type="radio"]:checked');

        if (!checked) {
            return '';
        }

        var label = checked.closest('label');
        var text = (axiomText(label) + ' ' + String(checked.value || '').toLowerCase());

        if (text.indexOf('store') !== -1) {
            return 'store_credit';
        }

        if (
            text.indexOf('manual') !== -1 ||
            text.indexOf('zelle') !== -1 ||
            text.indexOf('bank') !== -1
        ) {
            return 'bank_deposit';
        }

        return text;
    }

    function axiomUpdatePaymentState() {
        var paymentField = axiomFindPaymentField();
        var zelleField = axiomFindZelleField();

        if (!paymentField) {
            return;
        }

        var cards = Array.prototype.slice.call(paymentField.querySelectorAll('.axiom-payment-preference-card'));

        cards.forEach(function (card) {
            var radio = card.querySelector('input[type="radio"]');
            card.classList.toggle('is-selected', !!(radio && radio.checked));
        });

        if (!zelleField) {
            return;
        }

        var selected = axiomSelectedPaymentType(paymentField);
        var zelleInputs = Array.prototype.slice.call(zelleField.querySelectorAll('input, textarea, select'));

        if (selected !== 'bank_deposit') {
            zelleField.classList.add('axiom-zelle-hidden');
            zelleField.classList.remove('axiom-zelle-field-active');

            zelleInputs.forEach(function (input) {
                input.required = false;
                input.removeAttribute('required');
                input.value = '';
            });

            return;
        }

        zelleField.classList.remove('axiom-zelle-hidden');
        zelleField.classList.add('axiom-zelle-field-active');

        zelleInputs.forEach(function (input) {
            input.required = true;
            input.setAttribute('required', 'required');
        });
    }

    function axiomAddPartnerCodeHelper() {
        var field = axiomFindPartnerCodeField();

        if (!field || field.classList.contains('axiom-partner-helper-ready')) {
            return;
        }

        field.classList.add('axiom-partner-helper-ready');
        field.classList.add('axiom-partner-code-field');

        var helper = document.createElement('div');
        helper.className = 'axiom-partner-code-preview';
        helper.textContent = 'Your affiliate link will appear here as you type';

        field.appendChild(helper);

        var input = field.querySelector('input, textarea');

        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            var raw = input.value || '';
            var cleaned = raw.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 18);

            if (input.value !== cleaned) {
                input.value = cleaned;
            }

            if (cleaned.length) {
                helper.textContent = 'Requested code: ' + cleaned;
            } else {
                helper.textContent = 'Your affiliate link will appear here as you type';
            }
        });
    }

    function axiomAddFieldClasses() {
        var zelleField = axiomFindZelleField();
        var partnerField = axiomFindPartnerCodeField();
        var promoteField = axiomFindPromoteField();

        if (zelleField) {
            zelleField.classList.add('axiom-zelle-contact-field');
        }

        if (partnerField) {
            partnerField.classList.add('axiom-partner-code-field');
        }

        if (promoteField) {
            promoteField.classList.add('axiom-promote-field');
        }
    }

    function axiomRenameSubmitButton() {
        var submits = Array.prototype.slice.call(document.querySelectorAll(
            '.axiom-affiliate-form-wrap input[type="submit"], .axiom-affiliate-form-wrap button[type="submit"], input[type="submit"], button[type="submit"]'
        ));

        submits.forEach(function (submit) {
            if (submit.tagName === 'INPUT') {
                submit.value = 'Create Partner Account →→';
            } else {
                submit.textContent = 'Create Partner Account →→';
            }
        });
    }

    function axiomInitAffiliateRegistrationFields() {
        var paymentField = axiomFindPaymentField();

        if (paymentField) {
            axiomBuildPaymentCards(paymentField);
            axiomUpdatePaymentState();
        }

        axiomAddFieldClasses();
        axiomAddPartnerCodeHelper();
        axiomRenameSubmitButton();
    }

    document.addEventListener('DOMContentLoaded', function () {
        axiomInitAffiliateRegistrationFields();
        setTimeout(axiomInitAffiliateRegistrationFields, 500);
        setTimeout(axiomInitAffiliateRegistrationFields, 1200);
    });

    window.addEventListener('load', function () {
        axiomInitAffiliateRegistrationFields();
        setTimeout(axiomInitAffiliateRegistrationFields, 1000);
    });
})();
