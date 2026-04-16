document.addEventListener("DOMContentLoaded", function () {
  const cartForm = document.querySelector(".axiom-cart-layout");

  if (!cartForm) return;

  const qtyInputs = cartForm.querySelectorAll(".qty");

  qtyInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const value = parseInt(input.value, 10);
      if (!Number.isFinite(value) || value < 0) {
        input.value = 0;
      }
    });
  });
});
