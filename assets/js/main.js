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
  const cartItemsList = document.getElementById("cartItemsList");
  const cartEmptyState = document.getElementById("cartEmptyState");

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

  async function refreshCartDrawer() {
    if (!AXIOM_THEME || !AXIOM_THEME.ajaxUrl) return;

    try {
      const params = new URLSearchParams();
      params.append("action", "axiom_get_cart_drawer");
      params.append("nonce", AXIOM_THEME.nonce);

      const response = await fetch(AXIOM_THEME.ajaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: params.toString(),
        credentials: "same-origin",
      });

      const result = await response.json();

      if (!result || !result.success || !result.data) return;

      const data = result.data;
      const items = Array.isArray(data.items) ? data.items : [];

      if (cartCount) {
        cartCount.textContent = String(data.count || 0);
      }

      if (cartSubtotal) {
        cartSubtotal.innerHTML = data.subtotal || "$0.00";
      }

      if (!cartItemsList || !cartEmptyState) return;

      if (!items.length) {
        cartItemsList.hidden = true;
        cartEmptyState.hidden = false;
        cartItemsList.innerHTML = "";
        return;
      }

      cartEmptyState.hidden = true;
      cartItemsList.hidden = false;

      cartItemsList.innerHTML = items
        .map((item) => {
          return `
            <div class="cart-item-card">
              <a class="cart-item-image-wrap" href="${item.link || "#"}">
                <img src="${item.image}" alt="${item.name}">
              </a>

              <div class="cart-item-content">
                <div class="cart-item-top">
                  <div>
                    <h3 class="cart-item-name">${item.name}</h3>
                    ${item.variant ? `<p class="cart-item-variant">${item.variant}</p>` : ""}
                  </div>
                </div>

                <div class="cart-item-bottom">
                  <div class="cart-qty"><span>${item.quantity}</span></div>
                  <div class="cart-item-price-wrap">
                    <span class="cart-item-price">${item.subtotal}</span>
                  </div>
                </div>
              </div>
            </div>
          `;
        })
        .join("");
    } catch (error) {
      console.error("Cart drawer refresh failed:", error);
    }
  }

  if (menuToggle) menuToggle.addEventListener("click", openMenu);
  if (menuClose) menuClose.addEventListener("click", closeMenu);

  if (cartToggle) {
    cartToggle.addEventListener("click", async function (e) {
      e.preventDefault();
      await refreshCartDrawer();
      openCart();
    });
  }

  if (cartClose) cartClose.addEventListener("click", closeCart);

  if (overlay) {
    overlay.addEventListener("click", function () {
      closeMenu();
      closeCart();
    });
  }

  document.body.addEventListener("added_to_cart", function () {
    refreshCartDrawer();
  });

  jQuery(document.body).on("added_to_cart removed_from_cart updated_cart_totals wc_fragments_refreshed", function () {
    refreshCartDrawer();
  });

  function initAgeGate() {
    const STORAGE_KEY = "axiom_age_gate_accepted_v1";
    const overlay = document.getElementById("ageGateOverlay");
    const ageCheck = document.getElementById("ageGateAgeCheck");
    const useCheck = document.getElementById("ageGateUseCheck");
    const enterBtn = document.getElementById("ageGateEnterBtn");
    const exitBtn = document.getElementById("ageGateExitBtn");
    const logo = document.getElementById("ageGateLogo");

    if (!overlay || !ageCheck || !useCheck || !enterBtn || !exitBtn || !logo) return;

    logo.src = AXIOM_THEME.themeUrl + "/assets/images/axiom-menu-logo.PNG";

    function syncButton() {
      enterBtn.disabled = !(ageCheck.checked && useCheck.checked);
    }

    function openGate() {
      overlay.classList.add("active");
      body.classList.add("age-gate-locked");
    }

    function closeGate() {
      overlay.classList.remove("active");
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
