document.addEventListener("DOMContentLoaded", function () {
  const orderId = document.getElementById("axiomTrackOrderId");
  const orderEmail = document.getElementById("axiomTrackOrderEmail");
  const form = document.querySelector(".axiom-track-form");

  if (!form) return;

  form.addEventListener("submit", function (e) {
    const idValue = orderId ? orderId.value.trim() : "";
    const emailValue = orderEmail ? orderEmail.value.trim() : "";

    if (!idValue || !emailValue) {
      e.preventDefault();
      alert("Please enter your order number and billing email.");
    }
  });
});
