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

    function axiomHideElement(el) {
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

    function axiomHideBottomDuplicateSections(sliceArea) {
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
         * IMPORTANT:
         * Do NOT hide:
         * - Visits cards
         * - Commissions cards
         * - Earnings cards
         * - Chart
         * - Date range
         *
         * Only hide the bottom duplicate:
         * - All time
         * - Program details
         * - Their cards below those headings
         */

        var headings = Array.prototype.slice.call(
            sliceArea.querySelectorAll('h1, h2, h3, h4, h5, h6')
        );

        headings.forEach(function (heading) {
            if (axiomIsInsideNav(heading) || axiomIsProtectedAxiomSection(heading)) {
                return;
            }

            var text = axiomText(heading);

            if (text !== 'all time' && text !== 'program details') {
                return;
            }

            axiomHideElement(heading);

            /**
             * Hide only the section after "All time" / "Program details".
             * Stop once we reach nav, chart, or unrelated content.
             */
            var sibling = heading.nextElementSibling;
            var count = 0;

            while (sibling && count < 10) {
                if (axiomIsInsideNav(sibling) || axiomIsProtectedAxiomSection(sibling)) {
                    break;
                }

                var siblingText = axiomText(sibling);

                var shouldHide =
                    siblingText.indexOf('visits') !== -1 ||
                    siblingText.indexOf('commissions') !== -1 ||
                    siblingText.indexOf('paid earnings') !== -1 ||
                    siblingText.indexOf('unpaid earnings') !== -1 ||
                    siblingText.indexOf('commission rate') !== -1 ||
                    siblingText.indexOf('sale rate') !== -1 ||
                    siblingText.indexOf('cookie duration') !== -1 ||
                    siblingText.indexOf('30 days') !== -1;

                if (shouldHide) {
                    axiomHideElement(sibling);
                }

                sibling = sibling.nextElementSibling;
                count++;
            }
        });

        /**
         * Fallback: hide loose text nodes that only say All time / Program details.
         * This does NOT touch the metric cards/chart.
         */
        var walker = document.createTreeWalker(
            sliceArea,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        var textNodes = [];

        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }

        textNodes.forEach(function (node) {
            var text = (node.nodeValue || '')
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase();

            if (text !== 'all time' && text !== 'program details') {
                return;
            }

            var parent = node.parentElement;

            if (!parent) {
                node.nodeValue = '';
                return;
            }

            if (axiomIsInsideNav(parent) || axiomIsProtectedAxiomSection(parent)) {
                return;
            }

            var parentText = axiomText(parent);

            if (parentText === text) {
                axiomHideElement(parent);
            } else {
                node.nodeValue = '';
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
         * Keep custom Axiom dashboard visible.
         */
        dashboard.classList.remove('axiom-affiliate-not-home');
        dashboard.classList.add('axiom-affiliate-home-active');

        axiomHideBottomDuplicateSections(sliceArea);
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
    });
})();
