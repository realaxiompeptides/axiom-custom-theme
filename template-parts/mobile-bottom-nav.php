<?php
if (!defined('ABSPATH')) {
    exit;
}

$cart_count = 0;

if (function_exists('WC') && WC()->cart) {
    $cart_count = WC()->cart->get_cart_contents_count();
}
?>

<nav class="axiom-mobile-bottom-nav" aria-label="Mobile bottom navigation">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="axiom-mobile-bottom-nav-link">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="axiom-mobile-bottom-nav-link">
        <i class="fa-solid fa-bag-shopping"></i>
        <span>Shop</span>
    </a>

    <a href="<?php echo esc_url(home_url('/coa-page/')); ?>" class="axiom-mobile-bottom-nav-link">
        <i class="fa-solid fa-file-circle-check"></i>
        <span>COAs</span>
    </a>

    <button type="button" class="axiom-mobile-bottom-nav-link axiom-mobile-bottom-cart-btn" id="axiomMobileBottomCartBtn">
        <span class="axiom-mobile-bottom-cart-icon">
            <i class="fa-solid fa-cart-shopping"></i>
            <?php if ($cart_count > 0) : ?>
                <em><?php echo esc_html($cart_count); ?></em>
            <?php endif; ?>
        </span>
        <span>Cart</span>
    </button>

    <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/')); ?>" class="axiom-mobile-bottom-nav-link">
        <i class="fa-regular fa-user"></i>
        <span>Account</span>
    </a>
</nav>

<script>
document.addEventListener('click', function(event) {
    var cartBtn = event.target.closest('#axiomMobileBottomCartBtn');

    if (!cartBtn) {
        return;
    }

    event.preventDefault();

    document.dispatchEvent(new CustomEvent('axiom_open_cart_drawer'));
    document.body.classList.add('cart-drawer-open', 'axiom-cart-drawer-open');

    var drawerButtons = document.querySelectorAll('[data-cart-open], .cart-drawer-toggle, .header-cart-button, .cart-icon, .menu-cart-button');

    if (drawerButtons.length) {
        drawerButtons[0].click();
    }
});
</script>
