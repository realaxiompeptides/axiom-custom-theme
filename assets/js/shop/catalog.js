document.addEventListener("DOMContentLoaded", function () {
  const grid = document.getElementById("axiomCatalogGrid");
  const searchInput = document.getElementById("axiomCatalogSearch");
  const sortSelect = document.getElementById("axiomCatalogSort");
  const filterWrap = document.getElementById("axiomCatalogFilters");
  const countEl = document.getElementById("axiomCatalogCount");

  if (!grid) return;

  const cards = Array.from(grid.querySelectorAll(".axiom-product-card"));
  let activeFilter = "all";

  function getCardName(card) {
    return (card.getAttribute("data-name") || "").toLowerCase().trim();
  }

  function getCardCategories(card) {
    return (card.getAttribute("data-categories") || "")
      .toLowerCase()
      .trim()
      .split(/\s+/)
      .filter(Boolean);
  }

  function getCardPrice(card) {
    const raw = parseFloat(card.getAttribute("data-price") || "0");
    return Number.isFinite(raw) ? raw : 0;
  }

  function getCardDate(card) {
    const raw = parseInt(card.getAttribute("data-date") || "0", 10);
    return Number.isFinite(raw) ? raw : 0;
  }

  function updateCount() {
    const visibleCount = cards.filter((card) => !card.classList.contains("is-hidden")).length;
    if (countEl) {
      countEl.textContent = `${visibleCount} results`;
    }
  }

  function sortCards() {
    if (!sortSelect) return;

    const value = sortSelect.value;
    const sorted = [...cards].sort((a, b) => {
      const nameA = getCardName(a);
      const nameB = getCardName(b);
      const priceA = getCardPrice(a);
      const priceB = getCardPrice(b);
      const dateA = getCardDate(a);
      const dateB = getCardDate(b);

      switch (value) {
        case "name-asc":
          return nameA.localeCompare(nameB);
        case "name-desc":
          return nameB.localeCompare(nameA);
        case "price-asc":
          return priceA - priceB;
        case "price-desc":
          return priceB - priceA;
        case "newest":
          return dateB - dateA;
        default:
          return 0;
      }
    });

    sorted.forEach((card) => grid.appendChild(card));
  }

  function applyCatalogState() {
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : "";

    cards.forEach((card) => {
      const name = getCardName(card);
      const categories = getCardCategories(card);

      const matchesSearch =
        !searchTerm ||
        name.includes(searchTerm);

      const matchesFilter =
        activeFilter === "all" ||
        categories.includes(activeFilter);

      if (matchesSearch && matchesFilter) {
        card.classList.remove("is-hidden");
      } else {
        card.classList.add("is-hidden");
      }
    });

    sortCards();
    updateCount();
  }

  if (searchInput) {
    searchInput.addEventListener("input", applyCatalogState);

    searchInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        applyCatalogState();
      }
    });
  }

  if (sortSelect) {
    sortSelect.addEventListener("change", applyCatalogState);
  }

  if (filterWrap) {
    filterWrap.addEventListener("click", function (e) {
      const pill = e.target.closest(".axiom-filter-pill");
      if (!pill) return;

      activeFilter = (pill.getAttribute("data-filter") || "all").toLowerCase();

      filterWrap.querySelectorAll(".axiom-filter-pill").forEach((btn) => {
        btn.classList.remove("is-active");
      });

      pill.classList.add("is-active");
      applyCatalogState();
    });
  }

  applyCatalogState();
});
