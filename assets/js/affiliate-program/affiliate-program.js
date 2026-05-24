(function () {
    "use strict";

    function formatMoney(value) {
        return "$" + Math.round(value).toLocaleString();
    }

    function setRangeProgress(input) {
        if (!input) return;

        var min = Number(input.min || 0);
        var max = Number(input.max || 100);
        var value = Number(input.value || 0);
        var percent = ((value - min) / (max - min)) * 100;

        input.style.background =
            "linear-gradient(90deg, #0b63f6 0%, #0b63f6 " +
            percent +
            "%, #dbeafe " +
            percent +
            "%, #dbeafe 100%)";
    }

    function initCalculator() {
        var referrals = document.getElementById("axapReferrals");
        var aov = document.getElementById("axapAov");
        var reorder = document.getElementById("axapReorder");

        if (!referrals || !aov || !reorder) return;

        var referralsValue = document.getElementById("axapReferralsValue");
        var aovValue = document.getElementById("axapAovValue");
        var reorderValue = document.getElementById("axapReorderValue");
        var monthly = document.getElementById("axapMonthly");
        var yearly = document.getElementById("axapYearly");

        function update() {
            var referralCount = Number(referrals.value);
            var avgOrderValue = Number(aov.value);
            var reorderRate = Number(reorder.value) / 100;
            var commissionRate = 0.10;

            var firstOrderCommission = referralCount * avgOrderValue * commissionRate;
            var reorderCommission = referralCount * reorderRate * avgOrderValue * commissionRate;
            var monthlyTotal = firstOrderCommission + reorderCommission;
            var yearlyTotal = monthlyTotal * 12;

            referralsValue.textContent = referralCount.toLocaleString();
            aovValue.textContent = formatMoney(avgOrderValue);
            reorderValue.textContent = Math.round(reorderRate * 100) + "%";

            monthly.textContent = formatMoney(monthlyTotal);
            yearly.textContent = formatMoney(yearlyTotal);

            setRangeProgress(referrals);
            setRangeProgress(aov);
            setRangeProgress(reorder);
        }

        referrals.addEventListener("input", update);
        aov.addEventListener("input", update);
        reorder.addEventListener("input", update);

        update();
    }

    function initRevealAnimations() {
        var items = document.querySelectorAll(".axap-reveal");

        if (!items.length) return;

        if (!("IntersectionObserver" in window)) {
            items.forEach(function (item) {
                item.classList.add("axap-visible");
            });
            return;
        }

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("axap-visible");
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.14,
                rootMargin: "0px 0px -40px 0px"
            }
        );

        items.forEach(function (item, index) {
            item.style.transitionDelay = Math.min(index * 45, 240) + "ms";
            observer.observe(item);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        initCalculator();
        initRevealAnimations();
    });
})();
