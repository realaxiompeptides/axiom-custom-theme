(function () {
    'use strict';

    function axiomText(el) {
        return (el && el.textContent ? el.textContent : '')
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
                '.axiom-affiliate-dashboard-header, .axiom-affiliate-stats-grid, .axiom-affiliate-program-details'
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
         * Hide everything AFTER the chart block inside the SliceWP dashboard.
         * This is the cleanest way to remove the duplicated All Time / Program Details section.
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

            /**
             * Only hide duplicate dashboard pieces after the chart.
             * This avoids breaking other SliceWP tabs.
             */
            var isDuplicateAfterChart =
                text === 'all time' ||
                text === 'program details' ||
                text.indexOf('visits') !== -1 ||
                text.indexOf('commissions') !== -1 ||
                text.indexOf('paid earnings') !== -1 ||
                text.indexOf('unpaid earnings') !== -1 ||
                text.indexOf('commission rate') !== -1 ||
                text.indexOf('sale rate') !== -1 ||
                text.indexOf('cookie duration') !== -1 ||
                text.indexOf('30 days') !== -1;

            if (isDuplicateAfterChart) {
                axiomHide(el);
            }
        });
    }

    function axiomHideLooseDuplicateHeadings(sliceArea) {
        /**
         * Extra cleanup for the words themselves if they are loose text/headings.
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

        axiomUnhideCleanup(sliceArea);
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
