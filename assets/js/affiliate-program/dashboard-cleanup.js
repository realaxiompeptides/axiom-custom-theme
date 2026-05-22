(function () {
    'use strict';

    /* =========================================================
       Basic helpers
    ========================================================= */

    function axiomText(el) {
        return (el && el.textContent ? el.textContent : '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function axiomValue(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function axiomHref(el) {
        return (el && el.getAttribute ? (el.getAttribute('href') || '') : '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function axiomIsSettingsTab() {
        var url = window.location.href.toLowerCase();

        return (
            url.indexOf('affiliate-account-tab=settings') !== -1 ||
            url.indexOf('tab=settings') !== -1
        );
    }

    function axiomIsPayoutTab() {
        var url = window.location.href.toLowerCase();

        return (
            url.indexOf('affiliate-account-tab=payouts') !== -1 ||
            url.indexOf('tab=payouts') !== -1
        );
    }

    function axiomFindCouponIntroText(sliceArea) {
        if (!sliceArea) {
            return null;
        }

        return Array.prototype.slice.call(sliceArea.querySelectorAll('p, div'))
            .find(function (el) {
                return axiomText(el).indexOf('the following coupons have been linked') !== -1;
            }) || null;
    }

    function axiomIsCouponsTab(sliceArea) {
        if (!sliceArea) {
            return false;
        }

        return !!sliceArea.querySelector(
            '.slicewp-nav-tab.slicewp-active[data-slicewp-tab="coupons"], ' +
            '.slicewp-nav-tab.active[data-slicewp-tab="coupons"], ' +
            '.slicewp-nav-tab.current[data-slicewp-tab="coupons"]'
        );
    }

    function axiomIsInsideNav(el) {
        return !!(
            el &&
            el.closest(
                '.slicewp-tabs-nav, .slicewp-user-dashboard-nav, .slicewp-nav-tab-wrapper'
            )
        );
    }

    function axiomIsProtectedAxiomSection(el) {
        return !!(
            el &&
            el.closest(
                '.axiom-affiliate-dashboard-header, .axiom-affiliate-stats-grid, .axiom-affiliate-program-details, .axiom-partner-card'
            )
        );
    }

    function axiomHide(el) {
        if (!el) {
            return;
        }

        if (axiomIsInsideNav(el)) {
            return;
        }

        if (axiomIsProtectedAxiomSection(el)) {
            return;
        }

        el.classList.add('axiom-slicewp-duplicate-home-block');
    }

    function axiomUnhideCleanup(sliceArea) {
        if (!sliceArea) {
            return;
        }

        sliceArea.querySelectorAll('.axiom-slicewp-duplicate-home-block').forEach(function (el) {
            el.classList.remove('axiom-slicewp-duplicate-home-block');
        });
    }

    function axiomShouldHideNavButton(link) {
        var text = axiomText(link);
        var href = axiomHref(link);

        return (
            text.indexOf('creative') !== -1 ||
            text.indexOf('creatives') !== -1 ||
            href.indexOf('creative') !== -1 ||
            href.indexOf('creatives') !== -1
        );
    }

    function axiomHideBadNavButtons(sliceArea) {
        if (!sliceArea) {
            return;
        }

        var navLinks = sliceArea.querySelectorAll(
            '.slicewp-tabs-nav a, .slicewp-user-dashboard-nav a, .slicewp-nav-tab-wrapper a'
        );

        navLinks.forEach(function (link) {
            var wrapper = link.closest('li') || link;

            if (axiomShouldHideNavButton(link)) {
                wrapper.style.display = 'none';
                wrapper.setAttribute('data-axiom-hidden-tab', 'true');
            }
        });
    }

    function axiomHandlePayoutScheduleVisibility(dashboard, sliceArea) {
        if (!dashboard || !sliceArea) {
            return;
        }

        var payoutSchedule = dashboard.querySelector('.axiom-affiliate-payout-schedule');

        if (!payoutSchedule) {
            return;
        }

        var shouldShow = axiomIsSettingsTab() || axiomIsPayoutTab();

        if (!shouldShow) {
            payoutSchedule.style.display = 'none';
            return;
        }

        payoutSchedule.style.display = '';

        var nav =
            sliceArea.querySelector('.slicewp-tabs-nav') ||
            sliceArea.querySelector('.slicewp-user-dashboard-nav') ||
            sliceArea.querySelector('.slicewp-nav-tab-wrapper');

        if (nav && nav.parentNode && payoutSchedule.previousElementSibling !== nav) {
            nav.parentNode.insertBefore(payoutSchedule, nav.nextSibling);
        }
    }

    function axiomFindChartBlock(sliceArea) {
        if (!sliceArea) {
            return null;
        }

        var canvas = sliceArea.querySelector('canvas');

        if (!canvas) {
            return null;
        }

        return (
            canvas.closest('.slicewp-card') ||
            canvas.closest('.slicewp-box') ||
            canvas.closest('.slicewp-panel') ||
            canvas.closest('[class*="chart"]') ||
            canvas.closest('section') ||
            canvas.closest('div')
        );
    }

    function axiomHideDuplicateBottomAfterChart(sliceArea) {
        if (!sliceArea) {
            return;
        }

        var chartBlock = axiomFindChartBlock(sliceArea);

        if (!chartBlock) {
            return;
        }

        var allElements = Array.prototype.slice.call(sliceArea.querySelectorAll('*'));

        allElements.forEach(function (el) {
            if (el === chartBlock || chartBlock.contains(el)) {
                return;
            }

            if (axiomIsInsideNav(el) || axiomIsProtectedAxiomSection(el)) {
                return;
            }

            var position = chartBlock.compareDocumentPosition(el);
            var isAfterChart = !!(position & Node.DOCUMENT_POSITION_FOLLOWING);

            if (!isAfterChart) {
                return;
            }

            var text = axiomText(el);

            var isDuplicateAfterChart =
                text === 'all time' ||
                text === 'program details' ||
                text.indexOf('paid earnings') !== -1 ||
                text.indexOf('unpaid earnings') !== -1 ||
                text.indexOf('commission rate') !== -1 ||
                text.indexOf('sale rate') !== -1 ||
                text.indexOf('cookie duration') !== -1 ||
                text === '30 days';

            if (isDuplicateAfterChart) {
                axiomHide(el);
            }
        });
    }

    function axiomHideLooseDuplicateHeadings(sliceArea) {
        if (!sliceArea) {
            return;
        }

        sliceArea.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, strong').forEach(function (el) {
            if (axiomIsInsideNav(el) || axiomIsProtectedAxiomSection(el)) {
                return;
            }

            var text = axiomText(el);

            if (text === 'all time' || text === 'program details') {
                axiomHide(el);
            }
        });
    }

    function axiomFindAffiliateSettingsForm(sliceArea) {
        if (!sliceArea) {
            return null;
        }

        return (
            sliceArea.querySelector('form') ||
            document.querySelector('.axiom-affiliate-default-dashboard form') ||
            document.querySelector('.slicewp-section-settings form') ||
            document.querySelector('.slicewp-affiliate-account form') ||
            document.querySelector('.slicewp-user-dashboard form')
        );
    }

    function axiomEnsureHiddenInput(form, name) {
        if (!form || !name) {
            return null;
        }

        var input = form.querySelector('input[name="' + name + '"]');

        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            form.appendChild(input);
        }

        return input;
    }

    function axiomGetLabelForInput(input) {
        if (!input) {
            return null;
        }

        var id = input.getAttribute('id');

        if (id) {
            var directLabel = document.querySelector('label[for="' + id + '"]');

            if (directLabel) {
                return directLabel;
            }
        }

        return input.closest('label');
    }

    function axiomFindFieldWrapperByLabelText(sliceArea, textNeedle) {
        if (!sliceArea) {
            return null;
        }

        textNeedle = String(textNeedle || '').toLowerCase();

        var labels = sliceArea.querySelectorAll('label');

        for (var i = 0; i < labels.length; i++) {
            var label = labels[i];
            var labelText = axiomText(label);

            if (labelText.indexOf(textNeedle) === -1) {
                continue;
            }

            var field =
                label.closest('.slicewp-field-wrapper') ||
                label.closest('.slicewp-form-field') ||
                label.closest('.slicewp-field') ||
                label.closest('p') ||
                label.parentElement;

            if (!field) {
                continue;
            }

            var input = field.querySelector('input, textarea, select');

            if (input) {
                return {
                    wrapper: field,
                    label: label,
                    input: input
                };
            }
        }

        return null;
    }

    function axiomFindPaymentPreferenceWrapper(sliceArea) {
        if (!sliceArea) {
            return null;
        }

        var radios = sliceArea.querySelectorAll('input[type="radio"]');

        for (var i = 0; i < radios.length; i++) {
            var radio = radios[i];

            var wrapper =
                radio.closest('.slicewp-field-wrapper') ||
                radio.closest('.slicewp-form-field') ||
                radio.closest('.slicewp-field') ||
                radio.closest('p') ||
                radio.closest('div');

            if (!wrapper) {
                continue;
            }

            var text = axiomText(wrapper);

            if (
                text.indexOf('payment preference') !== -1 ||
                text.indexOf('manual / zelle payout') !== -1 ||
                text.indexOf('store credit') !== -1
            ) {
                return wrapper;
            }
        }

        return null;
    }

    function axiomFindZelleField(sliceArea) {
        if (!sliceArea) {
            return null;
        }

        var zelle =
            axiomFindFieldWrapperByLabelText(sliceArea, 'zelle email or phone') ||
            axiomFindFieldWrapperByLabelText(sliceArea, 'zelle email') ||
            axiomFindFieldWrapperByLabelText(sliceArea, 'zelle phone');

        if (zelle && zelle.input) {
            return zelle;
        }

        return null;
    }

    function axiomGetSelectedPaymentPreference(sliceArea) {
        var wrapper = axiomFindPaymentPreferenceWrapper(sliceArea);

        if (!wrapper) {
            return '';
        }

        var checked = wrapper.querySelector('input[type="radio"]:checked');

        if (!checked) {
            return '';
        }

        var label = axiomGetLabelForInput(checked);

        var selectedText = [
            axiomText(label),
            axiomText(checked.closest('label')),
            axiomValue(checked.value),
            axiomValue(checked.getAttribute('aria-label')),
            axiomValue(checked.getAttribute('data-value')),
            axiomText(checked.parentElement)
        ].join(' ');

        if (selectedText.indexOf('store') !== -1) {
            return 'store_credit';
        }

        if (
            selectedText.indexOf('manual') !== -1 ||
            selectedText.indexOf('zelle') !== -1 ||
            selectedText.indexOf('bank') !== -1
        ) {
            return 'manual';
        }

        return '';
    }

    function axiomSyncAffiliateSettingsFields(sliceArea) {
        if (!axiomIsSettingsTab()) {
            return;
        }

        if (!sliceArea) {
            return;
        }

        var form = axiomFindAffiliateSettingsForm(sliceArea);

        if (!form) {
            return;
        }

        var paymentHidden = axiomEnsureHiddenInput(form, 'axiom_payment_preference');
        var zelleHidden = axiomEnsureHiddenInput(form, 'axiom_zelle_contact');

        var paymentPreference = axiomGetSelectedPaymentPreference(sliceArea);
        var zelleField = axiomFindZelleField(sliceArea);

        if (paymentHidden) {
            paymentHidden.value = paymentPreference;
        }

        if (zelleHidden && zelleField && zelleField.input) {
            zelleHidden.value = zelleField.input.value || '';
        }

        if (zelleField && zelleField.wrapper) {
            zelleField.wrapper.style.display = paymentPreference === 'store_credit' ? 'none' : '';
        }
    }

    function axiomBindAffiliateSettingsForm(sliceArea) {
        if (!axiomIsSettingsTab()) {
            return;
        }

        if (!sliceArea) {
            return;
        }

        var form = axiomFindAffiliateSettingsForm(sliceArea);

        if (!form || form.classList.contains('axiom-payment-sync-bound')) {
            return;
        }

        form.classList.add('axiom-payment-sync-bound');

        form.addEventListener('submit', function () {
            axiomSyncAffiliateSettingsFields(sliceArea);
        });
    }

    function axiomHideDashboardPartnerCodeField(sliceArea) {
        if (!axiomIsSettingsTab()) {
            return;
        }

        if (!sliceArea) {
            return;
        }

        var labels = sliceArea.querySelectorAll('label');

        labels.forEach(function (label) {
            var text = axiomText(label);

            var isPartnerCodeLabel =
                text === 'your partner code *' ||
                text === 'your partner code' ||
                text === 'partner code *' ||
                text === 'partner code' ||
                text === 'affiliate code *' ||
                text === 'affiliate code';

            if (!isPartnerCodeLabel) {
                return;
            }

            var wrapper =
                label.closest('.slicewp-field-wrapper') ||
                label.closest('.slicewp-form-field') ||
                label.closest('.slicewp-field') ||
                label.closest('p') ||
                label.parentElement;

            if (wrapper) {
                wrapper.style.display = 'none';
                wrapper.classList.add('axiom-hidden-partner-code-field');
            }
        });
    }

    function axiomAddCouponCodeRequestBox(sliceArea) {
        if (!sliceArea) {
            return;
        }

        var existingBox = sliceArea.querySelector('.axiom-coupon-code-request-box');

        if (!axiomIsCouponsTab(sliceArea)) {
            if (existingBox) {
                existingBox.remove();
            }
            return;
        }

        if (existingBox) {
            return;
        }

        var couponText = axiomFindCouponIntroText(sliceArea);

        if (!couponText) {
            return;
        }

        var couponTable =
            couponText.parentElement.querySelector('table') ||
            sliceArea.querySelector('.slicewp-table') ||
            sliceArea.querySelector('table');

        var box = document.createElement('div');
        box.className = 'axiom-coupon-code-request-box';
        box.innerHTML =
            '<div class="axiom-coupon-code-request-icon"><i class="fa-brands fa-discord" aria-hidden="true"></i></div>' +
            '<div class="axiom-coupon-code-request-content">' +
                '<strong>Want a different coupon code?</strong>' +
                '<p>Email us to request a custom affiliate code, or join the Axiom affiliate Discord community for faster support.</p>' +
                '<div class="axiom-coupon-code-request-actions">' +
                    '<a href="mailto:realaxiompeptides@gmail.com?subject=Affiliate%20Coupon%20Code%20Request">Request by Email</a>' +
                    '<a class="discord" href="https://discord.gg/53udgxM6A" target="_blank" rel="noopener">Join Discord</a>' +
                '</div>' +
            '</div>';

        if (couponTable && couponTable.parentNode) {
            couponTable.parentNode.insertBefore(box, couponTable.nextSibling);
        } else {
            couponText.parentNode.insertBefore(box, couponText.nextSibling);
        }
    }

    function axiomDashboardCleanup() {
        var dashboard = document.querySelector('.axiom-affiliate-dashboard-modern');

        if (!dashboard) {
            return;
        }

        var sliceArea = dashboard.querySelector('.axiom-affiliate-default-dashboard');

        if (!sliceArea) {
            return;
        }

        dashboard.classList.remove('axiom-affiliate-not-home');
        dashboard.classList.add('axiom-affiliate-home-active');

        axiomUnhideCleanup(sliceArea);
        axiomHideBadNavButtons(sliceArea);
        axiomHandlePayoutScheduleVisibility(dashboard, sliceArea);
        axiomHideDuplicateBottomAfterChart(sliceArea);
        axiomHideLooseDuplicateHeadings(sliceArea);
        axiomHideDashboardPartnerCodeField(sliceArea);
        axiomSyncAffiliateSettingsFields(sliceArea);
        axiomBindAffiliateSettingsForm(sliceArea);
        axiomAddCouponCodeRequestBox(sliceArea);
    }

    function axiomRunSoon() {
        setTimeout(axiomDashboardCleanup, 100);
        setTimeout(axiomDashboardCleanup, 400);
        setTimeout(axiomDashboardCleanup, 900);
    }

    document.addEventListener('DOMContentLoaded', function () {
        axiomDashboardCleanup();
        axiomRunSoon();

        document.addEventListener('click', function () {
            axiomRunSoon();
        });

        document.addEventListener('change', function () {
            axiomRunSoon();
        });

        document.addEventListener('input', function () {
            axiomRunSoon();
        });
    });

    window.addEventListener('load', function () {
        axiomDashboardCleanup();

        setTimeout(axiomDashboardCleanup, 500);
        setTimeout(axiomDashboardCleanup, 1200);
        setTimeout(axiomDashboardCleanup, 2500);
        setTimeout(axiomDashboardCleanup, 4000);
    });
})();
