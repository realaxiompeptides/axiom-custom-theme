<?php
if (!defined('ABSPATH')) {
    exit;
}

$cart_count = 0;

if (function_exists('WC') && WC()->cart) {
    $cart_count = WC()->cart->get_cart_contents_count();
}

$home_url    = home_url('/');
$shop_url    = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$coa_url     = home_url('/coa-page/');
$cart_url    = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
?>

<nav class="axiom-mobile-bottom-nav" aria-label="Mobile bottom navigation">
    <a href="<?php echo esc_url($home_url); ?>" class="axiom-mobile-bottom-nav-link">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <a href="<?php echo esc_url($shop_url); ?>" class="axiom-mobile-bottom-nav-link">
        <i class="fa-solid fa-bag-shopping"></i>
        <span>Shop</span>
    </a>

    <a href="<?php echo esc_url($coa_url); ?>" class="axiom-mobile-bottom-nav-link">
        <i class="fa-solid fa-file-circle-check"></i>
        <span>COAs</span>
    </a>

    <a href="<?php echo esc_url($cart_url); ?>" class="axiom-mobile-bottom-nav-link axiom-mobile-bottom-cart-btn" id="axiomMobileBottomCartBtn">
        <span class="axiom-mobile-bottom-cart-icon">
            <i class="fa-solid fa-cart-shopping"></i>
            <?php if ($cart_count > 0) : ?>
                <em><?php echo esc_html($cart_count); ?></em>
            <?php endif; ?>
        </span>
        <span>Cart</span>
    </a>

    <a href="<?php echo esc_url($account_url); ?>" class="axiom-mobile-bottom-nav-link">
        <i class="fa-regular fa-user"></i>
        <span>Account</span>
    </a>
</nav>
