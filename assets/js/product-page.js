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
  const productForm = document.getElementById("ajaxProductForm");

  const productCompareRow = document.getElementById("productCompareRow");
  const productComparePrice = document.getElementById("productComparePrice");
  const productSavePill = document.getElementById("productSavePill");
  const stickyBar = document.getElementById("stickyProductBar");
  const qtyNote = document.getElementById("productQtyNote");

  const openCoaModalBtn = document.getElementById("openCoaModalBtn");
  const productCoaModal = document.getElementById("productCoaModal");
  const closeCoaModalBtn = document.getElementById("closeCoaModalBtn");
  const coaVariantButtons = document.querySelectorAll(".product-coa-variant-btn");
  const coaModalCurrentLabel = document.getElementById("productCoaModalCurrentLabel");
  const coaModalOpenFile = document.getElementById("productCoaModalOpenFile");
  const coaModalImage = document.getElementById("productCoaModalImage");
  const coaModalPdfState = document.getElementById("productCoaModalPdfState");
  const coaModalPdfLink = document.getElementById("productCoaModalPdfLink");

  const variationData = window.AXIOM_PRODUCT_PAGE || {
    isVariable: false,
    productId: 0,
    variations: [],
    simpleProduct: {
      managingStock: false,
      stockQuantity: null,
      maxQty: "",
      backordersAllowed: false
    }
  };

  const qtyState = {
    maxQty: "",
    stockQuantity: null,
    backordersAllowed: false,
    managingStock: false
  };

  function getNumericMax() {
    const raw = qtyState.maxQty;
    if (raw === "" || raw === null || typeof raw === "undefined") return null;
    const parsed = parseInt(raw, 10);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
  }

  function getStockQuantity() {
    const raw = qtyState.stockQuantity;
    if (raw === "" || raw === null || typeof raw === "undefined") return 0;
    const parsed = parseInt(raw, 10);
    return Number.isFinite(parsed) && parsed >= 0 ? parsed : 0;
  }

  function getSafeQtyValue(requestedValue) {
    let safeValue = parseInt(requestedValue || "1", 10);

    if (!Number.isFinite(safeValue) || safeValue < 1) {
      safeValue = 1;
    }

    const numericMax = getNumericMax();

    if (numericMax && !qtyState.backordersAllowed) {
      safeValue = Math.min(safeValue, numericMax);
    }

    return safeValue;
  }

  function updateQtyNote() {
    if (!qtyNote) return;

    const currentQuantity = getSafeQtyValue(qtyInput ? qtyInput.value : "1");
    const stockQty = getStockQuantity();

    qtyNote.textContent = "";
    qtyNote.style.display = "none";

    if (qtyState.backordersAllowed) {
      if (currentQuantity > stockQty) {
        const backorderCount = currentQuantity - stockQty;
        qtyNote.textContent = `${stockQty} available now • ${backorderCount} item${backorderCount === 1 ? "" : "s"} will be backordered`;
        qtyNote.style.display = "";
      }
      return;
    }
  }

  function applyQtyLimits(requestedValue) {
    const safeValue = getSafeQtyValue(requestedValue);

    if (qtyInput) {
      qtyInput.value = safeValue;
      qtyInput.setAttribute("min", "1");

      const numericMax = getNumericMax();

      if (numericMax && !qtyState.backordersAllowed) {
        qtyInput.setAttribute("max", String(numericMax));
      } else {
        qtyInput.removeAttribute("max");
      }
    }

    if (stickyQtyValue) {
      stickyQtyValue.textContent = safeValue;
    }

    updateQtyNote();
    return safeValue;
  }

  function currentQty() {
    return applyQtyLimits(qtyInput ? qtyInput.value : "1");
  }

  function increaseQty() {
    const current = currentQty();
    const numericMax = getNumericMax();

    if (numericMax && !qtyState.backordersAllowed && current >= numericMax) {
      applyQtyLimits(current);
      return;
    }

    applyQtyLimits(current + 1);
  }

  function decreaseQty() {
    applyQtyLimits(Math.max(1, currentQty() - 1));
  }

  function setQtyRules(config) {
    qtyState.maxQty = config && typeof config.maxQty !== "undefined" ? config.maxQty : "";
    qtyState.stockQuantity = config && typeof config.stockQuantity !== "undefined" ? config.stockQuantity : null;
    qtyState.backordersAllowed = !!(config && config.backordersAllowed);
    qtyState.managingStock = !!(config && config.managingStock);

    applyQtyLimits(currentQty());
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
        if (result && result.data && result.data.message) {
          alert(result.data.message);
        }
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

  function openCoaModal() {
    if (!productCoaModal) return;
    productCoaModal.hidden = false;
    productCoaModal.setAttribute("aria-hidden", "false");
    document.documentElement.classList.add("coa-modal-open");
    document.body.classList.add("coa-modal-open");
  }

  function closeCoaModal() {
    if (!productCoaModal) return;
    productCoaModal.hidden = true;
    productCoaModal.setAttribute("aria-hidden", "true");
    document.documentElement.classList.remove("coa-modal-open");
    document.body.classList.remove("coa-modal-open");
  }

  function setActiveCoaButton(activeButton) {
    if (!coaVariantButtons.length) return;
    coaVariantButtons.forEach((button) => {
      button.classList.toggle("is-active", button === activeButton);
    });
  }

  function updateCoaModalFromButton(button) {
    if (!button) return;

    const label = button.getAttribute("data-coa-label") || "COA FILE";
    const title = button.getAttribute("data-coa-title") || label;
    const url = button.getAttribute("data-coa-url") || "";
    const thumb = button.getAttribute("data-coa-thumb") || "";
    const isPdf = button.getAttribute("data-coa-is-pdf") === "1";

    if (coaModalCurrentLabel) {
      coaModalCurrentLabel.textContent = label;
    }

    if (coaModalOpenFile) {
      coaModalOpenFile.href = url;
    }

    if (coaModalPdfLink) {
      coaModalPdfLink.href = url;
    }

    if (isPdf) {
      if (coaModalImage) {
        coaModalImage.hidden = true;
        coaModalImage.src = "";
        coaModalImage.alt = "";
      }
      if (coaModalPdfState) {
        coaModalPdfState.hidden = false;
      }
    } else {
      if (coaModalImage) {
        coaModalImage.hidden = false;
        coaModalImage.src = thumb || url;
        coaModalImage.alt = title;
      }
      if (coaModalPdfState) {
        coaModalPdfState.hidden = true;
      }
    }

    setActiveCoaButton(button);
  }

  function normalizeText(value) {
    return String(value || "")
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, " ")
      .trim();
  }

  function syncCoaToSelectedVariant(variationLabel) {
    if (!variationLabel || !coaVariantButtons.length) return;

    const target = normalizeText(variationLabel);
    let match = null;

    coaVariantButtons.forEach((button) => {
      if (match) return;

      const buttonLabel = normalizeText(button.getAttribute("data-coa-label") || "");
      if (buttonLabel && (buttonLabel.includes(target) || target.includes(buttonLabel))) {
        match = button;
      }
    });

    if (match) {
      updateCoaModalFromButton(match);
    }
  }

  if (qtyMinus) qtyMinus.addEventListener("click", decreaseQty);
  if (qtyPlus) qtyPlus.addEventListener("click", increaseQty);
  if (stickyQtyMinus) stickyQtyMinus.addEventListener("click", decreaseQty);
  if (stickyQtyPlus) stickyQtyPlus.addEventListener("click", increaseQty);

  if (qtyInput) {
    qtyInput.addEventListener("input", function () {
      applyQtyLimits(qtyInput.value);
    });

    qtyInput.addEventListener("blur", function () {
      applyQtyLimits(qtyInput.value);
    });
  }

  if (openCoaModalBtn) {
    openCoaModalBtn.addEventListener("click", function () {
      openCoaModal();
    });
  }

  if (closeCoaModalBtn) {
    closeCoaModalBtn.addEventListener("click", function () {
      closeCoaModal();
    });
  }

  if (productCoaModal) {
    productCoaModal.addEventListener("click", function (event) {
      const closeTrigger = event.target.closest("[data-coa-close='1']");
      if (closeTrigger) {
        closeCoaModal();
      }
    });
  }

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && productCoaModal && !productCoaModal.hidden) {
      closeCoaModal();
    }
  });

  if (coaVariantButtons.length) {
    coaVariantButtons.forEach((button) => {
      button.addEventListener("click", function () {
        updateCoaModalFromButton(button);
      });
    });

    updateCoaModalFromButton(coaVariantButtons[0]);
  }

  if (variationSelect && variationData.isVariable) {
    setQtyRules({
      managingStock: false,
      stockQuantity: 0,
      maxQty: "",
      backordersAllowed: false
    });

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
      const managingStock = selected.getAttribute("data-managing-stock") === "1";
      const stockQuantityRaw = selected.getAttribute("data-stock-quantity");
      const stockQuantity = stockQuantityRaw === "" ? 0 : parseInt(stockQuantityRaw, 10) || 0;
      const maxQty = selected.getAttribute("data-max-qty") || "";
      const backordersAllowed = selected.getAttribute("data-backorders-allowed") === "1";

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

      setQtyRules({
        managingStock,
        stockQuantity,
        maxQty,
        backordersAllowed
      });

      syncCoaToSelectedVariant(variationLabel);

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
    setQtyRules({
      managingStock: !!variationData.simpleProduct.managingStock,
      stockQuantity: variationData.simpleProduct.stockQuantity,
      maxQty: variationData.simpleProduct.maxQty,
      backordersAllowed: !!variationData.simpleProduct.backordersAllowed
    });

    if (stickyProductVariant) {
      stickyProductVariant.textContent = "Ready to add";
    }
  }

  applyQtyLimits(1);

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
