<?php
/**
 * Template Name: Axiom Custom Cart
 */

defined('ABSPATH') || exit;

get_header();

if (!function_exists('WC')) {
    echo '<main class="axiom-cart-page"><div class="container"><p>WooCommerce is required for this page.</p></div></main>';
    get_footer();
    return;
}

$cart         = WC()->cart;
$cart_url     = wc_get_cart_url();
$checkout_url = wc_get_checkout_url();
$shop_url     = wc_get_page_permalink('shop');

$recommended_slugs = array(
    'glp-3-rt',
    'ghk-cu',
    'mots-c',
    'nad',
    'mt-1',
    'mt-2',
);

$recommended_ids = array();

foreach ($recommended_slugs as $slug) {
    $product_post = get_page_by_path($slug, OBJECT, 'product');
    if ($product_post) {
        $recommended_ids[] = (int) $product_post->ID;
    }
}

$cart_product_ids = array();

if ($cart && !$cart->is_empty()) {
    foreach ($cart->get_cart() as $cart_item) {
        if (!empty($cart_item['product_id'])) {
            $cart_product_ids[] = (int) $cart_item['product_id'];
        }
        if (!empty($cart_item['variation_id'])) {
            $cart_product_ids[] = (int) $cart_item['variation_id'];
        }
    }
}

$recommended_ids = array_values(array_diff(array_unique($recommended_ids), array_unique($cart_product_ids)));

$recommended_products = array();

if (!empty($recommended_ids)) {
    $recommended_products = wc_get_products(array(
        'include' => array_slice($recommended_ids, 0, 6),
        'limit'   => 6,
        'status'  => 'publish',
        'orderby' => 'include',
    ));
}
?>

