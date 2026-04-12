document.addEventListener("DOMContentLoaded", function () {
  const qtyInput = document.getElementById("productQty");
  const qtyMinus = document.getElementById("qtyMinus");
  const qtyPlus = document.getElementById("qtyPlus");

  const variationSelect = document.getElementById("productVariantSelect");
  const variationIdInput = document.getElementById("variation_id");
  const productPrice = document.getElementById("productPrice");
  const productStock = document.getElementById("productStock");
  const productMainImage = document.getElementById("productMainImage");
  const addToCartBtn = document.getElementById("productAddToCart");

  const variationData = window.AXIOM_PRODUCT_PAGE || { isVariable: false, variations: [] };

  if (qtyMinus && qtyInput) {
    qtyMinus.addEventListener("click", function () {
      const current = parseInt(qtyInput.value || "1", 10);
      qtyInput.value = Math.max(1, current - 1);
    });
  }

  if (qtyPlus && qtyInput) {
    qtyPlus.addEventListener("click", function () {
      const current = parseInt(qtyInput.value || "1", 10);
      qtyInput.value = current + 1;
    });
  }

  if (qtyInput) {
    qtyInput.addEventListener("input", function () {
      const current = parseInt(qtyInput.value || "1", 10);
      qtyInput.value = Math.max(1, current || 1);
    });
  }

  if (variationSelect && variationData.isVariable) {
    variationSelect.addEventListener("change", function () {
      const selected = variationSelect.options[variationSelect.selectedIndex];
      const variationId = selected.value || "";
      const priceHtml = selected.getAttribute("data-price-html") || "";
      const image = selected.getAttribute("data-image") || "";
      const stockText = selected.getAttribute("data-stock-text") || "";
      const stockClass = selected.getAttribute("data-stock-class") || "";
      const purchasable = selected.getAttribute("data-purchasable") === "1";
      const attributesJson = selected.getAttribute("data-attributes") || "{}";

      if (variationIdInput) {
        variationIdInput.value = variationId;
      }

      if (productPrice) {
        productPrice.innerHTML = priceHtml;
      }

      if (productMainImage && image) {
        productMainImage.src = image;
      }

      if (productStock) {
        productStock.textContent = stockText;
        productStock.className = "product-stock-text " + stockClass;
      }

      if (addToCartBtn) {
        addToCartBtn.disabled = !variationId || !purchasable;
        addToCartBtn.textContent = variationId ? (purchasable ? "Add To Cart" : "Unavailable") : "Select Variant";
      }

      try {
        const attributes = JSON.parse(attributesJson);
        Object.keys(attributes).forEach(function (key) {
          const field = document.getElementById(key);
          if (field) {
            field.value = attributes[key];
          }
        });
      } catch (error) {
        console.error("Failed to parse variation attributes", error);
      }
    });
  }
});
