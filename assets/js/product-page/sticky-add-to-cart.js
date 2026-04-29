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

    function getQty() {
        if (!realQty) {
            return 1;
        }

        let current = parseInt(realQty.value || '1', 10);

        if (isNaN(current) || current < 1) {
            current = 1;
        }

        return current;
    }

    function getMaxQty() {
        if (!realQty) {
            return 0;
        }

        const max = parseInt(realQty.getAttribute('max') || '0', 10);

        if (isNaN(max) || max < 1) {
            return 0;
        }

        return max;
    }

    function setQty(value) {
        if (!realQty || !stickyQtyValue) {
            return;
        }

        let nextQty = parseInt(value, 10);

        if (isNaN(nextQty) || nextQty < 1) {
            nextQty = 1;
        }

        const maxQty = getMaxQty();

        if (maxQty > 0) {
            nextQty = Math.min(nextQty, maxQty);
        }

        realQty.value = nextQty;
        stickyQtyValue.textContent = nextQty;

        realQty.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function syncStickyQty() {
        setQty(getQty());
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
        realQty.addEventListener('input', function () {
            if (stickyQtyValue) {
                stickyQtyValue.textContent = getQty();
            }
        });

        realQty.addEventListener('change', function () {
            if (stickyQtyValue) {
                stickyQtyValue.textContent = getQty();
            }
        });
    }

    document.addEventListener('click', function (event) {
        const minusBtn = event.target.closest('#stickyQtyMinus');
        const plusBtn = event.target.closest('#stickyQtyPlus');

        if (!minusBtn && !plusBtn) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        if (minusBtn) {
            setQty(getQty() - 1);
        }

        if (plusBtn) {
            setQty(getQty() + 1);
        }
    }, true);

    stickyBtn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

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
