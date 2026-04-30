(function () {
    'use strict';

    const POPUP_KEY = 'axiom_popup_seen_v2';
    const DELAY_AFTER_AGE_GATE = 2500;

    function getPopup() {
        return document.getElementById('axiom-popup');
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

    function isValidPhone(phone) {
        const cleaned = String(phone || '').replace(/[^\d]/g, '');
        return cleaned.length >= 10;
    }

    function showPopup() {
        const popup = getPopup();
        if (!popup) return;

        if (localStorage.getItem(POPUP_KEY)) return;

        popup.style.display = 'block';
        popup.setAttribute('aria-hidden', 'false');
        document.body.classList.add('axiom-popup-open');
    }

    function closePopup() {
        const popup = getPopup();
        if (!popup) return;

        popup.style.display = 'none';
        popup.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('axiom-popup-open');
        localStorage.setItem(POPUP_KEY, '1');
    }

    function waitForAgeGateThenShow() {
        let tries = 0;

        const timer = setInterval(function () {
            tries++;

            const gate =
                document.getElementById('ageGateOverlay') ||
                document.querySelector('.age-gate-overlay');

            const gateIsHidden =
                !gate ||
                gate.getAttribute('aria-hidden') === 'true' ||
                gate.style.display === 'none' ||
                gate.offsetParent === null;

            if (gateIsHidden || tries > 80) {
                clearInterval(timer);
                setTimeout(showPopup, DELAY_AFTER_AGE_GATE);
            }
        }, 300);
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
    }

    function setLoading(isLoading) {
        const buttons = document.querySelectorAll('#axiom-popup button');
        buttons.forEach(function (btn) {
            btn.disabled = !!isLoading;
        });
    }

    function submitLead(percent) {
        const emailInput = document.getElementById('axiomPopupEmail');
        const phoneInput = document.getElementById('axiomPopupPhone');

        const email = emailInput ? emailInput.value.trim() : '';
        const phone = phoneInput ? phoneInput.value.trim() : '';

        if (!isValidEmail(email)) {
            showMessage('Enter a valid email.');
            if (emailInput) emailInput.focus();
            return;
        }

        if (percent === 15 && !isValidPhone(phone)) {
            showMessage('Enter a valid phone number for the extra 5%.');
            if (phoneInput) phoneInput.focus();
            return;
        }

        if (!window.axiom_ajax || !window.axiom_ajax.ajax_url) {
            showMessage('Popup setup error. Please try again.');
            return;
        }

        setLoading(true);

        const body = new URLSearchParams();
        body.append('action', 'axiom_save_lead');
        body.append('email', email);
        body.append('phone', phone);
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
                    showMessage('Could not create your code. Please try again.');
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
        if (!codeEl) return;

        const code = codeEl.textContent.trim();

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function () {
                const btn = document.getElementById('axiomCopyCode');
                if (btn) {
                    btn.textContent = 'Copied ✓';
                    setTimeout(function () {
                        btn.textContent = 'Copy Code';
                    }, 1800);
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        waitForAgeGateThenShow();

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
