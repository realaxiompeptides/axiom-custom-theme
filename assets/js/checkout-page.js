document.addEventListener("DOMContentLoaded", function () {
  const mobileWidth = window.innerWidth <= 991;
  if (!mobileWidth) return;

  const sidebarReview = document.getElementById("axiomCheckoutSidebarReview");
  const mainReview = document.querySelector("#order_review");

  if (!sidebarReview || !mainReview) return;

  sidebarReview.style.display = "none";
});
