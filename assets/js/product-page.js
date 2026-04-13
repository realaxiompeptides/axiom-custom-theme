document.addEventListener("DOMContentLoaded", function () {
  const qtyInput = document.getElementById("productQty");
  const qtyMinus = document.getElementById("qtyMinus");
  const qtyPlus = document.getElementById("qtyPlus");

  const variationSelect = document.getElementById("productVariantSelect");
  const variationIdInput = document.getElementById("variation_id");
  const productPrice = document.getElementById("productPrice");
  const stickyProductPrice = document.getElementById("stickyProductPrice");
  const productStock = document.getElementById("productStock");
  const productMainImage = document.getElementById("productMainImage");
  const addToCartBtn = document.getElementById("productAddToCart");
  const stickyAddToCartBtn = document.getElementById("stickyAddToCartBtn");
  const productPurchaseBox = document.getElementById("productPurchaseBox");
  const stickyBar = document.getElementById("stickyProductBar");
  const productForm = document.querySelector(".ajax-product-form");

  const variationData = window.AXIOM_PRODUCT_PAGE || { isVariable: false, productId: 0, variations: [] };

  function openCartDrawerIfAvailable() {
    const cartToggle = document.getElementById("cartToggle");
    if (cartToggle) {
      cartToggle.click();
    }
  }

  function syncStickyVisibility() {
    if (!stickyBar || !productPurchaseBox) return;
    const rect = productPurchaseBox.getBoundingClientRect();
    if (rect.bottom < 0) {
      stickyBar.classList.add("active");
    } else {
      stickyBar.classList.remove("active");
    }
  }

  async function postAjax(action, extra = {}) {
    const params = new URLSearchParams();
    params.append("action", action);
    params.append("nonce", AXIOM_THEME.nonce);

    Object.keys(extra).forEach((key) => {
      params.append(key, extra[key]);
    });

    const response = await fetch(AXIOM_THEME.ajaxUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: params.toString(),
      credentials: "same-origin",
    });

    return response.json();
  }

  async function addProductAjax(payload) {
    try {
      const result = await postAjax("axiom_add_product_from_product_page", payload);
      if (result && result.success) {
        if (document.body && window.jQuery) {
          jQuery(document.body).trigger("added_to_cart");
        }
        openCartDrawerIfAvailable();
      }
    } catch (error) {
      console.error("Product add failed:", error);
    }
  }

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

      if (stickyProductPrice) {
        stickyProductPrice.innerHTML = priceHtml;
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

      if (stickyAddToCartBtn) {
        stickyAddToCartBtn.disabled = !variationId || !purchasable;
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

  if (productForm) {
    productForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const quantity = qtyInput ? parseInt(qtyInput.value || "1", 10) : 1;

      if (variationData.isVariable) {
        const variationId = variationIdInput ? variationIdInput.value : "";
        if (!variationId) return;

        const payload = {
          product_id: variationData.productId,
          variation_id: variationId,
          quantity: quantity
        };

        const hiddenFields = productForm.querySelectorAll('input[id^="attribute_"]');
        hiddenFields.forEach((field) => {
          payload[field.id] = field.value;
        });

        await addProductAjax(payload);
      } else {
        await addProductAjax({
          product_id: variationData.productId,
          quantity: quantity
        });
      }
    });
  }

  if (stickyAddToCartBtn) {
    stickyAddToCartBtn.addEventListener("click", function () {
      if (productForm) {
        productForm.requestSubmit();
      }
    });
  }

  window.addEventListener("scroll", syncStickyVisibility, { passive: true });
  syncStickyVisibility();
});
