(function () {
    function axiomAffiliateGetActiveTabIndex(sliceArea) {
        var navLinks = Array.prototype.slice.call(sliceArea.querySelectorAll(
            '.slicewp-tabs-nav a, .slicewp-user-dashboard-nav a, .slicewp-nav-tab-wrapper a, .slicewp-nav-tab'
        ));

        if (!navLinks.length) {
            return 0;
        }

        var activeLink = sliceArea.querySelector(
            '.slicewp-nav-tab-active, .slicewp-tabs-nav .active a, .slicewp-tabs-nav li.active a, .slicewp-user-dashboard-nav .active a, .slicewp-active a'
        );

        if (!activeLink) {
            return 0;
        }

        var activeIndex = navLinks.indexOf(activeLink);

        if (activeIndex < 0 && activeLink.tagName !== 'A') {
            var activeAnchor = activeLink.querySelector('a');

            if (activeAnchor) {
                activeIndex = navLinks.indexOf(activeAnchor);
            }
        }

        return activeIndex >= 0 ? activeIndex : 0;
    }

    function axiomAffiliateCleanupHomeDuplicates(sliceArea) {
        var blocks = Array.prototype.slice.call(sliceArea.querySelectorAll(
            '.slicewp-card, .slicewp-box, .slicewp-panel, .slicewp-chart, .slicewp-section, .slicewp-grid, .slicewp-row, section'
        ));

        blocks.forEach(function (block) {
            var text = (block.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();

            if (!text) {
                return;
            }

            var isDuplicate =
                text.indexOf('view all visits') !== -1 ||
                text.indexOf('view all commissions') !== -1 ||
                text.indexOf('visits commissions earnings') !== -1 ||
                text.indexOf('all time') !== -1 ||
                text.indexOf('cookie duration') !== -1 ||
                text.indexOf('sale rate') !== -1 ||
                text.indexOf('commission rate') !== -1;

            if (isDuplicate) {
                block.classList.add('axiom-slicewp-duplicate-home-block');
            }
        });

        var headings = Array.prototype.slice.call(sliceArea.querySelectorAll('h1, h2, h3, h4, h5, h6'));

        headings.forEach(function (heading) {
            var text = (heading.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();

            if (text === 'all time' || text === 'program details') {
                heading.classList.add('axiom-slicewp-duplicate-home-block');
            }
        });
    }

    function axiomAffiliateDashboardCleanup() {
        var dashboard = document.querySelector('.axiom-affiliate-dashboard-modern');

        if (!dashboard) {
            return;
        }

        var sliceArea = dashboard.querySelector('.axiom-affiliate-default-dashboard');

        if (!sliceArea) {
            return;
        }

        var activeTabIndex = axiomAffiliateGetActiveTabIndex(sliceArea);

        /**
         * IMPORTANT:
         * Home tab is tab index 0.
         * If SliceWP fails to report active tab, we assume Home.
         */
        var isHomeTab = activeTabIndex === 0;

        dashboard.classList.toggle('axiom-affiliate-home-active', isHomeTab);
        dashboard.classList.toggle('axiom-affiliate-not-home', !isHomeTab);

        /**
         * Reset old cleanup classes first.
         */
        var previouslyHidden = sliceArea.querySelectorAll('.axiom-slicewp-duplicate-home-block');

        previouslyHidden.forEach(function (el) {
            el.classList.remove('axiom-slicewp-duplicate-home-block');
        });

        /**
         * Only hide duplicate SliceWP dashboard blocks on Home tab.
         */
        if (isHomeTab) {
            axiomAffiliateCleanupHomeDuplicates(sliceArea);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        axiomAffiliateDashboardCleanup();

        document.addEventListener('click', function () {
            setTimeout(axiomAffiliateDashboardCleanup, 150);
            setTimeout(axiomAffiliateDashboardCleanup, 500);
            setTimeout(axiomAffiliateDashboardCleanup, 1000);
        });
    });

    window.addEventListener('load', axiomAffiliateDashboardCleanup);
})();
