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

  function getCart() {
    try {
      return JSON.parse(localStorage.getItem("axiom_cart") || "[]");
    } catch (e) {
      return [];
    }
  }

  function formatMoney(value) {
    return "$" + Number(value || 0).toFixed(2);
  }

  function updateCartDrawer() {
    const cart = getCart();
    let count = 0;
    let subtotal = 0;

    if (cartCount) {
      cart.forEach((item) => {
        count += Number(item.quantity || item.qty || 0);
      });
      cartCount.textContent = String(count);
    }

    if (!cartItemsList || !cartEmptyState || !cartSubtotal) return;

    if (!cart.length) {
      cartItemsList.hidden = true;
      cartEmptyState.hidden = false;
      cartItemsList.innerHTML = "";
      cartSubtotal.textContent = "$0.00";
      return;
    }

    cartItemsList.hidden = false;
    cartEmptyState.hidden = true;

    cartItemsList.innerHTML = cart
      .map((item) => {
        const qty = Number(item.quantity || item.qty || 1);
        const price = Number(item.price || 0);
        subtotal += qty * price;

        const image = item.image || (AXIOM_THEME.themeUrl + "/assets/images/axiom-logo.PNG");
        const name = item.name || "Product";
        const variant = item.variantLabel || item.variant || "";

        return `
          <div class="cart-item-card">
            <div class="cart-item-image-wrap">
              <img src="${image}" alt="${name}">
            </div>
            <div class="cart-item-content">
              <div class="cart-item-top">
                <div>
                  <h3 class="cart-item-name">${name}</h3>
                  ${variant ? `<p class="cart-item-variant">${variant}</p>` : ""}
                </div>
              </div>
              <div class="cart-item-bottom">
                <div class="cart-qty"><span>${qty}</span></div>
                <div class="cart-item-price-wrap">
                  <span class="cart-item-price">${formatMoney(price * qty)}</span>
                </div>
              </div>
            </div>
          </div>
        `;
      })
      .join("");

    cartSubtotal.textContent = formatMoney(subtotal);
  }

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

  if (menuToggle) menuToggle.addEventListener("click", openMenu);
  if (menuClose) menuClose.addEventListener("click", closeMenu);

  if (cartToggle) cartToggle.addEventListener("click", function () {
    updateCartDrawer();
    openCart();
  });

  if (cartClose) cartClose.addEventListener("click", closeCart);

  if (overlay) {
    overlay.addEventListener("click", function () {
      closeMenu();
      closeCart();
    });
  }

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
  updateCartDrawer();

  window.addEventListener("storage", updateCartDrawer);
  window.addEventListener("axiom-cart-updated", updateCartDrawer);
});
