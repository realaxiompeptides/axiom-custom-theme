document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("axiomCoaSearch");
  const grid = document.getElementById("axiomCoaGrid");
  const modal = document.getElementById("axiomCoaModal");
  const modalImage = document.getElementById("axiomCoaModalImage");
  const modalTitle = document.getElementById("axiomCoaModalTitle");

  if (searchInput && grid) {
    const cards = Array.from(grid.querySelectorAll(".axiom-coa-card"));

    searchInput.addEventListener("input", function () {
      const value = (searchInput.value || "").trim().toLowerCase();

      cards.forEach((card) => {
        const cardText = (card.getAttribute("data-search") || "").toLowerCase();
        const variationRows = Array.from(card.querySelectorAll(".axiom-coa-variant-row"));

        let cardMatches = !value || cardText.includes(value);
        let variationMatchFound = false;

        variationRows.forEach((row) => {
          const rowText = (row.getAttribute("data-search") || "").toLowerCase();
          const rowMatches = !value || rowText.includes(value);

          row.style.display = rowMatches ? "" : "none";

          if (rowMatches) {
            variationMatchFound = true;
          }
        });

        card.style.display = (cardMatches || variationMatchFound || (!variationRows.length && cardMatches)) ? "" : "none";
      });
    });
  }

  function openModal(title, imageSrc) {
    if (!modal || !modalImage || !modalTitle) return;

    modalTitle.textContent = title || "COA Preview";
    modalImage.src = imageSrc || "";
    modalImage.alt = title || "COA Preview";
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    if (!modal || !modalImage) return;

    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    modalImage.src = "";
    document.body.style.overflow = "";
  }

  document.querySelectorAll(".axiom-coa-open-modal").forEach((button) => {
    button.addEventListener("click", function () {
      const title = button.getAttribute("data-coa-title") || "COA Preview";
      const image = button.getAttribute("data-coa-image") || "";
      openModal(title, image);
    });
  });

  document.querySelectorAll("[data-close-coa-modal]").forEach((el) => {
    el.addEventListener("click", closeModal);
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeModal();
    }
  });
});
