document.addEventListener("DOMContentLoaded", function () {
  const qtyInput = document.getElementById("productQty");
  const qtyMinus = document.getElementById("qtyMinus");
  const qtyPlus = document.getElementById("qtyPlus");

  const stickyQtyMinus = document.getElementById("stickyQtyMinus");
  const stickyQtyPlus = document.getElementById("stickyQtyPlus");
  const stickyQtyValue = document.getElementById("stickyQtyValue");

  const variationSelect = document.getElementById("productVariantSelect");
  const variationIdInput = document.getElementById("variation_id");
  const productPrice = document.getElementById("productPrice");
  const stickyProductPrice = document.getElementById("stickyProductPrice");
  const stickyProductVariant = document.getElementById("stickyProductVariant");
  const stickyProductImage = document.getElementById("stickyProductImage");
  const productStock = document.getElementById("productStock");
  const productMainImage = document.getElementById("productMainImage");
  const addToCartBtn = document.getElementById("productAddToCart");
  const stickyAddToCartBtn = document.getElementById("stickyAddToCartBtn");
  const productPurchaseBox = document.getElementById("productPurchaseBox");
  const stickyBar = document.getElementById("stickyProductBar");
  const productForm = document.getElementById("ajaxProductForm");

  const productCompareRow = document.getElementById("productCompareRow");
  const productComparePrice = document.getElementById("productComparePrice");
  const productSavePill = document.getElementById("productSavePill");

  const variationData = window.AXIOM_PRODUCT_PAGE || {
    isVariable: false,
    productId: 0,
    variations: []
  };

  function syncQtyDisplays(value) {
    const safeValue = Math.max(1, parseInt(value || "1", 10) || 1);
    if (qtyInput) qtyInput.value = safeValue;
    if (stickyQtyValue) stickyQtyValue.textContent = safeValue;
  }

  function currentQty() {
    return Math.max(1, parseInt(qtyInput ? qtyInput.value : "1", 10) || 1);
  }

  function increaseQty() {
    syncQtyDisplays(currentQty() + 1);
  }

  function decreaseQty() {
    syncQtyDisplays(Math.max(1, currentQty() - 1));
  }

  function openCartDrawerIfAvailable() {
    const cartToggle = document.getElementById("cartToggle");
    if (cartToggle) {
      cartToggle.click();
    }
  }

  function syncStickyVisibility() {
    if (!stickyBar || !addToCartBtn) return;
    const rect = addToCartBtn.getBoundingClientRect();
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
    const originalMainText = addToCartBtn ? addToCartBtn.textContent : "";
    const originalStickyText = stickyAddToCartBtn ? stickyAddToCartBtn.textContent : "";

    try {
      if (addToCartBtn) {
        addToCartBtn.disabled = true;
        addToCartBtn.textContent = "Adding...";
      }

      if (stickyAddToCartBtn) {
        stickyAddToCartBtn.disabled = true;
        stickyAddToCartBtn.textContent = "Adding...";
      }

      const result = await postAjax("axiom_add_product_from_product_page", payload);

      if (result && result.success) {
        if (window.jQuery) {
          jQuery(document.body).trigger("added_to_cart");
        }
        openCartDrawerIfAvailable();
      } else {
        console.error(result);
      }
    } catch (error) {
      console.error("Product add failed:", error);
    } finally {
      if (addToCartBtn) {
        addToCartBtn.disabled = false;
        addToCartBtn.textContent = originalMainText || "Add To Cart";
      }

      if (stickyAddToCartBtn) {
        stickyAddToCartBtn.disabled = false;
        stickyAddToCartBtn.textContent = originalStickyText || "Add To Cart";
      }
    }
  }

  function updateCompareRow(regularPriceHtml, savePercent, isOnSale) {
    if (!productCompareRow || !productComparePrice || !productSavePill) {
      return;
    }

    if (isOnSale && regularPriceHtml) {
      productComparePrice.innerHTML = regularPriceHtml;
      productSavePill.textContent = savePercent || "";
      productCompareRow.style.display = "";
      productSavePill.style.display = savePercent ? "" : "none";
    } else {
      productComparePrice.innerHTML = "";
      productSavePill.textContent = "";
      productCompareRow.style.display = "none";
      productSavePill.style.display = "none";
    }
  }

  if (qtyMinus) qtyMinus.addEventListener("click", decreaseQty);
  if (qtyPlus) qtyPlus.addEventListener("click", increaseQty);
  if (stickyQtyMinus) stickyQtyMinus.addEventListener("click", decreaseQty);
  if (stickyQtyPlus) stickyQtyPlus.addEventListener("click", increaseQty);

  if (qtyInput) {
    qtyInput.addEventListener("input", function () {
      syncQtyDisplays(qtyInput.value);
    });
  }

  syncQtyDisplays(1);

  if (variationSelect && variationData.isVariable) {
    variationSelect.addEventListener("change", function () {
      const selected = variationSelect.options[variationSelect.selectedIndex];
      const variationId = selected.value || "";
      const variationLabel = selected.getAttribute("data-label") || "Select variant";
      const priceHtml = selected.getAttribute("data-price-html") || "";
      const regularPriceHtml = selected.getAttribute("data-regular-price-html") || "";
      const savePercent = selected.getAttribute("data-save-percent") || "";
      const isOnSale = selected.getAttribute("data-is-on-sale") === "1";
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

      if (stickyProductVariant) {
        stickyProductVariant.textContent = variationId ? variationLabel : "Select variant";
      }

      if (productMainImage && image) {
        productMainImage.src = image;
      }

      if (stickyProductImage && image) {
        stickyProductImage.src = image;
      }

      if (productStock) {
        productStock.textContent = stockText;
        productStock.className = "product-stock-text " + stockClass;
      }

      updateCompareRow(regularPriceHtml, savePercent, isOnSale);

      if (addToCartBtn) {
        addToCartBtn.disabled = !variationId || !purchasable;
        addToCartBtn.textContent = variationId
          ? (purchasable ? "Add To Cart" : "Unavailable")
          : "Select Variant";
      }

      if (stickyAddToCartBtn) {
        stickyAddToCartBtn.disabled = !variationId || !purchasable;
        stickyAddToCartBtn.textContent = variationId
          ? (purchasable ? "Add To Cart" : "Unavailable")
          : "Select Variant";
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
  } else {
    if (stickyProductVariant) {
      stickyProductVariant.textContent = "Ready to add";
    }
  }

  async function handleProductSubmit() {
    const quantity = currentQty();

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
  }

  if (productForm) {
    productForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      await handleProductSubmit();
    });
  }

  if (stickyAddToCartBtn) {
    stickyAddToCartBtn.addEventListener("click", async function () {
      await handleProductSubmit();
    });
  }

  window.addEventListener("scroll", syncStickyVisibility, { passive: true });
  syncStickyVisibility();
});
