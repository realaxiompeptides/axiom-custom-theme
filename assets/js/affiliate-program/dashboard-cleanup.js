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

    function axiomHideDuplicateHomeBlocks(sliceArea) {
        if (!sliceArea) {
            return;
        }

        /**
         * Reset previous cleanup.
         */
        sliceArea.querySelectorAll('.axiom-slicewp-duplicate-home-block').forEach(function (el) {
            el.classList.remove('axiom-slicewp-duplicate-home-block');
        });

        /**
         * Hide duplicate headings left behind by SliceWP.
         */
        sliceArea.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(function (heading) {
            if (axiomIsInsideNav(heading)) {
                return;
            }

            var text = axiomText(heading);

            if (
                text === 'all time' ||
                text === 'program details' ||
                text === 'dashboard'
            ) {
                heading.classList.add('axiom-slicewp-duplicate-home-block');
            }
        });

        /**
         * Hide duplicate SliceWP dashboard metric blocks.
         * This targets only the default home dashboard content, not the nav buttons.
         */
        var blocks = sliceArea.querySelectorAll(
            '.slicewp-card, .slicewp-box, .slicewp-panel, .slicewp-chart, .slicewp-section, .slicewp-grid, .slicewp-row, section'
        );

        blocks.forEach(function (block) {
            if (axiomIsInsideNav(block)) {
                return;
            }

            var text = axiomText(block);

            if (!text) {
                return;
            }

            var isDefaultMetricBlock =
                text.indexOf('view all visits') !== -1 ||
                text.indexOf('view all commissions') !== -1 ||
                text.indexOf('visits commissions earnings') !== -1 ||
                text.indexOf('paid earnings unpaid earnings') !== -1 ||
                text.indexOf('commission rate sale rate') !== -1 ||
                text.indexOf('cookie duration') !== -1 ||
                text.indexOf('sale rate:') !== -1;

            if (isDefaultMetricBlock) {
                block.classList.add('axiom-slicewp-duplicate-home-block');
            }
        });

        /**
         * Extra cleanup:
         * Sometimes SliceWP wraps all-time/program details in plain divs.
         * This hides parent blocks only when the text clearly matches duplicate home content.
         */
        sliceArea.querySelectorAll('div').forEach(function (div) {
            if (axiomIsInsideNav(div)) {
                return;
            }

            if (div.classList.contains('axiom-affiliate-default-dashboard')) {
                return;
            }

            var text = axiomText(div);

            if (!text) {
                return;
            }

            var isLooseDuplicate =
                text === 'all time' ||
                text === 'program details' ||
                text === 'commission rate sale rate: 10%' ||
                text === 'cookie duration 30 days';

            if (isLooseDuplicate) {
                div.classList.add('axiom-slicewp-duplicate-home-block');
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
         * IMPORTANT:
         * Do NOT add axiom-affiliate-not-home anymore.
         * That class was hiding your custom Axiom cards.
         *
         * We always keep the Axiom cards and Program Details visible.
         * We only remove duplicate SliceWP home/dashboard blocks.
         */
        dashboard.classList.remove('axiom-affiliate-not-home');
        dashboard.classList.add('axiom-affiliate-home-active');

        axiomHideDuplicateHomeBlocks(sliceArea);
    }

    document.addEventListener('DOMContentLoaded', function () {
        axiomDashboardCleanup();

        /**
         * SliceWP changes tab content after clicks, so cleanup runs again.
         */
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
    });
})();
