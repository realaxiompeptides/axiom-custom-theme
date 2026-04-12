<?php
defined('ABSPATH') || exit;

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
    return;
}

$product_id        = $product->get_id();
$product_name      = $product->get_name();
$product_permalink = get_permalink($product_id);
$product_image_id  = $product->get_image_id();
$product_image_url = $product_image_id ? wp_get_attachment_image_url($product_image_id, 'large') : wc_placeholder_img_src();
$product_short     = $product->get_short_description();
$product_long      = $product->get_description();
$product_price     = $product->get_price_html();
$product_sku       = $product->get_sku();

$is_variable = $product->is_type('variable');
$is_simple   = $product->is_type('simple');

$stock_text = '';
$stock_class = 'stock-backorder';

if ($product->managing_stock()) {
    $qty = (int) $product->get_stock_quantity();

    if ($qty > 5) {
        $stock_text = $qty . ' available';
        $stock_class = 'stock-high';
    } elseif ($qty > 0) {
        $stock_text = $qty . ' available';
        $stock_class = 'stock-low';
    } else {
        $stock_text = $product->backorders_allowed() ? 'Available on backorder' : 'Out of stock';
        $stock_class = $product->backorders_allowed() ? 'stock-backorder' : 'stock-unavailable';
    }
} else {
    if ($product->is_in_stock()) {
        $stock_text = 'In stock';
        $stock_class = 'stock-high';
    } else {
        $stock_text = $product->backorders_allowed() ? 'Available on backorder' : 'Out of stock';
        $stock_class = $product->backorders_allowed() ? 'stock-backorder' : 'stock-unavailable';
    }
}

$available_variations = $is_variable ? $product->get_available_variations() : array();
$variation_options = array();

if ($is_variable && !empty($available_variations)) {
    foreach ($available_variations as $variation) {
        $variation_obj = wc_get_product($variation['variation_id']);
        if (!$variation_obj) {
            continue;
        }

        $label_parts = array();
        foreach ($variation['attributes'] as $attr_key => $attr_value) {
            if (!$attr_value) {
                continue;
            }
            $label_parts[] = ucwords(str_replace('-', ' ', $attr_value));
        }

        $variation_stock_text = '';
        $variation_stock_class = 'stock-backorder';

        if ($variation_obj->managing_stock()) {
            $vqty = (int) $variation_obj->get_stock_quantity();
            if ($vqty > 5) {
                $variation_stock_text = $vqty . ' available';
                $variation_stock_class = 'stock-high';
            } elseif ($vqty > 0) {
                $variation_stock_text = $vqty . ' available';
                $variation_stock_class = 'stock-low';
            } else {
                $variation_stock_text = $variation_obj->backorders_allowed() ? 'Available on backorder' : 'Out of stock';
                $variation_stock_class = $variation_obj->backorders_allowed() ? 'stock-backorder' : 'stock-unavailable';
            }
        } else {
            $variation_stock_text = $variation_obj->is_in_stock() ? 'In stock' : 'Out of stock';
            $variation_stock_class = $variation_obj->is_in_stock() ? 'stock-high' : 'stock-unavailable';
        }

        $variation_options[] = array(
            'variation_id' => $variation['variation_id'],
            'label'        => implode(' / ', $label_parts),
            'price_html'   => $variation_obj->get_price_html(),
            'image'        => !empty($variation['image']['src']) ? $variation['image']['src'] : $product_image_url,
            'stock_text'   => $variation_stock_text,
            'stock_class'  => $variation_stock_class,
            'attributes'   => $variation['attributes'],
            'purchasable'  => $variation_obj->is_purchasable() && ($variation_obj->is_in_stock() || $variation_obj->backorders_allowed()),
        );
    }
}
?>

