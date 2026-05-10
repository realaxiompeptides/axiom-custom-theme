(function () {
    'use strict';

    function axiomText(el) {
        return (el && el.textContent ? el.textContent : '')
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
        sliceArea.querySelectorAll('.axiom-slicewp-duplicate-home-block').forEach(function (el) {
            el.classList.remove('axiom-slicewp-duplicate-home-block');
        });
    }

    /**
     * Hide ONLY the Creatives tab.
     */
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

    function axiomHandlePayoutScheduleVisibility(dashboard, sliceArea) {
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

    /**
     * Find a field wrapper by visible label text.
     * IMPORTANT: this only checks label text, not full parent text.
     */
    function axiomFindFieldByLabelText(textNeedle) {
        textNeedle = String(textNeedle || '').toLowerCase();

        var labels = document.querySelectorAll('.axiom-affiliate-default-dashboard label');

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
                    field: field,
                    input: input
                };
            }
        }

        return null;
    }

    function axiomCleanPartnerCode(value) {
        return String(value || '')
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '')
            .slice(0, 18);
    }

    function axiomSetHiddenInput(form, name, value) {
        if (!form || !name) {
            return;
        }

        var input = form.querySelector('input[name="' + name + '"]');

        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            form.appendChild(input);
        }

        input.value = value || '';
    }

    /**
     * Settings hidden sync:
     * Only payment + Zelle.
     * Partner code is intentionally NOT synced anymore because affiliates should not edit it from dashboard.
     */
    function axiomForceSettingsHiddenFields() {
        if (!axiomIsSettingsTab()) {
            return;
        }

        var forms = document.querySelectorAll('.axiom-affiliate-default-dashboard form');

        forms.forEach(function (form) {
            if (form.classList.contains('axiom-hidden-sync-ready')) {
                return;
            }

            form.classList.add('axiom-hidden-sync-ready');

            form.addEventListener('submit', function () {
                var zelle = axiomFindFieldByLabelText('zelle email');
                var payment = axiomFindFieldByLabelText('payment preference');

                if (zelle && zelle.input) {
                    axiomSetHiddenInput(form, 'axiom_zelle_contact', zelle.input.value || '');
                }

                if (payment && payment.field) {
                    var checked = payment.field.querySelector('input[type="radio"]:checked');
                    var selectedText = '';

                    if (checked) {
                        selectedText =
                            axiomText(checked.closest('label')) +
                            ' ' +
                            String(checked.value || '').toLowerCase();
                    }

                    if (selectedText.indexOf('store') !== -1) {
                        axiomSetHiddenInput(form, 'axiom_payment_preference', 'store_credit');
                    } else if (
                        selectedText.indexOf('manual') !== -1 ||
                        selectedText.indexOf('zelle') !== -1 ||
                        selectedText.indexOf('bank') !== -1
                    ) {
                        axiomSetHiddenInput(form, 'axiom_payment_preference', 'manual');
                    }
                }
            });
        });
    }

    /**
     * Hide editable Partner Code field in dashboard settings only.
     * This does NOT touch registration page.
     */
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
        axiomForceSettingsHiddenFields();
        axiomHideDashboardPartnerCodeField(sliceArea);
    }

    document.addEventListener('DOMContentLoaded', function () {
        axiomDashboardCleanup();

        document.addEventListener('input', function () {
            axiomForceSettingsHiddenFields();
        });

        document.addEventListener('change', function () {
            axiomForceSettingsHiddenFields();
        });

        document.addEventListener('click', function () {
            setTimeout(axiomDashboardCleanup, 150);
            setTimeout(axiomDashboardCleanup, 500);
            setTimeout(axiomDashboardCleanup, 1000);
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
