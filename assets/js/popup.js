(function () {
    'use strict';

    const POPUP_KEY = 'axiom_popup_seen_v9';
    const AGE_GATE_KEY = 'axiom_age_gate_accepted_v1';
    const DELAY_AFTER_GATE_CLOSE = 3000;

    /**
     * If visitor came from an affiliate/referral link, never show email/SMS popup.
     */
    const AFFILIATE_TRAFFIC_KEY = 'axiom_affiliate_traffic_v1';

    let popupTimeout = null;
    let ageGateWatchInterval = null;

    function getPopup() {
        return document.getElementById('axiom-popup');
    }

    function getLauncher() {
        return document.getElementById('axiom-popup-launcher');
    }

    /**
     * Hide launcher only on:
     * - checkout
     * - account page
     * - affiliate program page
     *
     * Do NOT hide it on product pages or cart pages.
     */
    function isBlockedLauncherPage() {
        const body = document.body;
        const path = String(window.location.pathname || '').toLowerCase();

        if (!body) {
            return false;
        }

        return (
            body.classList.contains('woocommerce-checkout') ||
            body.classList.contains('checkout') ||
            body.classList.contains('woocommerce-account') ||
            body.classList.contains('page-template-affiliate-program-template') ||
            path.indexOf('/checkout') !== -1 ||
            path.indexOf('/my-account') !== -1 ||
            path.indexOf('/affiliate-program') !== -1
        );
    }

    /**
     * Detect affiliate traffic from URL params or SliceWP cookies.
     */
    function isAffiliateTraffic() {
        const params = new URLSearchParams(window.location.search);

        const affiliateParams = [
            'ref',
            'aff',
            'affiliate',
            'affiliate_id',
            'referral',
            'slicewp_ref',
            'swp_ref',
            'slicewp_affiliate',
            'affiliate_code'
        ];

        for (let i = 0; i < affiliateParams.length; i++) {
            if (params.get(affiliateParams[i])) {
                localStorage.setItem(AFFILIATE_TRAFFIC_KEY, '1');
                return true;
            }
        }

        const cookies = String(document.cookie || '').toLowerCase();

        if (
            cookies.indexOf('slicewp') !== -1 ||
            cookies.indexOf('affiliate') !== -1 ||
            cookies.indexOf('referral') !== -1
        ) {
            localStorage.setItem(AFFILIATE_TRAFFIC_KEY, '1');
            return true;
        }

        return localStorage.getItem(AFFILIATE_TRAFFIC_KEY) === '1';
    }

    function hideLauncher() {
        const launcher = getLauncher();

        if (!launcher) {
            return;
        }

        launcher.style.display = 'none';
        launcher.setAttribute('aria-hidden', 'true');
    }

    function showLauncher() {
        const launcher = getLauncher();

        if (!launcher) {
            return;
        }

        if (isAffiliateTraffic()) {
            hideLauncher();
            return;
        }

        if (isBlockedLauncherPage()) {
            hideLauncher();
            return;
        }

        if (!ageGateAccepted()) {
            hideLauncher();
            return;
        }

        launcher.style.display = 'flex';
        launcher.setAttribute('aria-hidden', 'false');
    }

    function hidePopup() {
        const popup = getPopup();

        if (!popup) {
            return;
        }

        popup.style.display = 'none';
        popup.setAttribute('aria-hidden', 'true');

        if (document.body) {
            document.body.classList.remove('axiom-popup-open');
        }
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
            showLauncher();
            return;
        }

        if (isAffiliateTraffic()) {
            hidePopup();
            hideLauncher();
            return;
        }

        if (isBlockedLauncherPage()) {
            hidePopup();
            hideLauncher();
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
            showLauncher();
            return;
        }

        if (isAffiliateTraffic()) {
            hidePopup();
            hideLauncher();
            return;
        }

        if (isBlockedLauncherPage()) {
            hidePopup();
            hideLauncher();
            return;
        }

        if (!ageGateAccepted()) {
            hidePopup();
            waitForAgeGateAccepted();
            return;
        }

        hideLauncher();

        popup.style.display = 'block';
        popup.setAttribute('aria-hidden', 'false');

        if (document.body) {
            document.body.classList.add('axiom-popup-open');
        }
    }

    /**
     * Opens popup from floating % icon.
     * This ignores POPUP_KEY because the visitor manually reopened it.
     */
    function reopenPopupFromLauncher() {
        const popup = getPopup();

        if (!popup) {
            return;
        }

        if (isAffiliateTraffic()) {
            hidePopup();
            hideLauncher();
            return;
        }

        if (isBlockedLauncherPage()) {
            hidePopup();
            hideLauncher();
            return;
        }

        if (!ageGateAccepted()) {
            hidePopup();
            hideLauncher();
            waitForAgeGateAccepted();
            return;
        }

        hideLauncher();

        popup.style.display = 'block';
        popup.setAttribute('aria-hidden', 'false');

        if (document.body) {
            document.body.classList.add('axiom-popup-open');
        }
    }

    function waitForAgeGateAccepted() {
        hidePopup();
        hideLauncher();

        clearInterval(ageGateWatchInterval);

        ageGateWatchInterval = setInterval(function () {
            if (isAffiliateTraffic()) {
                clearInterval(ageGateWatchInterval);
                hidePopup();
                hideLauncher();
                return;
            }

            if (isBlockedLauncherPage()) {
                clearInterval(ageGateWatchInterval);
                hidePopup();
                hideLauncher();
                return;
            }

            if (ageGateAccepted()) {
                clearInterval(ageGateWatchInterval);

                if (popupAlreadySeen()) {
                    showLauncher();
                } else {
                    schedulePopup();
                }
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

        if (document.body) {
            document.body.classList.remove('axiom-popup-open');
        }

        localStorage.setItem(POPUP_KEY, '1');

        /**
         * Show floating % icon after the user closes popup,
         * except on checkout/account/affiliate pages.
         */
        showLauncher();
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
                hideLauncher();
            })
            .catch(function () {
                setLoading(false);
                showMessage('Something went wrong. Please try again.');
            });
    }

    function copyCode() {
        const codeEl = document.getElementById('axiomGeneratedCode');
        const hintEl = document.getElementById('axiomCopyHint');
        const boxEl = document.getElementById('axiomCopyCodeBox');

        if (!codeEl) {
            return;
        }

        const code = codeEl.textContent.trim();

        if (!code || code === 'Loading...' || code === 'Copied ✓') {
            return;
        }

        const originalCodeText = code;
        const originalHintText = hintEl
            ? hintEl.textContent
            : 'Tap to copy • one-time use • expires in 30 days';

        function showCopied() {
            codeEl.textContent = 'Copied ✓';

            if (hintEl) {
                hintEl.textContent = 'Discount code copied';
            }

            if (boxEl) {
                boxEl.classList.add('is-copied');
            }

            setTimeout(function () {
                codeEl.textContent = originalCodeText;

                if (hintEl) {
                    hintEl.textContent = originalHintText;
                }

                if (boxEl) {
                    boxEl.classList.remove('is-copied');
                }
            }, 1400);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(originalCodeText).then(showCopied).catch(function () {
                fallbackCopy(originalCodeText);
                showCopied();
            });
        } else {
            fallbackCopy(originalCodeText);
            showCopied();
        }
    }

    function fallbackCopy(code) {
        const temp = document.createElement('textarea');

        temp.value = code;
        temp.setAttribute('readonly', 'readonly');
        temp.style.position = 'fixed';
        temp.style.left = '-9999px';
        temp.style.top = '-9999px';

        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
    }

    function bindPopupEvents() {
        document.querySelectorAll('[data-axiom-popup-close]').forEach(function (el) {
            el.addEventListener('click', closePopup);
        });

        const launcher = getLauncher();

        if (launcher) {
            launcher.addEventListener('click', function () {
                reopenPopupFromLauncher();
            });
        }

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

        const copyCodeBox = document.getElementById('axiomCopyCodeBox');

        if (copyCodeBox) {
            copyCodeBox.addEventListener('click', copyCode);
        }

        const ageEnterBtn = document.getElementById('ageGateEnterBtn');

        if (ageEnterBtn) {
            ageEnterBtn.addEventListener('click', function () {
                hidePopup();
                hideLauncher();

                setTimeout(function () {
                    if (isAffiliateTraffic()) {
                        hidePopup();
                        hideLauncher();
                        return;
                    }

                    if (isBlockedLauncherPage()) {
                        hidePopup();
                        hideLauncher();
                        return;
                    }

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
        hideLauncher();
        populateCountrySelector();
        bindPopupEvents();

        if (isAffiliateTraffic()) {
            hidePopup();
            hideLauncher();
            return;
        }

        if (isBlockedLauncherPage()) {
            hidePopup();
            hideLauncher();
            return;
        }

        if (popupAlreadySeen()) {
            showLauncher();
            return;
        }

        if (ageGateAccepted()) {
            schedulePopup();
        } else {
            waitForAgeGateAccepted();
        }
    });
})();