<main class="product-main">
  <section class="product-shell">
    <div class="container">
      <div class="product-breadcrumbs">
        <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
        <span>/</span>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Products</a>
        <span>/</span>
        <strong><?php echo esc_html($product_name); ?></strong>
      </div>

      <div class="product-layout">
        <div class="product-gallery-card">
          <?php if ($product->is_on_sale()) : ?>
            <div class="product-badge">Sale</div>
          <?php endif; ?>

          <div class="product-gallery-main">
            <img
              id="productMainImage"
              src="<?php echo esc_url($product_image_url); ?>"
              alt="<?php echo esc_attr($product_name); ?>"
            />
          </div>
        </div>

        <div class="product-info-card">
          <h1 id="productName"><?php echo esc_html($product_name); ?></h1>

          <div class="product-price-row" id="productPriceWrap">
            <span class="product-price-current" id="productPrice"><?php echo wp_kses_post($product_price); ?></span>
          </div>

          <?php if (!empty($product_short)) : ?>
            <div class="product-short-description" id="productShortDescription">
              <?php echo wp_kses_post(wpautop($product_short)); ?>
            </div>
          <?php endif; ?>

          <?php include get_template_directory() . '/product-page/icon-benefits.php'; ?>

          <p id="productStock" class="product-stock-text <?php echo esc_attr($stock_class); ?>">
            <?php echo esc_html($stock_text); ?>
          </p>

          <div class="product-purchase-box">
            <?php if ($is_variable && !empty($variation_options)) : ?>
              <form class="product-form" method="post" action="<?php echo esc_url(wc_get_cart_url()); ?>">
                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                <input type="hidden" name="variation_id" id="variation_id" value="">
                <?php
                $first_attributes = !empty($variation_options[0]['attributes']) ? $variation_options[0]['attributes'] : array();
                foreach ($product->get_variation_attributes() as $attribute_name => $options) :
                    $field_name = 'attribute_' . sanitize_title($attribute_name);
                    ?>
                    <input type="hidden" name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name); ?>" value="">
                <?php endforeach; ?>

                <div class="product-option-group">
                  <label class="product-option-label" for="productVariantSelect">Choose Variant</label>
                  <div class="product-select-wrap">
                    <select id="productVariantSelect" class="product-variant-select">
                      <option value="">Select a variant</option>
                      <?php foreach ($variation_options as $option) : ?>
                        <option
                          value="<?php echo esc_attr($option['variation_id']); ?>"
                          data-price-html="<?php echo esc_attr(wp_strip_all_tags($option['price_html'])); ?>"
                          data-image="<?php echo esc_url($option['image']); ?>"
                          data-stock-text="<?php echo esc_attr($option['stock_text']); ?>"
                          data-stock-class="<?php echo esc_attr($option['stock_class']); ?>"
                          data-purchasable="<?php echo $option['purchasable'] ? '1' : '0'; ?>"
                          data-attributes="<?php echo esc_attr(wp_json_encode($option['attributes'])); ?>"
                        >
                          <?php echo esc_html($option['label']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <span class="product-select-icon">
                      <i class="fa-solid fa-chevron-down"></i>
                    </span>
                  </div>
                </div>

                <div class="product-option-group">
                  <label class="product-option-label" for="productQty">Quantity</label>
                  <div class="product-qty-wrap">
                    <button type="button" class="product-qty-btn" id="qtyMinus" aria-label="Decrease quantity">−</button>
                    <input id="productQty" class="product-qty-input" type="number" name="quantity" value="1" min="1" inputmode="numeric" />
                    <button type="button" class="product-qty-btn" id="qtyPlus" aria-label="Increase quantity">+</button>
                  </div>
                </div>

                <button id="productAddToCart" class="product-add-to-cart-btn" type="submit" disabled>
                  Select Variant
                </button>
              </form>

            <?php else : ?>
              <form class="product-form" method="post" action="<?php echo esc_url(wc_get_cart_url()); ?>">
                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">

                <div class="product-option-group">
                  <label class="product-option-label" for="productQty">Quantity</label>
                  <div class="product-qty-wrap">
                    <button type="button" class="product-qty-btn" id="qtyMinus" aria-label="Decrease quantity">−</button>
                    <input id="productQty" class="product-qty-input" type="number" name="quantity" value="1" min="1" inputmode="numeric" />
                    <button type="button" class="product-qty-btn" id="qtyPlus" aria-label="Increase quantity">+</button>
                  </div>
                </div>

                <button id="productAddToCart" class="product-add-to-cart-btn" type="submit" <?php disabled(!$product->is_purchasable()); ?>>
                  <?php echo $product->is_purchasable() ? 'Add To Cart' : 'Unavailable'; ?>
                </button>
              </form>
            <?php endif; ?>

            <div class="product-meta">
              <?php if (!empty($product_sku)) : ?>
                <div class="product-meta-item">
                  <i class="fa-solid fa-barcode"></i>
                  <span>SKU: <?php echo esc_html($product_sku); ?></span>
                </div>
              <?php endif; ?>

              <div class="product-meta-item">
                <i class="fa-solid fa-truck-fast"></i>
                <span>Fast USA fulfillment</span>
              </div>

              <div class="product-meta-item">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Research use only</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <section class="product-description-card">
        <div class="product-description-header">
          <p class="product-section-kicker">Product Details</p>
          <h2>Product Description</h2>
        </div>

        <div id="productLongDescription">
          <?php echo wp_kses_post(wpautop($product_long)); ?>
        </div>
      </section>

      <?php include get_template_directory() . '/product-page/why-choose-us.php'; ?>
    </div>
  </section>
</main>

<script>
  window.AXIOM_PRODUCT_PAGE = <?php echo wp_json_encode(array(
    'isVariable' => $is_variable,
    'variations' => $variation_options,
  )); ?>;
</script>
