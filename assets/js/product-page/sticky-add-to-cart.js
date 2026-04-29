document.addEventListener('DOMContentLoaded', function () {
    const realForm = document.getElementById('ajaxProductForm');
    const realAddToCartBtn = document.getElementById('productAddToCart');
    const stickyBar = document.getElementById('stickyProductBar');
    const stickyBtn = document.getElementById('stickyAddToCartBtn');
    const stickyQtyValue = document.getElementById('stickyQtyValue');
    const stickyQtyMinus = document.getElementById('stickyQtyMinus');
    const stickyQtyPlus = document.getElementById('stickyQtyPlus');
    const realQty = document.getElementById('productQty');

    if (!realForm || !realAddToCartBtn || !stickyBar || !stickyBtn) {
        return;
    }

    function syncStickyQty() {
        if (!realQty || !stickyQtyValue) {
            return;
        }

        stickyQtyValue.textContent = realQty.value || '1';
    }

    function showStickyIfNeeded() {
        const rect = realAddToCartBtn.getBoundingClientRect();
        const passedButton = rect.bottom < 0;

        if (passedButton) {
            stickyBar.classList.add('is-visible');
            stickyBar.setAttribute('aria-hidden', 'false');
            document.body.classList.add('has-sticky-product-bar');
        } else {
            stickyBar.classList.remove('is-visible');
            stickyBar.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('has-sticky-product-bar');
        }
    }

    if (realQty) {
        realQty.addEventListener('input', syncStickyQty);
        realQty.addEventListener('change', syncStickyQty);
    }

    if (stickyQtyMinus && realQty) {
        stickyQtyMinus.addEventListener('click', function () {
            let current = parseInt(realQty.value || '1', 10);
            current = Math.max(1, current - 1);
            realQty.value = current;
            realQty.dispatchEvent(new Event('change', { bubbles: true }));
            syncStickyQty();
        });
    }

    if (stickyQtyPlus && realQty) {
        stickyQtyPlus.addEventListener('click', function () {
            let current = parseInt(realQty.value || '1', 10);
            let max = realQty.getAttribute('max');

            current = current + 1;

            if (max && parseInt(max, 10) > 0) {
                current = Math.min(current, parseInt(max, 10));
            }

            realQty.value = current;
            realQty.dispatchEvent(new Event('change', { bubbles: true }));
            syncStickyQty();
        });
    }

    stickyBtn.addEventListener('click', function () {
        if (realAddToCartBtn.disabled) {
            realAddToCartBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        realAddToCartBtn.click();

        setTimeout(function () {
            document.dispatchEvent(new CustomEvent('axiom_open_cart_drawer'));
            document.body.classList.add('cart-drawer-open', 'axiom-cart-drawer-open');
        }, 500);
    });

    window.addEventListener('scroll', showStickyIfNeeded, { passive: true });
    window.addEventListener('resize', showStickyIfNeeded);

    syncStickyQty();
    showStickyIfNeeded();
});
