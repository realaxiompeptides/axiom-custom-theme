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
            url.indexOf('tab=settings') !== -1 ||
            url.indexOf('settings') !== -1
        );
    }

    function axiomIsPayoutTab() {
        var url = window.location.href.toLowerCase();

        return (
            url.indexOf('affiliate-account-tab=payouts') !== -1 ||
            url.indexOf('tab=payouts') !== -1 ||
            url.indexOf('payout') !== -1
        );
    }

    /**
     * Payout Schedule:
     * - Hide on dashboard home.
     * - Show on settings/payouts.
     * - Move below SliceWP buttons and above the settings form/content.
     */
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

        if (nav && nav.parentNode) {
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
        sliceArea.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, strong, div').forEach(function (el) {
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
     * Find a field by visible label text.
     */
    function axiomFindFieldByLabelText(textNeedle) {
        textNeedle = String(textNeedle || '').toLowerCase();

        var fields = document.querySelectorAll(
            '.slicewp-field-wrapper, .slicewp-form-field, .slicewp-field, p, div'
        );

        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            var text = axiomText(field);

            if (text.indexOf(textNeedle) !== -1) {
                var input = field.querySelector('input, textarea, select');

                if (input) {
                    return {
                        field: field,
                        input: input
                    };
                }
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
     * IMPORTANT FIX:
     * SliceWP custom fields use random/internal names.
     * This injects normal field names PHP can actually read:
     * - axiom_partner_code
     * - axiom_payment_preference
     * - axiom_zelle_contact
     */
    function axiomForceSettingsHiddenFields() {
        if (!axiomIsSettingsTab()) {
            return;
        }

        var forms = document.querySelectorAll('.axiom-affiliate-default-dashboard form, form');

        forms.forEach(function (form) {
            if (form.classList.contains('axiom-hidden-sync-ready')) {
                return;
            }

            form.classList.add('axiom-hidden-sync-ready');

            form.addEventListener('submit', function () {
                var partner = axiomFindFieldByLabelText('partner code');
                var zelle = axiomFindFieldByLabelText('zelle email');
                var payment = axiomFindFieldByLabelText('payment preference');

                if (partner && partner.input) {
                    partner.input.value = axiomCleanPartnerCode(partner.input.value);
                    axiomSetHiddenInput(form, 'axiom_partner_code', partner.input.value);
                }

                if (zelle && zelle.input) {
                    axiomSetHiddenInput(form, 'axiom_zelle_contact', zelle.input.value || '');
                }

                if (payment && payment.field) {
                    var checked = payment.field.querySelector('input[type="radio"]:checked');
                    var selectedText = checked ? axiomText(checked.closest('label')) + ' ' + checked.value : '';

                    if (selectedText.indexOf('store') !== -1) {
                        axiomSetHiddenInput(form, 'axiom_payment_preference', 'store_credit');
                    } else {
                        axiomSetHiddenInput(form, 'axiom_payment_preference', 'manual');
                    }
                }
            });
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
