document.addEventListener("DOMContentLoaded", function () {
  const body = document.body;
  const overlay = document.getElementById("siteOverlay");

  const menuToggle = document.getElementById("menuToggle");
  const menuClose = document.getElementById("menuClose");
  const mobileMenu = document.getElementById("mobileMenu");

  const cartToggle = document.getElementById("cartToggle");
  const cartClose = document.getElementById("cartClose");
  const cartDrawer = document.getElementById("cartDrawer");
  const cartCount = document.getElementById("cartCount");
  const cartSubtotal = document.getElementById("cartSubtotal");
  const cartShippingValue = document.getElementById("cartShippingValue");
  const cartItemsList = document.getElementById("cartItemsList");
  const cartEmptyState = document.getElementById("cartEmptyState");
  const cartItemCountBadge = document.getElementById("cartItemCountBadge");

  function openMenu() {
    if (!mobileMenu || !overlay) return;
    mobileMenu.classList.add("active");
    overlay.classList.add("active");
    body.style.overflow = "hidden";
  }

  function closeMenu() {
    if (!mobileMenu || !overlay) return;
    mobileMenu.classList.remove("active");
    overlay.classList.remove("active");
    body.style.overflow = "";
  }

  function openCart() {
    if (!cartDrawer || !overlay) return;
    cartDrawer.classList.add("active");
    overlay.classList.add("active");
    body.style.overflow = "hidden";
  }

  function closeCart() {
    if (!cartDrawer || !overlay) return;
    cartDrawer.classList.remove("active");
    overlay.classList.remove("active");
    body.style.overflow = "";
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

  function renderUpsell(upsell) {
    if (!upsell) return "";

    return `
      <div class="cart-upsell-card" id="cartUpsellCard">
        <div class="cart-upsell-image-wrap">
          <img src="${upsell.image}" alt="${upsell.name}">
        </div>

        <div class="cart-upsell-copy">
          <span class="cart-upsell-kicker">Recommended Add-On</span>
          <h3 class="cart-upsell-title">${upsell.name}</h3>
          <div class="cart-upsell-price">${upsell.priceHtml}</div>
        </div>

        <div class="cart-upsell-right">
          <button
            class="cart-upsell-btn"
            type="button"
            data-add-product-id="${upsell.productId}"
            data-add-variation-id="${upsell.variationId || ""}"
            data-add-attributes='${upsell.attributes ? JSON.stringify(upsell.attributes) : "{}"}'
          >
            Add
          </button>
        </div>
      </div>
    `;
  }

  function renderCartItem(item) {
    return `
      <div class="cart-item-card" data-cart-key="${item.key}">
        <a class="cart-item-image-wrap" href="${item.link || "#"}">
          <img src="${item.image}" alt="${item.name}">
        </a>

        <div class="cart-item-main">
          <div class="cart-item-top-row">
            <div class="cart-item-meta">
              <h3 class="cart-item-name">${item.name}</h3>
              ${item.variant ? `<p class="cart-item-variant">${item.variant}</p>` : ""}
            </div>

            <button
              class="cart-remove-btn"
              type="button"
              aria-label="Remove item"
              data-remove-cart-key="${item.key}"
            >
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>

          <div class="cart-item-bottom-row">
            <div class="cart-qty-control">
              <button type="button" class="cart-qty-btn" data-qty-action="decrease" data-cart-key="${item.key}">−</button>
              <span class="cart-qty-value">${item.quantity}</span>
              <button type="button" class="cart-qty-btn" data-qty-action="increase" data-cart-key="${item.key}">+</button>
            </div>

            <div class="cart-item-price-wrap">
              <span class="cart-item-price">${item.subtotal}</span>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function renderCartDrawer(data) {
    const items = Array.isArray(data.items) ? data.items : [];
    const count = Number(data.count || 0);

    if (cartCount) {
      cartCount.textContent = String(count);
    }

    if (cartSubtotal) {
      cartSubtotal.innerHTML = data.subtotal || "$0.00";
    }

    if (cartShippingValue) {
      cartShippingValue.textContent = data.shippingLabel || "Calculated at checkout";
    }

    if (cartItemCountBadge) {
      if (count > 0) {
        cartItemCountBadge.hidden = false;
        cartItemCountBadge.textContent = `${count} item${count === 1 ? "" : "s"}`;
      } else {
        cartItemCountBadge.hidden = true;
        cartItemCountBadge.textContent = "";
      }
    }

    if (!cartItemsList || !cartEmptyState) return;

    if (!items.length) {
      cartEmptyState.hidden = false;
      cartItemsList.hidden = false;
      cartItemsList.innerHTML = data.upsell ? renderUpsell(data.upsell) : "";
      return;
    }

    cartEmptyState.hidden = true;
    cartItemsList.hidden = false;

    cartItemsList.innerHTML = `
      <div class="cart-items-stack">
        ${items.map(renderCartItem).join("")}
      </div>
      ${data.upsell ? renderUpsell(data.upsell) : ""}
    `;
  }

  async function refreshCartDrawer() {
    try {
      const result = await postAjax("axiom_get_cart_drawer");

      if (!result || !result.success || !result.data) return;
      renderCartDrawer(result.data);
    } catch (error) {
      console.error("Cart drawer refresh failed:", error);
    }
  }

  async function updateCartQuantity(cartKey, quantity) {
    try {
      const result = await postAjax("axiom_update_cart_item_quantity", {
        cart_key: cartKey,
        quantity: quantity,
      });

      if (!result || !result.success || !result.data) return;
      renderCartDrawer(result.data);
    } catch (error) {
      console.error("Update quantity failed:", error);
    }
  }

  async function removeCartItem(cartKey) {
    try {
      const result = await postAjax("axiom_remove_cart_item", {
        cart_key: cartKey,
      });

      if (!result || !result.success || !result.data) return;
      renderCartDrawer(result.data);
    } catch (error) {
      console.error("Remove item failed:", error);
    }
  }

  async function addUpsellProduct(button) {
    if (!button) return;

    const productId = button.getAttribute("data-add-product-id");
    const variationId = button.getAttribute("data-add-variation-id") || "";
    const attributesRaw = button.getAttribute("data-add-attributes") || "{}";

    let attributes = {};
    try {
      attributes = JSON.parse(attributesRaw);
    } catch (error) {
      console.error("Failed to parse upsell attributes:", error);
    }

    const originalText = button.textContent;

    try {
      button.disabled = true;
      button.textContent = "Adding...";

      const payload = {
        product_id: productId,
      };

      if (variationId) {
        payload.variation_id = variationId;
      }

      Object.keys(attributes).forEach((key) => {
        payload[key] = attributes[key];
      });

      const result = await postAjax("axiom_add_simple_product_to_cart", payload);

      if (!result || !result.success || !result.data) {
        if (result && result.data && result.data.message) {
          alert(result.data.message);
        }
        return;
      }

      renderCartDrawer(result.data);
      openCart();
    } catch (error) {
      console.error("Upsell add failed:", error);
    } finally {
      button.disabled = false;
      button.textContent = originalText;
    }
  }

  if (menuToggle) {
    menuToggle.addEventListener("click", function (e) {
      e.preventDefault();
      openMenu();
    });
  }

  if (menuClose) {
    menuClose.addEventListener("click", function (e) {
      e.preventDefault();
      closeMenu();
    });
  }

  if (cartToggle) {
    cartToggle.addEventListener("click", async function (e) {
      e.preventDefault();
      await refreshCartDrawer();
      openCart();
    });
  }

  if (cartClose) {
    cartClose.addEventListener("click", function (e) {
      e.preventDefault();
      closeCart();
    });
  }

  if (overlay) {
    overlay.addEventListener("click", function () {
      closeMenu();
      closeCart();
    });
  }

  if (cartItemsList) {
    cartItemsList.addEventListener("click", async function (e) {
      const qtyBtn = e.target.closest(".cart-qty-btn");
      const removeBtn = e.target.closest("[data-remove-cart-key]");
      const addBtn = e.target.closest("[data-add-product-id]");

      if (qtyBtn) {
        e.preventDefault();

        const cartKey = qtyBtn.getAttribute("data-cart-key");
        const card = qtyBtn.closest(".cart-item-card");
        const valueEl = card ? card.querySelector(".cart-qty-value") : null;
        const currentQty = valueEl ? parseInt(valueEl.textContent, 10) || 1 : 1;
        const action = qtyBtn.getAttribute("data-qty-action");

        let nextQty = currentQty;
        if (action === "increase") nextQty = currentQty + 1;
        if (action === "decrease") nextQty = Math.max(0, currentQty - 1);

        await updateCartQuantity(cartKey, nextQty);
        return;
      }

      if (removeBtn) {
        e.preventDefault();
        const cartKey = removeBtn.getAttribute("data-remove-cart-key");
        await removeCartItem(cartKey);
        return;
      }

      if (addBtn) {
        e.preventDefault();
        await addUpsellProduct(addBtn);
      }
    });
  }

  document.body.addEventListener("added_to_cart", function () {
    refreshCartDrawer();
  });

  if (window.jQuery) {
    jQuery(document.body).on(
      "added_to_cart removed_from_cart updated_cart_totals wc_fragments_refreshed",
      function () {
        refreshCartDrawer();
      }
    );
  }

  function initAgeGate() {
    const STORAGE_KEY = "axiom_age_gate_accepted_v1";
    const gateOverlay = document.getElementById("ageGateOverlay");
    const ageCheck = document.getElementById("ageGateAgeCheck");
    const useCheck = document.getElementById("ageGateUseCheck");
    const enterBtn = document.getElementById("ageGateEnterBtn");
    const exitBtn = document.getElementById("ageGateExitBtn");
    const logo = document.getElementById("ageGateLogo");

    if (!gateOverlay || !ageCheck || !useCheck || !enterBtn || !exitBtn || !logo) return;

    logo.src = AXIOM_THEME.themeUrl + "/assets/images/axiom-menu-logo.PNG";

    function syncButton() {
      enterBtn.disabled = !(ageCheck.checked && useCheck.checked);
    }

    function openGate() {
      gateOverlay.classList.add("active");
      body.classList.add("age-gate-locked");
    }

    function closeGate() {
      gateOverlay.classList.remove("active");
      body.classList.remove("age-gate-locked");
    }

    if (localStorage.getItem(STORAGE_KEY) === "true") {
      closeGate();
    } else {
      openGate();
    }

    ageCheck.addEventListener("change", syncButton);
    useCheck.addEventListener("change", syncButton);

    enterBtn.addEventListener("click", function () {
      if (enterBtn.disabled) return;
      localStorage.setItem(STORAGE_KEY, "true");
      closeGate();
    });

    exitBtn.addEventListener("click", function () {
      window.location.href = "https://www.google.com";
    });

    syncButton();
  }

  initAgeGate();
  refreshCartDrawer();
});
