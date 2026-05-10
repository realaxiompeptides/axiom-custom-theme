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
     * Hide bad/unused SliceWP tabs.
     *
     * KEEP:
     * - Dashboard
     * - Affiliate Links
     * - Visits
     * - Commissions
     * - Settings
     * - Logout
     *
     * HIDE:
     * - Coupons
     * - Creatives
     * - Payouts
     */
    function axiomShouldHideNavButton(link) {
        var text = axiomText(link);
        var href = axiomHref(link);

        var badTabs = [
            'coupon',
            'coupons',
            'creative',
            'creatives',
            'payout',
            'payouts'
        ];

        for (var i = 0; i < badTabs.length; i++) {
            if (text.indexOf(badTabs[i]) !== -1 || href.indexOf(badTabs[i]) !== -1) {
                return true;
            }
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
            } else {
                if (wrapper.getAttribute('data-axiom-hidden-tab') !== 'true') {
                    wrapper.style.display = '';
                }
            }
        });
    }

    /**
     * Hide the custom payout schedule from the main dashboard home.
     * It should not show under the normal home dashboard.
     */
    function axiomHandlePayoutScheduleVisibility(dashboard) {
        var payoutSchedule = dashboard.querySelector('.axiom-affiliate-payout-schedule');

        if (!payoutSchedule) {
            return;
        }

        var url = window.location.href.toLowerCase();

        var isPayoutOrSettingsArea =
            url.indexOf('payout') !== -1 ||
            url.indexOf('settings') !== -1 ||
            url.indexOf('affiliate-account-tab=settings') !== -1 ||
            url.indexOf('affiliate-account-tab=payouts') !== -1;

        if (isPayoutOrSettingsArea) {
            payoutSchedule.style.display = '';
        } else {
            payoutSchedule.style.display = 'none';
        }
    }

    function axiomFindChartBlock(sliceArea) {
        var canvas = sliceArea.querySelector('canvas');

        if (!canvas) {
            return null;
        }

        /**
         * Try to grab the full chart card/wrapper instead of just the canvas.
         */
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

        /**
         * Hide duplicated bottom blocks AFTER the chart only.
         */
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
        /**
         * Extra cleanup for loose duplicate headings.
         */
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

        /**
         * Keep custom Axiom top dashboard visible.
         */
        dashboard.classList.remove('axiom-affiliate-not-home');
        dashboard.classList.add('axiom-affiliate-home-active');

        axiomHandlePayoutScheduleVisibility(dashboard);

        axiomUnhideCleanup(sliceArea);

        /**
         * Hide bad SliceWP nav buttons only.
         */
        axiomHideBadNavButtons(sliceArea);

        /**
         * Keep analytics/chart, but hide duplicate bottom SliceWP sections.
         */
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
