document.addEventListener("DOMContentLoaded", function () {
  const content = document.querySelector(".woocommerce-MyAccount-content");
  if (!content) return;

  const path = window.location.pathname.replace(/\/+$/, "");

  // DO NOT run on single order details page.
  if (path.includes("/view-order")) return;

  // Only run on the orders list page.
  if (!path.includes("/orders")) return;

  const links = Array.from(content.querySelectorAll('a[href*="view-order"]'));
  if (!links.length) return;

  const text = content.innerText;
  const matches = [...text.matchAll(/#(\d+)\s+([A-Za-z]+)\s+Date:\s+(.+?)\s+Total:\s+\$?([\d.]+)\s+Items:\s+(\d+)/g)];
  if (!matches.length) return;

  const hero = content.querySelector(".axiom-account-page-hero");

  const list = document.createElement("div");
  list.className = "axiom-orders-modern-list";

  matches.forEach((m, i) => {
    const card = document.createElement("div");
    card.className = "axiom-order-card";

    card.innerHTML = `
      <div class="axiom-order-card-top">
        <div class="axiom-order-number">#${m[1]}</div>
        <div class="axiom-order-status">${m[2]}</div>
      </div>
      <div class="axiom-order-meta">
        <div><span>Date</span><strong>${m[3]}</strong></div>
        <div><span>Total</span><strong>$${m[4]}</strong></div>
        <div><span>Items</span><strong>${m[5]}</strong></div>
      </div>
    `;

    const btn = links[i]?.cloneNode(true);
    if (btn) {
      btn.className = "axiom-order-view";
      btn.textContent = "View order details";
      card.appendChild(btn);
    }

    list.appendChild(card);
  });

  content.innerHTML = "";
  if (hero) content.appendChild(hero);
  content.appendChild(list);
});
