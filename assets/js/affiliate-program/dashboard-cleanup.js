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
     *
     * KEEP:
     * - Dashboard
     * - Affiliate Links
     * - Commissions
     * - Visits
     * - Coupons
     * - Payouts
     * - Settings
     * - Logout
     */
    function axiomShouldHideNavButton(link) {
        var text = axiomText(link);
        var href = axiomHref(link);

        if (text.indexOf('creative') !== -1 || href.indexOf('creative') !== -1) {
            return true;
        }

        if (text.indexOf('creatives') !== -1 || href.indexOf('creatives') !== -1) {
            return true;
        }

        return false;
    }

    function axiomHideBadNavButtons(sliceArea) {
        var navLinks = sliceArea.querySelectorAll(
            '.slicewp-tabs-nav a, .slicewp-user-dashboard-nav a, .slicewp-nav-tab-wrapper a'
        );

        navLinks.forEach(function (link) {
            var shouldHide = axiomShouldHideNavButton(link);
            var wrapper = link.closest('li') || link;

            if (shouldHide) {
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
     * - Hide it on the main dashboard.
     * - Show it on Settings/Payouts.
     * - Move it below the button navigation, above the settings form/content.
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
            var navParent = nav.parentNode;

            if (payoutSchedule.parentNode !== navParent || payoutSchedule.previousElementSibling !== nav) {
                navParent.insertBefore(payoutSchedule, nav.nextSibling);
            }
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
    }

    document.addEventListener('DOMContentLoaded', function () {
        axiomDashboardCleanup();

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
