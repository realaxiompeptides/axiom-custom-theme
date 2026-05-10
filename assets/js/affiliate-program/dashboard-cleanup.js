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

    function axiomRemoveLooseDuplicateText(scope) {
        if (!scope) {
            return;
        }

        var walker = document.createTreeWalker(
            scope,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        var nodesToProcess = [];

        while (walker.nextNode()) {
            nodesToProcess.push(walker.currentNode);
        }

        nodesToProcess.forEach(function (node) {
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

            /**
             * If the parent only contains this text, hide the parent.
             * If not, remove just the text node.
             */
            var parentText = axiomText(parent);

            if (parentText === text) {
                axiomHideElement(parent);
            } else {
                node.nodeValue = '';
            }
        });
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
         * Hide duplicate metric/dashboard cards.
         */
        var blocks = sliceArea.querySelectorAll(
            '.slicewp-card, .slicewp-box, .slicewp-panel, .slicewp-chart, .slicewp-section, .slicewp-grid, .slicewp-row, section, div'
        );

        blocks.forEach(function (block) {
            if (axiomIsInsideNav(block) || axiomIsProtectedAxiomSection(block)) {
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
                text.indexOf('sale rate:') !== -1 ||
                text === 'all time' ||
                text === 'program details';

            if (isDefaultMetricBlock) {
                axiomHideElement(block);
            }
        });

        /**
         * Hide headings that contain duplicate text.
         */
        var headings = sliceArea.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, strong');

        headings.forEach(function (heading) {
            if (axiomIsInsideNav(heading) || axiomIsProtectedAxiomSection(heading)) {
                return;
            }

            var text = axiomText(heading);

            if (text === 'all time' || text === 'program details') {
                axiomHideElement(heading);
            }
        });

        /**
         * Final fallback: remove loose text nodes.
         */
        axiomRemoveLooseDuplicateText(sliceArea);
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
        setTimeout(axiomDashboardCleanup, 3500);
    });
})();
