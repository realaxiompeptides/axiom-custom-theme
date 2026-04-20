document.addEventListener("DOMContentLoaded", function () {
  const faqItems = document.querySelectorAll(".axiom-kits-faq-item");

  faqItems.forEach((item) => {
    item.addEventListener("toggle", function () {
      if (!item.open) return;

      faqItems.forEach((other) => {
        if (other !== item) {
          other.removeAttribute("open");
        }
      });
    });
  });
});
