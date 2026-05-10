(function () {
    function axiomAffiliateDashboardCleanup() {
        var dashboard = document.querySelector('.axiom-affiliate-dashboard-modern');

        if (!dashboard) {
            return;
        }

        var sliceArea = dashboard.querySelector('.axiom-affiliate-default-dashboard');

        if (!sliceArea) {
            return;
        }

        var navLinks = sliceArea.querySelectorAll(
            '.slicewp-tabs-nav a, .slicewp-user-dashboard-nav a, .slicewp-nav-tab-wrapper a, .slicewp-nav-tab'
        );

        var activeLink = sliceArea.querySelector(
            '.slicewp-nav-tab-active, .slicewp-tabs-nav .active a, .slicewp-tabs-nav li.active a, .slicewp-user-dashboard-nav .active a, .slicewp-active a'
        );

        var isHomeTab = true;

        if (activeLink && navLinks.length) {
            isHomeTab = activeLink === navLinks[0];
        }

        dashboard.classList.toggle('axiom-affiliate-home-active', isHomeTab);
        dashboard.classList.toggle('axiom-affiliate-not-home', !isHomeTab);

        /**
         * Remove duplicate SliceWP home dashboard blocks.
         * Only runs on the Home tab so other tabs like links/commissions/settings still work.
         */
        if (isHomeTab) {
            var possibleBlocks = sliceArea.querySelectorAll(
                '.slicewp-card, .slicewp-box, .slicewp-panel, .slicewp-chart, section, .slicewp-section, .slicewp-grid, .slicewp-row'
            );

            possibleBlocks.forEach(function (block) {
                var text = (block.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();

                if (!text) {
                    return;
                }

                var isDuplicateMetric =
                    (
                        text.includes('visits') &&
                        text.includes('commissions') &&
                        text.includes('earnings')
                    ) ||
                    text.includes('view all visits') ||
                    text.includes('view all commissions') ||
                    text.includes('all time') ||
                    text.includes('program details') ||
                    text.includes('cookie duration') ||
                    text.includes('sale rate');

                if (isDuplicateMetric) {
                    block.classList.add('axiom-slicewp-duplicate-home-block');
                }
            });

            /**
             * Remove loose leftover headings like:
             * All time
             * Program details
             */
            var headings = sliceArea.querySelectorAll('h1, h2, h3, h4, h5, h6');

            headings.forEach(function (heading) {
                var text = (heading.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();

                if (text === 'all time' || text === 'program details') {
                    heading.classList.add('axiom-slicewp-duplicate-home-block');
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        axiomAffiliateDashboardCleanup();

        document.addEventListener('click', function () {
            setTimeout(axiomAffiliateDashboardCleanup, 250);
            setTimeout(axiomAffiliateDashboardCleanup, 800);
        });
    });

    window.addEventListener('load', axiomAffiliateDashboardCleanup);
})();
