(function () {
    'use strict';

    const POPUP_KEY = 'axiom_popup_seen_v6';
    const DELAY_AFTER_AGE_GATE = 3000;

    let popupTimer = null;
    let observerStarted = false;

    function getPopup() {
        return document.getElementById('axiom-popup');
    }

    function elementIsVisible(el) {
        if (!el) return false;

        const style = window.getComputedStyle(el);
        const rect = el.getBoundingClientRect();

        return (
            style.display !== 'none' &&
            style.visibility !== 'hidden' &&
            parseFloat(style.opacity || '1') > 0 &&
            rect.width > 0 &&
            rect.height > 0
        );
    }

    function elementText(el) {
        return (el && el.innerText ? el.innerText : '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function isInsideAxiomPopup(el) {
        return !!(el && el.closest && el.closest('#axiom-popup'));
    }

    /**
     * VERY STRICT AGE GATE DETECTION
     * This checks selectors, visible text, and the Enter Site button.
     */
    function isAgeGateActive() {
        const gateSelectors = [
            '#ageGateOverlay',
            '#age-gate-overlay',
            '#ageGate',
            '#age-gate',
            '.age-gate-overlay',
            '.age-gate',
            '.axiom-age-gate',
            '.age-verification',
            '.age-verification-overlay',
            '[data-age-gate]',
            '[data-agegate]',
            '[id*="ageGate"]',
            '[id*="age-gate"]',
            '[class*="ageGate"]',
            '[class*="age-gate"]'
        ];

        for (let i = 0; i < gateSelectors.length; i++) {
            const matches = document.querySelectorAll(gateSelectors[i]);

            for (let j = 0; j < matches.length; j++) {
                const el = matches[j];

                if (isInsideAxiomPopup(el)) continue;

                const ariaHidden = el.getAttribute('aria-hidden');

                if (ariaHidden === 'true') continue;

                if (elementIsVisible(el)) {
                    return true;
                }
            }
        }

        /**
         * Detect visible "Enter Site" button.
         * This is the most important part for your current age gate.
         */
        const clickableEls = document.querySelectorAll('button, a, input[type="button"], input[type="submit"], [role="button"]');

        for (let i = 0; i < clickableEls.length; i++) {
            const el = clickableEls[i];

            if (isInsideAxiomPopup(el)) continue;

            const txt = elementText(el);
            const val = (el.value || '').toLowerCase();

            if ((txt === 'enter site' || val === 'enter site') && elementIsVisible(el)) {
                return true;
            }

            if ((txt === 'exit' || val === 'exit') && elementIsVisible(el)) {
                return true;
            }
        }

        /**
         * Detect visible modal content from the agreement.
         */
        const allEls = document.querySelectorAll('body *');

        for (let i = 0; i < allEls.length; i++) {
            const el = allEls[i];

            if (isInsideAxiomPopup(el)) continue;
            if (!elementIsVisible(el)) continue;

            const txt = elementText(el);

            if (
                txt.includes('21+ access agreement') ||
                txt.includes('research access notice') ||
                txt.includes('i confirm that i am 21 years of age') ||
                txt.includes('in-vitro laboratory research only') ||
                txt.includes('not for human consumption')
            ) {
                return true;
            }
        }

        return false;
    }

    function forceHidePopupWhileAgeGateActive() {
        const popup = getPopup();

        if (!popup) return;

        if (isAgeGateActive()) {
            popup.style.display = 'none';
            popup.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('axiom-popup-open');
        }
    }

    function showMessage(message) {
        const box = document.getElementById('axiomPopupMessage');
        if (!box) return;

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
            { code: 'US', name: 'United States', dial: '+1', min: 10, max: 10, flag: '🇺🇸' }
        ];
    }

    function populateCountrySelector() {
        const select = document.getElementById('axiomPopupCountry');
        if (!select) return;

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

    function showPopup() {
        const popup = getPopup();

        if (!popup) return;
        if (localStorage.getItem(POPUP_KEY)) return;

        /**
         * FINAL BLOCKER:
         * If the 21+ agreement is still visible, DO NOT show.
         */
        if (isAgeGateActive()) {
            forceHidePopupWhileAgeGateActive();
            waitForAgeGateToClose();
            return;
        }

        popup.style.display = 'block';
        popup.setAttribute('aria-hidden', 'false');
        document.body.classList.add('axiom-popup-open');
    }

    function schedulePopupAfterGate() {
        clearTimeout(popupTimer);

        popupTimer = setTimeout(function () {
            if (!isAgeGateActive()) {
                showPopup();
            } else {
                forceHidePopupWhileAgeGateActive();
                waitForAgeGateToClose();
            }
        }, DELAY_AFTER_AGE_GATE);
    }

    function waitForAgeGateToClose() {
        forceHidePopupWhileAgeGateActive();

        const interval = setInterval(function () {
            forceHidePopupWhileAgeGateActive();

            if (!isAgeGateActive()) {
                clearInterval(interval);
                schedulePopupAfterGate();
            }
        }, 300);
    }

    function startAgeGateObserver() {
        if (observerStarted) return;
        observerStarted = true;

        const observer = new MutationObserver(function () {
            forceHidePopupWhileAgeGateActive();

            if (!isAgeGateActive()) {
                schedulePopupAfterGate();
            }
        });

        observer.observe(document.body, {
            attributes: true,
            childList: true,
            subtree: true,
            characterData: true
        });
    }

    function closePopup() {
        const popup = getPopup();
        if (!popup) return;

        popup.style.display = 'none';
        popup.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('axiom-popup-open');

        localStorage.setItem(POPUP_KEY, '1');
    }

    function goToSmsStep() {
        const emailInput = document.getElementById('axiomPopupEmail');
        const email = emailInput ? emailInput.value.trim() : '';

        if (!isValidEmail(email)) {
            showMessage('Enter a valid email first.');
            if (emailInput) emailInput.focus();
            return;
        }

        document.getElementById('axiomStepEmail').style.display = 'none';
        document.getElementById('axiomStepSms').style.display = 'block';

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
            if (emailInput) emailInput.focus();
            return;
        }

        let phoneToSend = '';

        if (percent === 15) {
            if (!isValidPhoneForCountry()) {
                const country = getSelectedCountry();
                showMessage(`Enter a valid ${country.name} phone number.`);

                const phoneInput = document.getElementById('axiomPopupPhone');
                if (phoneInput) phoneInput.focus();

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

                document.getElementById('axiomStepEmail').style.display = 'none';
                document.getElementById('axiomStepSms').style.display = 'none';
                document.getElementById('axiomStepSuccess').style.display = 'block';

                document.getElementById('axiomGeneratedCode').textContent = code;
                document.getElementById('axiomSuccessText').innerHTML =
                    'Apply at checkout for <strong>' + finalPercent + '% off</strong> your first order.';

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

        if (!codeEl || !btn) return;

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

    document.addEventListener('DOMContentLoaded', function () {
        populateCountrySelector();

        /**
         * Start hidden every time.
         */
        forceHidePopupWhileAgeGateActive();

        /**
         * Watch for the age gate closing.
         */
        startAgeGateObserver();
        waitForAgeGateToClose();

        /**
         * Also listen specifically for Enter Site click.
         */
        document.addEventListener('click', function (event) {
            const target = event.target;
            const clickable = target.closest ? target.closest('button, a, [role="button"], input[type="button"], input[type="submit"]') : null;

            if (!clickable) return;
            if (isInsideAxiomPopup(clickable)) return;

            const txt = elementText(clickable);
            const val = (clickable.value || '').toLowerCase();

            if (txt === 'enter site' || val === 'enter site') {
                clearTimeout(popupTimer);

                setTimeout(function () {
                    if (!isAgeGateActive()) {
                        schedulePopupAfterGate();
                    } else {
                        waitForAgeGateToClose();
                    }
                }, 500);
            }
        }, true);

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

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const popup = getPopup();

                if (popup && popup.style.display !== 'none') {
                    closePopup();
                }
            }
        });
    });
})();
