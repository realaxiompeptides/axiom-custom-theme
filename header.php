<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"
  />
  <meta
    name="description"
    content="Axiom Peptides offers premium research compounds with a clean, trusted, and modern experience."
  />
  <?php wp_head(); ?>
</head>
<body <?php body_class('home-page'); ?>>
<?php wp_body_open(); ?>

<div id="ageGateMount"></div>

<div class="top-announcement-bar">
  <div class="top-announcement-track">
    <span class="announcement-item">🇺🇸 USA Fulfilled Orders</span>
    <span class="announcement-sep">|</span>
    <span class="announcement-item">🧪 Third-Party Lab Tested</span>
    <span class="announcement-sep">|</span>
    <span class="announcement-item">⭐ Trusted by 4,200+ Researchers</span>

    <span class="announcement-sep">|</span>
    <span class="announcement-item">🇺🇸 USA Fulfilled Orders</span>
    <span class="announcement-sep">|</span>
    <span class="announcement-item">🧪 Third-Party Lab Tested</span>
    <span class="announcement-sep">|</span>
    <span class="announcement-item">⭐ Trusted by 4,200+ Researchers</span>
  </div>
</div>

<header class="site-header">
  <div class="header-inner container">
    <button
      class="icon-btn hamburger-btn"
      id="menuToggle"
      aria-label="Open menu"
      aria-controls="mobileMenu"
      aria-expanded="false"
      type="button"
    >
      <span></span>
      <span></span>
      <span></span>
    </button>

    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" aria-label="Axiom Peptides home">
      <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/axiom-logo.PNG'); ?>" alt="Axiom Peptides logo" />
    </a>

    <button
      class="icon-btn cart-btn"
      id="cartToggle"
      aria-label="Open cart"
      aria-controls="cartDrawer"
      aria-expanded="false"
      type="button"
    >
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2Zm10 0c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2ZM7.17 14h9.96c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 21.58 5H6.21l-.94-2H2v2h2l3.6 7.59-1.35 2.44A1.98 1.98 0 0 0 6 16c0 1.1.9 2 2 2h12v-2H8l1.17-2Z"/>
      </svg>
      <span class="cart-count" id="cartCount">0</span>
    </button>
  </div>
</header>

<aside class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <div class="mobile-menu-inner">
    <div class="mobile-menu-header">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="mobile-menu-logo" aria-label="Axiom Peptides home">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/axiom-menu-logo.PNG'); ?>" alt="Axiom Peptides logo" />
      </a>

      <button class="drawer-close" id="menuClose" aria-label="Close menu" type="button">
        &times;
      </button>
    </div>

    <nav class="mobile-nav">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="mobile-nav-link">
        <span>Home</span>
        <span class="mobile-nav-arrow">›</span>
      </a>

      <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="mobile-nav-link">
        <span>Products</span>
        <span class="mobile-nav-arrow">›</span>
      </a>

      <a href="<?php echo esc_url(home_url('/track-order/')); ?>" class="mobile-nav-link">
        <span>Track Your Order</span>
        <span class="mobile-nav-arrow">›</span>
      </a>

      <a href="<?php echo esc_url(home_url('/affiliate-program/')); ?>" class="mobile-nav-link">
        <span>Affiliate Program</span>
        <span class="mobile-nav-arrow">›</span>
      </a>

      <a href="<?php echo esc_url(home_url('/contact-us/')); ?>" class="mobile-nav-link">
        <span>Contact Us</span>
        <span class="mobile-nav-arrow">›</span>
      </a>
    </nav>

    <div class="mobile-menu-divider"></div>

    <div class="mobile-menu-secondary">
      <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/')); ?>" class="mobile-nav-link secondary-link">
        <span>Account</span>
        <span class="mobile-nav-arrow">›</span>
      </a>
    </div>
  </div>
</aside>

<aside class="cart-drawer" id="cartDrawer" aria-hidden="true">
  <div class="cart-drawer-inner">
    <div class="cart-drawer-header">
      <h2>Your Cart</h2>
      <button class="drawer-close" id="cartClose" aria-label="Close cart" type="button">
        &times;
      </button>
    </div>

    <div class="cart-body">
      <div class="cart-empty-state" id="cartEmptyState">
        <p class="cart-empty-text">Your cart is currently empty.</p>
        <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="cart-pill-btn cart-outline-btn">Browse Products</a>
      </div>

      <div class="cart-items-list" id="cartItemsList" hidden></div>
    </div>

    <div class="cart-footer">
      <div class="cart-subtotal-row">
        <span>Subtotal</span>
        <strong id="cartSubtotal">$0.00</strong>
      </div>

      <div class="cart-action-stack">
        <a href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/')); ?>" class="cart-pill-btn cart-muted-btn">View Cart</a>
        <a href="<?php echo esc_url(function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/')); ?>" class="cart-pill-btn cart-outline-btn">Checkout</a>
      </div>
    </div>
  </div>
</aside>

<div class="site-overlay" id="siteOverlay"></div>
