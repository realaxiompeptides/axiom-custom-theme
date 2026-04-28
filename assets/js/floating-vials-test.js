document.addEventListener('DOMContentLoaded', function () {
    const vials = document.querySelectorAll('.axiom-floating-vial');

    vials.forEach(function (vial) {
        const randomDuration = (3 + Math.random() * 1.2).toFixed(2);
        const randomDelay = (Math.random() * -2).toFixed(2);

        vial.style.animationDuration = randomDuration + 's';
        vial.style.animationDelay = randomDelay + 's';
    });
});
