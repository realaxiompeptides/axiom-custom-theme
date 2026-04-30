(function () {
    'use strict';

    const POPUP_KEY = 'axiom_popup_seen_v8';
    const AGE_GATE_KEY = 'axiom_age_gate_accepted_v1';
    const DELAY_AFTER_GATE_CLOSE = 3000;

    let popupTimeout = null;
    let ageGateWatchInterval = null;

    function getPopup() {
        return document.getElementById('axiom-popup');
    }

    function hidePopup() {
        const popup = getPopup();

        if (!popup) {
            return;
        }

        popup.style.display = 'none';
        popup.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('axiom-popup-open');
    }

    function ageGateAccepted() {
        return localStorage.getItem(AGE_GATE_KEY) === 'true';
    }

    function popupAlreadySeen() {
        return localStorage.getItem(POPUP_KEY) === '1';
    }

    function schedulePopup() {
        clearTimeout(popupTimeout);

        if (popupAlreadySeen()) {
            return;
        }

        popupTimeout = setTimeout(function () {
            showPopup();
        }, DELAY_AFTER_GATE_CLOSE);
    }

    function showPopup() {
        const popup = getPopup();

        if (!popup) {
            return;
        }

        if (popupAlreadySeen()) {
            return;
        }

        /**
         * Main safety rule:
         * Do NOT show the popup unless the 21+ gate has been accepted.
         */
        if (!ageGateAccepted()) {
            hidePopup();
            waitForAgeGateAccepted();
            return;
        }

        popup.style.display = 'block';
        popup.setAttribute('aria-hidden', 'false');
        document.body.classList.add('axiom-popup-open');
    }

    function waitForAgeGateAccepted() {
        hidePopup();

        clearInterval(ageGateWatchInterval);

        ageGateWatchInterval = setInterval(function () {
            if (popupAlreadySeen()) {
                clearInterval(ageGateWatchInterval);
                return;
            }

            if (ageGateAccepted()) {
                clearInterval(ageGateWatchInterval);
                schedulePopup();
            }
        }, 300);
    }

    function closePopup() {
        const popup = getPopup();

        if (!popup) {
            return;
        }

        popup.style.display = 'none';
        popup.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('axiom-popup-open');

        localStorage.setItem(POPUP_KEY, '1');
    }

    function showMessage(message) {
        const box = document.getElementById('axiomPopupMessage');

        if (!box) {
            return;
        }

        box.textContent = message;
        box.style.display = 'block';

        setTimeout(function () {
            box.style.display = 'none';
        }, 4200);
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || '').trim());
    }

    function getCountries() {
        if (
            window.AXIOM_SMS_COUNTRIES &&
            Array.isArray(window.AXIOM_SMS_COUNTRIES.countries)
        ) {
            return window.AXIOM_SMS_COUNTRIES.countries;
        }

        return [
            {
                code: 'US',
                name: 'United States',
                dial: '+1',
                min: 10,
                max: 10,
                flag: '🇺🇸'
            }
        ];
    }

    function populateCountrySelector() {
        const select = document.getElementById('axiomPopupCountry');

        if (!select) {
            return;
        }

        const countries = getCountries();

        const defaultCountry =
            window.AXIOM_SMS_COUNTRIES && window.AXIOM_SMS_COUNTRIES.defaultCountry
                ? window.AXIOM_SMS_COUNTRIES.defaultCountry
                : 'US';

        select.innerHTML = '';

        countries.forEach(function (country) {
            const option = document.createElement('option');

            option.value = country.code;
            option.textContent = `${country.flag} ${country.dial}`;
            option.dataset.dial = country.dial;
            option.dataset.min = country.min;
            option.dataset.max = country.max;
            option.dataset.name = country.name;

            select.appendChild(option);
        });

        select.value = defaultCountry;
    }

    function getSelectedCountry() {
        const select = document.getElementById('axiomPopupCountry');

        if (!select || !select.selectedOptions || !select.selectedOptions[0]) {
            return {
                code: 'US',
                dial: '+1',
                min: 10,
                max: 10,
                name: 'United States'
            };
        }

        const option = select.selectedOptions[0];

        return {
            code: option.value,
            dial: option.dataset.dial || '+1',
            min: parseInt(option.dataset.min || '10', 10),
            max: parseInt(option.dataset.max || '10', 10),
            name: option.dataset.name || 'United States'
        };
    }

    function normalizePhone(phone) {
        return String(phone || '').replace(/\D+/g, '');
    }

    function getFullPhoneNumber() {
        const input = document.getElementById('axiomPopupPhone');
        const country = getSelectedCountry();

        let digits = normalizePhone(input ? input.value : '');

        if (country.code === 'US' || country.code === 'CA') {
            if (digits.length === 11 && digits.charAt(0) === '1') {
                digits = digits.substring(1);
            }
        }

        return {
            raw: input ? input.value.trim() : '',
            digits: digits,
            full: country.dial + digits,
            country: country
        };
    }

    function isValidPhoneForCountry() {
        const phone = getFullPhoneNumber();
        const length = phone.digits.length;

        return length >= phone.country.min && length <= phone.country.max;
    }

    function goToSmsStep() {
        const emailInput = document.getElementById('axiomPopupEmail');
        const email = emailInput ? emailInput.value.trim() : '';

        if (!isValidEmail(email)) {
            showMessage('Enter a valid email first.');

            if (emailInput) {
                emailInput.focus();
            }

            return;
        }

        const emailStep = document.getElementById('axiomStepEmail');
        const smsStep = document.getElementById('axiomStepSms');

        if (emailStep) {
            emailStep.style.display = 'none';
        }

        if (smsStep) {
            smsStep.style.display = 'block';
        }

        const phoneInput = document.getElementById('axiomPopupPhone');

        if (phoneInput) {
            setTimeout(function () {
                phoneInput.focus();
            }, 100);
        }
    }

    function setLoading(isLoading) {
        const buttons = document.querySelectorAll('#axiom-popup button');

        buttons.forEach(function (btn) {
            btn.disabled = !!isLoading;
        });
    }

    function submitLead(percent) {
        const emailInput = document.getElementById('axiomPopupEmail');
        const email = emailInput ? emailInput.value.trim() : '';

        if (!isValidEmail(email)) {
            showMessage('Enter a valid email.');

            if (emailInput) {
                emailInput.focus();
            }

            return;
        }

        let phoneToSend = '';

        if (percent === 15) {
            if (!isValidPhoneForCountry()) {
                const country = getSelectedCountry();

                showMessage(`Enter a valid ${country.name} phone number.`);

                const phoneInput = document.getElementById('axiomPopupPhone');

                if (phoneInput) {
                    phoneInput.focus();
                }

                return;
            }

            phoneToSend = getFullPhoneNumber().full;
        }

        if (!window.axiom_ajax || !window.axiom_ajax.ajax_url) {
            showMessage('Popup setup error. Please try again.');
            return;
        }

        setLoading(true);

        const body = new URLSearchParams();

        body.append('action', 'axiom_save_lead');
        body.append('email', email);
        body.append('phone', phoneToSend);
        body.append('discount_percent', String(percent));
        body.append('nonce', window.axiom_ajax.nonce || '');

        fetch(window.axiom_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                setLoading(false);

                if (!data || !data.success || !data.data || !data.data.code) {
                    showMessage(
                        data && data.data && data.data.message
                            ? data.data.message
                            : 'Could not create your code. Please try again.'
                    );

                    return;
                }

                const code = data.data.code;
                const finalPercent = data.data.discount_percent || percent;

                const emailStep = document.getElementById('axiomStepEmail');
                const smsStep = document.getElementById('axiomStepSms');
                const successStep = document.getElementById('axiomStepSuccess');
                const codeBox = document.getElementById('axiomGeneratedCode');
                const successText = document.getElementById('axiomSuccessText');

                if (emailStep) {
                    emailStep.style.display = 'none';
                }

                if (smsStep) {
                    smsStep.style.display = 'none';
                }

                if (successStep) {
                    successStep.style.display = 'block';
                }

                if (codeBox) {
                    codeBox.textContent = code;
                }

                if (successText) {
                    successText.innerHTML =
                        'Apply at checkout for <strong>' + finalPercent + '% off</strong> your first order.';
                }

                localStorage.setItem(POPUP_KEY, '1');
            })
            .catch(function () {
                setLoading(false);
                showMessage('Something went wrong. Please try again.');
            });
    }

    function copyCode() {
        const codeEl = document.getElementById('axiomGeneratedCode');
        const btn = document.getElementById('axiomCopyCode');

        if (!codeEl || !btn) {
            return;
        }

        const code = codeEl.textContent.trim();

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function () {
                btn.textContent = 'Copied ✓';

                setTimeout(function () {
                    btn.textContent = 'Copy Code';
                }, 1800);
            });
        } else {
            const temp = document.createElement('textarea');

            temp.value = code;
            document.body.appendChild(temp);
            temp.select();

            document.execCommand('copy');

            document.body.removeChild(temp);

            btn.textContent = 'Copied ✓';

            setTimeout(function () {
                btn.textContent = 'Copy Code';
            }, 1800);
        }
    }

    function bindPopupEvents() {
        document.querySelectorAll('[data-axiom-popup-close]').forEach(function (el) {
            el.addEventListener('click', closePopup);
        });

        const showSmsBtn = document.getElementById('axiomShowSmsStep');

        if (showSmsBtn) {
            showSmsBtn.addEventListener('click', goToSmsStep);
        }

        const claim10 = document.getElementById('axiomClaim10');

        if (claim10) {
            claim10.addEventListener('click', function () {
                submitLead(10);
            });
        }

        const claim15 = document.getElementById('axiomClaim15');

        if (claim15) {
            claim15.addEventListener('click', function () {
                submitLead(15);
            });
        }

        const skipSms = document.getElementById('axiomSkipSms');

        if (skipSms) {
            skipSms.addEventListener('click', function () {
                submitLead(10);
            });
        }

        const copyBtn = document.getElementById('axiomCopyCode');

        if (copyBtn) {
            copyBtn.addEventListener('click', copyCode);
        }

        const ageEnterBtn = document.getElementById('ageGateEnterBtn');

        if (ageEnterBtn) {
            ageEnterBtn.addEventListener('click', function () {
                hidePopup();

                setTimeout(function () {
                    if (ageGateAccepted()) {
                        schedulePopup();
                    } else {
                        waitForAgeGateAccepted();
                    }
                }, 500);
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const popup = getPopup();

                if (popup && popup.style.display !== 'none') {
                    closePopup();
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        hidePopup();
        populateCountrySelector();
        bindPopupEvents();

        if (popupAlreadySeen()) {
            return;
        }

        if (ageGateAccepted()) {
            schedulePopup();
        } else {
            waitForAgeGateAccepted();
        }
    });
})();
