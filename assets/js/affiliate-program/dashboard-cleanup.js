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

    function axiomHideElement(el) {
        if (el && !axiomIsInsideNav(el)) {
            el.classList.add('axiom-slicewp-duplicate-home-block');
        }
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
         * Hide duplicate SliceWP metric/dashboard cards.
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
                axiomHideElement(block);
            }
        });

        /**
         * Hide leftover duplicate headings:
         * All time
         * Program details
         */
        var headings = sliceArea.querySelectorAll('h1, h2, h3, h4, h5, h6');

        headings.forEach(function (heading) {
            if (axiomIsInsideNav(heading)) {
                return;
            }

            var text = axiomText(heading);

            if (text === 'all time' || text === 'program details') {
                axiomHideElement(heading);

                /**
                 * Also hide the next few sibling blocks because SliceWP puts
                 * the All Time / Program Details cards right after these headings.
                 */
                var sibling = heading.nextElementSibling;
                var count = 0;

                while (sibling && count < 8) {
                    if (axiomIsInsideNav(sibling)) {
                        sibling = sibling.nextElementSibling;
                        count++;
                        continue;
                    }

                    var siblingText = axiomText(sibling);

                    if (
                        siblingText.indexOf('visits') !== -1 ||
                        siblingText.indexOf('commissions') !== -1 ||
                        siblingText.indexOf('paid earnings') !== -1 ||
                        siblingText.indexOf('unpaid earnings') !== -1 ||
                        siblingText.indexOf('commission rate') !== -1 ||
                        siblingText.indexOf('sale rate') !== -1 ||
                        siblingText.indexOf('cookie duration') !== -1 ||
                        siblingText.indexOf('30 days') !== -1
                    ) {
                        axiomHideElement(sibling);
                    }

                    sibling = sibling.nextElementSibling;
                    count++;
                }
            }
        });

        /**
         * Extra cleanup for loose divs that only contain duplicate text.
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
                text === 'cookie duration 30 days' ||
                text === 'visits 0' ||
                text === 'commissions 0' ||
                text === 'paid earnings $0.00' ||
                text === 'unpaid earnings $0.00';

            if (isLooseDuplicate) {
                axiomHideElement(div);
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
         * Keep custom Axiom cards visible.
         */
        dashboard.classList.remove('axiom-affiliate-not-home');
        dashboard.classList.add('axiom-affiliate-home-active');

        axiomHideDuplicateHomeBlocks(sliceArea);
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
        setTimeout(axiomDashboardCleanup, 2000);
    });
})();