<main class="axiom-cart-page">
  <section class="axiom-cart-shell">
    <div class="container">
      <div class="axiom-cart-header">
        <p class="axiom-cart-kicker">Your Cart</p>
        <h1>Review Your Order</h1>
        <p>Secure checkout, fast fulfillment, and research-use-only product handling.</p>
      </div>

      <?php wc_print_notices(); ?>

      <?php if ($cart && !$cart->is_empty()) : ?>
        <form class="woocommerce-cart-form axiom-cart-layout" action="<?php echo esc_url($cart_url); ?>" method="post">
          <section class="axiom-cart-main">
            <div class="axiom-cart-card axiom-cart-items-card">
              <div class="axiom-cart-card-header">
                <h2>Products in Cart</h2>
                <span class="axiom-cart-count-pill">
                  <?php echo esc_html($cart->get_cart_contents_count()); ?> item<?php echo $cart->get_cart_contents_count() === 1 ? '' : 's'; ?>
                </span>
              </div>

              <div class="axiom-cart-items">
                <?php foreach ($cart->get_cart() as $cart_item_key => $cart_item) :
                    $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                    if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0) {
                        continue;
                    }

                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                    $thumbnail         = $_product->get_image('woocommerce_thumbnail');
                    $product_name      = $_product->get_name();
                    $product_price     = WC()->cart->get_product_price($_product);
                    $product_subtotal  = WC()->cart->get_product_subtotal($_product, $cart_item['quantity']);
                    $variation_data    = wc_get_formatted_cart_item_data($cart_item, true);
                    ?>
                    <article class="axiom-cart-item">
                      <div class="axiom-cart-item-media">
                        <?php if ($product_permalink) : ?>
                          <a href="<?php echo esc_url($product_permalink); ?>">
                            <?php echo $thumbnail; ?>
                          </a>
                        <?php else : ?>
                          <?php echo $thumbnail; ?>
                        <?php endif; ?>
                      </div>

                      <div class="axiom-cart-item-content">
                        <div class="axiom-cart-item-top">
                          <div class="axiom-cart-item-copy">
                            <h3 class="axiom-cart-item-title">
                              <?php if ($product_permalink) : ?>
                                <a href="<?php echo esc_url($product_permalink); ?>">
                                  <?php echo esc_html($product_name); ?>
                                </a>
                              <?php else : ?>
                                <?php echo esc_html($product_name); ?>
                              <?php endif; ?>
                            </h3>

                            <?php if ($variation_data) : ?>
                              <div class="axiom-cart-item-meta">
                                <?php echo wp_kses_post($variation_data); ?>
                              </div>
                            <?php endif; ?>

                            <div class="axiom-cart-item-unit-price">
                              <?php echo wp_kses_post($product_price); ?>
                            </div>
                          </div>

                          <div class="axiom-cart-item-remove">
                            <?php
                            echo apply_filters(
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                                    esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
                                    esc_attr($product_id),
                                    esc_attr($cart_item_key),
                                    esc_attr($_product->get_sku())
                                ),
                                $cart_item_key
                            );
                            ?>
                          </div>
                        </div>

                        <div class="axiom-cart-item-bottom">
                          <div class="axiom-cart-item-qty">
                            <?php
                            if ($_product->is_sold_individually()) {
                                echo sprintf(
                                    '<span class="axiom-qty-static">1</span><input type="hidden" name="cart[%s][qty]" value="1" />',
                                    esc_attr($cart_item_key)
                                );
                            } else {
                                echo woocommerce_quantity_input(array(
                                    'input_name'   => "cart[{$cart_item_key}][qty]",
                                    'input_value'  => $cart_item['quantity'],
                                    'max_value'    => $_product->get_max_purchase_quantity(),
                                    'min_value'    => '0',
                                    'product_name' => $product_name,
                                ), $_product, false);
                            }
                            ?>
                          </div>

                          <div class="axiom-cart-item-subtotal">
                            <?php echo wp_kses_post($product_subtotal); ?>
                          </div>
                        </div>
                      </div>
                    </article>
                <?php endforeach; ?>
              </div>

              <div class="axiom-cart-actions-row">
                <?php if (wc_coupons_enabled()) : ?>
                  <div class="axiom-cart-coupon">
                    <label for="coupon_code" class="screen-reader-text"><?php esc_html_e('Coupon:', 'woocommerce'); ?></label>
                    <input type="text" name="coupon_code" id="coupon_code" value="" placeholder="Discount code" />
                    <button type="submit" class="button axiom-cart-coupon-btn" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>">
                      Apply Code
                    </button>
                  </div>
                <?php endif; ?>

                <button type="submit" class="button axiom-cart-update-btn" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>">
                  Update Cart
                </button>

                <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
              </div>
            </div>
          </section>

          <aside class="axiom-cart-sidebar">
            <div class="axiom-cart-card axiom-cart-summary-card">
              <div class="axiom-cart-card-header">
                <h2>Order Summary</h2>
              </div>

              <div class="axiom-cart-summary-lines">
                <div class="axiom-summary-line">
                  <span>Subtotal</span>
                  <strong><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></strong>
                </div>

                <?php if (WC()->cart->get_coupons()) : ?>
                  <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                    <div class="axiom-summary-line axiom-summary-discount">
                      <span><?php echo esc_html(wc_cart_totals_coupon_label($coupon, false)); ?></span>
                      <strong><?php echo wp_kses_post(wc_cart_totals_coupon_html($coupon, false)); ?></strong>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>

                <div class="axiom-summary-line">
                  <span>Shipping</span>
                  <strong>Calculated at checkout</strong>
                </div>

                <div class="axiom-summary-line axiom-summary-total">
                  <span>Total</span>
                  <strong><?php echo wp_kses_post(WC()->cart->get_total()); ?></strong>
                </div>
              </div>

              <a class="axiom-cart-checkout-btn" href="<?php echo esc_url($checkout_url); ?>">
                Proceed to Checkout
              </a>

              <a class="axiom-cart-continue-link" href="<?php echo esc_url($shop_url); ?>">
                Continue Shopping
              </a>
            </div>
          </aside>
        </form>
      <?php else : ?>
        <section class="axiom-cart-empty-card">
          <div class="axiom-cart-empty-icon"><span>🛒</span></div>
          <h2>Your cart is currently empty</h2>
          <p>Add research products to begin your order. Fast fulfillment and secure checkout are available once items are added.</p>
          <a href="<?php echo esc_url($shop_url); ?>" class="axiom-cart-checkout-btn axiom-cart-empty-btn">
            Shop Products
          </a>
        </section>
      <?php endif; ?>

      <?php if (!empty($recommended_products)) : ?>
        <section class="axiom-cart-recommendations">
          <div class="axiom-cart-recommendations-header">
            <p class="axiom-cart-kicker">Recommended Products</p>
            <h2>You may also like</h2>
          </div>

          <div class="axiom-cart-recommendations-grid">
            <?php foreach ($recommended_products as $rec_product) :
                if (!$rec_product || !is_a($rec_product, 'WC_Product')) {
                    continue;
                }

                $rec_id      = $rec_product->get_id();
                $rec_name    = $rec_product->get_name();
                $rec_link    = get_permalink($rec_id);
                $rec_image   = $rec_product->get_image('woocommerce_thumbnail');
                $rec_is_sale = $rec_product->is_on_sale();

                if ($rec_product->is_type('variable')) {
                    $rec_regular = $rec_product->get_variation_regular_price('min', true);
                    $rec_current = $rec_product->get_variation_price('min', true);
                } else {
                    $rec_regular = $rec_product->get_regular_price();
                    $rec_current = $rec_product->get_price();
                }
                ?>
                <article class="axiom-cart-rec-card">
                  <div class="axiom-cart-rec-image">
                    <?php if ($rec_is_sale) : ?>
                      <span class="axiom-cart-rec-badge">Sale</span>
                    <?php endif; ?>
                    <a href="<?php echo esc_url($rec_link); ?>">
                      <?php echo $rec_image; ?>
                    </a>
                  </div>

                  <div class="axiom-cart-rec-body">
                    <h3>
                      <a href="<?php echo esc_url($rec_link); ?>">
                        <?php echo esc_html($rec_name); ?>
                      </a>
                    </h3>

                    <div class="axiom-cart-rec-price">
                      <?php if ($rec_is_sale && $rec_regular && (float) $rec_regular > (float) $rec_current) : ?>
                        <span class="axiom-cart-rec-old-price"><?php echo wp_kses_post(wc_price($rec_regular)); ?></span>
                      <?php endif; ?>
                      <span class="axiom-cart-rec-current-price"><?php echo wp_kses_post(wc_price($rec_current)); ?></span>
                    </div>

                    <a href="<?php echo esc_url($rec_link); ?>" class="axiom-cart-rec-btn">
                      View Product
                    </a>
                  </div>
                </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
