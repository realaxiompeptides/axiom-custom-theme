document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("axiomCoaSearch");
  const grid = document.getElementById("axiomCoaGrid");

  if (!searchInput || !grid) return;

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

      card.style.display = (cardMatches || variationMatchFound || !variationRows.length && cardMatches) ? "" : "none";
    });
  });
});
