<?php
defined('ABSPATH') || exit;

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

if (!$product) {
    echo '<main class="product-main"><div class="container"><p>Product not found.</p></div></main>';
    return;
}

$product_id        = $product->get_id();
$product_name      = $product->get_name();
$product_image_id  = $product->get_image_id();
$product_image_url = $product_image_id ? wp_get_attachment_image_url($product_image_id, 'large') : wc_placeholder_img_src();
$product_short     = $product->get_short_description();
$product_long      = $product->get_description();
$product_price     = $product->get_price_html();
$is_variable       = $product->is_type('variable');
$is_on_sale        = $product->is_on_sale();

$regular_price = $product->get_regular_price();
$sale_price    = $product->get_sale_price();

$compare_html = '';
$save_percent = '';

if ($is_on_sale && $regular_price && $sale_price && (float) $regular_price > 0) {
    $percent = round((((float) $regular_price - (float) $sale_price) / (float) $regular_price) * 100);
    $compare_html = wc_price($regular_price);
    $save_percent = $percent > 0 ? 'Save ' . $percent . '%' : '';
}

$stock_text  = $product->is_in_stock() ? 'In stock' : 'Out of stock';
$stock_class = $product->is_in_stock() ? 'stock-high' : 'stock-unavailable';

$simple_max_qty = '';
$simple_manage_stock = false;
$simple_backorders_allowed = $product->backorders_allowed();

if ($product->managing_stock()) {
    $simple_manage_stock = true;
    $qty = (int) $product->get_stock_quantity();
    $simple_max_qty = $qty > 0 ? $qty : '';

    if ($qty > 5) {
        $stock_text  = $qty . ' available';
        $stock_class = 'stock-high';
    } elseif ($qty > 0) {
        $stock_text  = $qty . ' available';
        $stock_class = 'stock-low';
    } elseif ($product->backorders_allowed()) {
        $stock_text  = 'Available on backorder';
        $stock_class = 'stock-backorder';
    } else {
        $stock_text  = 'Out of stock';
        $stock_class = 'stock-unavailable';
    }
} elseif ($product->backorders_allowed()) {
    $stock_text  = 'Available on backorder';
    $stock_class = 'stock-backorder';
}

$variation_options = array();

if ($is_variable) {
    $available_variations = $product->get_available_variations();

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

        $variation_label = implode(' / ', $label_parts);

        $variation_stock_text  = $variation_obj->is_in_stock() ? 'In stock' : 'Out of stock';
        $variation_stock_class = $variation_obj->is_in_stock() ? 'stock-high' : 'stock-unavailable';
        $variation_manage_stock = $variation_obj->managing_stock();
        $variation_backorders_allowed = $variation_obj->backorders_allowed();
        $variation_max_qty = '';

        if ($variation_manage_stock) {
            $vqty = (int) $variation_obj->get_stock_quantity();
            $variation_max_qty = $vqty > 0 ? $vqty : '';

            if ($vqty > 5) {
                $variation_stock_text  = $vqty . ' available';
                $variation_stock_class = 'stock-high';
            } elseif ($vqty > 0) {
                $variation_stock_text  = $vqty . ' available';
                $variation_stock_class = 'stock-low';
            } elseif ($variation_backorders_allowed) {
                $variation_stock_text  = 'Available on backorder';
                $variation_stock_class = 'stock-backorder';
            } else {
                $variation_stock_text  = 'Out of stock';
                $variation_stock_class = 'stock-unavailable';
            }
        } elseif ($variation_backorders_allowed) {
            $variation_stock_text  = 'Available on backorder';
            $variation_stock_class = 'stock-backorder';
        }

        $v_regular = $variation_obj->get_regular_price();
        $v_sale    = $variation_obj->get_sale_price();
        $v_save    = '';

        if ($variation_obj->is_on_sale() && $v_regular && $v_sale && (float) $v_regular > 0) {
            $v_percent = round((((float) $v_regular - (float) $v_sale) / (float) $v_regular) * 100);
            $v_save = $v_percent > 0 ? 'Save ' . $v_percent . '%' : '';
        }

        $variation_options[] = array(
            'variation_id'         => $variation['variation_id'],
            'label'                => $variation_label,
            'price_html'           => $variation_obj->get_price_html(),
            'image'                => !empty($variation['image']['src']) ? $variation['image']['src'] : $product_image_url,
            'stock_text'           => $variation_stock_text,
            'stock_class'          => $variation_stock_class,
            'attributes'           => $variation['attributes'],
            'purchasable'          => $variation_obj->is_purchasable() && ($variation_obj->is_in_stock() || $variation_backorders_allowed),
            'regular_price_html'   => $v_regular ? wc_price($v_regular) : '',
            'save_percent'         => $v_save,
            'is_on_sale'           => $variation_obj->is_on_sale(),
            'managing_stock'       => $variation_manage_stock,
            'stock_quantity'       => $variation_manage_stock ? (int) $variation_obj->get_stock_quantity() : 0,
            'max_qty'              => $variation_backorders_allowed ? '' : $variation_max_qty,
            'backorders_allowed'   => $variation_backorders_allowed,
        );
    }
}

$coa_page = get_page_by_path('coa');
$coa_url  = $coa_page ? get_permalink($coa_page->ID) : '';
?>

<main class="product-main">
  <section class="product-shell">
    <div class="container">
      <div class="product-breadcrumbs-wrap">
        <div class="product-breadcrumbs">
          <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
          <span>/</span>
          <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Products</a>
          <span>/</span>
          <strong><?php echo esc_html($product_name); ?></strong>
        </div>
      </div>

      <div class="product-layout">
        <div class="product-gallery-card">
          <?php if ($is_on_sale) : ?>
            <div class="product-badge">Sale</div>
          <?php endif; ?>

          <div class="product-gallery-main">
            <img id="productMainImage" src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product_name); ?>" />
          </div>
        </div>

        <div class="product-info-card">
          <h1 id="productName"><?php echo esc_html($product_name); ?></h1>

          <div class="product-price-stack">
            <div class="product-price-row">
              <span class="product-price-current" id="productPrice"><?php echo wp_kses_post($product_price); ?></span>
            </div>

            <div class="product-compare-row" id="productCompareRow"<?php echo ($compare_html || $save_percent) ? '' : ' style="display:none;"'; ?>>
              <span class="product-compare-text">Compare at <span id="productComparePrice"><?php echo wp_kses_post($compare_html); ?></span></span>
              <span class="product-save-pill" id="productSavePill"><?php echo esc_html($save_percent); ?></span>
            </div>
          </div>

          <?php if (!empty($product_short)) : ?>
            <div class="product-short-description">
              <?php echo wp_kses_post(wpautop($product_short)); ?>
            </div>
          <?php endif; ?>

          <?php
          $benefits = get_template_directory() . '/product-page/icon-benefits.php';
          if (file_exists($benefits)) {
              include $benefits;
          }
          ?>

          <p id="productStock" class="product-stock-text <?php echo esc_attr($stock_class); ?>">
            <?php echo esc_html($stock_text); ?>
          </p>

          <div class="product-purchase-box" id="productPurchaseBox">
            <?php if ($is_variable && !empty($variation_options)) : ?>
              <form class="product-form ajax-product-form" id="ajaxProductForm" data-product-type="variable">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                <input type="hidden" name="variation_id" id="variation_id" value="">

                <?php foreach ($product->get_variation_attributes() as $attribute_name => $options) :
                    $field_name = 'attribute_' . sanitize_title($attribute_name); ?>
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
                          data-label="<?php echo esc_attr($option['label']); ?>"
                          data-price-html="<?php echo esc_attr($option['price_html']); ?>"
                          data-image="<?php echo esc_url($option['image']); ?>"
                          data-stock-text="<?php echo esc_attr($option['stock_text']); ?>"
                          data-stock-class="<?php echo esc_attr($option['stock_class']); ?>"
                          data-purchasable="<?php echo $option['purchasable'] ? '1' : '0'; ?>"
                          data-attributes="<?php echo esc_attr(wp_json_encode($option['attributes'])); ?>"
                          data-regular-price-html="<?php echo esc_attr($option['regular_price_html']); ?>"
                          data-save-percent="<?php echo esc_attr($option['save_percent']); ?>"
                          data-is-on-sale="<?php echo $option['is_on_sale'] ? '1' : '0'; ?>"
                          data-managing-stock="<?php echo $option['managing_stock'] ? '1' : '0'; ?>"
                          data-stock-quantity="<?php echo esc_attr($option['stock_quantity']); ?>"
                          data-max-qty="<?php echo esc_attr($option['max_qty']); ?>"
                          data-backorders-allowed="<?php echo $option['backorders_allowed'] ? '1' : '0'; ?>"
                        >
                          <?php echo esc_html($option['label']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <span class="product-select-icon"><i class="fa-solid fa-chevron-down"></i></span>
                  </div>
                </div>

                <div class="product-option-group">
                  <label class="product-option-label" for="productQty">Quantity</label>
                  <div class="product-qty-wrap">
                    <button type="button" class="product-qty-btn" id="qtyMinus">−</button>
                    <input
                      id="productQty"
                      class="product-qty-input"
                      type="number"
                      name="quantity"
                      value="1"
                      min="1"
                      step="1"
                      inputmode="numeric"
                      autocomplete="off"
                    >
                    <button type="button" class="product-qty-btn" id="qtyPlus">+</button>
                  </div>
                  <p class="product-qty-note" id="productQtyNote" style="display:none;"></p>
                </div>

                <button id="productAddToCart" class="product-add-to-cart-btn" type="submit" disabled>
                  Select Variant
                </button>
              </form>
            <?php else : ?>
              <form class="product-form ajax-product-form" id="ajaxProductForm" data-product-type="simple">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">

                <div class="product-option-group">
                  <label class="product-option-label" for="productQty">Quantity</label>
                  <div class="product-qty-wrap">
                    <button type="button" class="product-qty-btn" id="qtyMinus">−</button>
                    <input
                      id="productQty"
                      class="product-qty-input"
                      type="number"
                      name="quantity"
                      value="1"
                      min="1"
                      step="1"
                      inputmode="numeric"
                      autocomplete="off"
                      <?php echo (!$simple_backorders_allowed && $simple_max_qty !== '') ? 'max="' . esc_attr($simple_max_qty) . '"' : ''; ?>
                    >
                    <button type="button" class="product-qty-btn" id="qtyPlus">+</button>
                  </div>
                  <p class="product-qty-note" id="productQtyNote" style="display:none;"></p>
                </div>

                <button id="productAddToCart" class="product-add-to-cart-btn" type="submit" <?php disabled(!$product->is_purchasable()); ?>>
                  <?php echo $product->is_purchasable() ? 'Add To Cart' : 'Unavailable'; ?>
                </button>
              </form>
            <?php endif; ?>

            <?php if (!empty($coa_url)) : ?>
              <div class="product-coa-button-wrap">
                <a class="product-coa-button" href="<?php echo esc_url($coa_url); ?>">
                  View COA
                </a>
              </div>
            <?php endif; ?>

            <div class="product-payment-icons">
              <span class="payment-icon-pill" aria-label="Visa"><i class="fa-brands fa-cc-visa"></i></span>
              <span class="payment-icon-pill" aria-label="Mastercard"><i class="fa-brands fa-cc-mastercard"></i></span>
              <span class="payment-icon-pill" aria-label="American Express"><i class="fa-brands fa-cc-amex"></i></span>
              <span class="payment-icon-pill" aria-label="Discover"><i class="fa-brands fa-cc-discover"></i></span>
              <span class="payment-icon-pill payment-icon-image-pill" aria-label="Venmo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/venmo.jpg'); ?>" alt="Venmo">
              </span>
              <span class="payment-icon-pill payment-icon-image-pill" aria-label="Zelle">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/zelle.jpg'); ?>" alt="Zelle">
              </span>
              <span class="payment-icon-pill" aria-label="Crypto"><i class="fa-brands fa-bitcoin"></i></span>
            </div>

            <div class="product-afterpay-box">
              <div class="product-afterpay-row">
                <i class="fa-solid fa-truck-fast"></i>
                <span>Same day shipping on orders before 2pm PST</span>
              </div>

              <div class="product-afterpay-row">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Research use only</span>
              </div>

              <div class="product-trust-stack">
                <div class="product-trust-card product-trust-card-green">
                  <div class="product-trust-card-icon">🛡️</div>
                  <div class="product-trust-card-copy">
                    <strong>30-Day Money-Back Guarantee</strong>
                    <span>Not satisfied? Contact us and we will make it right.</span>
                  </div>
                </div>

                <div class="product-trust-card product-trust-card-blue">
                  <div class="product-trust-card-icon">📦</div>
                  <div class="product-trust-card-copy">
                    <strong>Shipment Protection Included</strong>
                    <span>Lost or damaged in transit? We will help resolve it quickly.</span>
                  </div>
                </div>
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

        <div class="product-disclaimer-box product-disclaimer-below-description">
          <div class="product-disclaimer-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          <div class="product-disclaimer-copy">
            <strong>Research Use Only</strong>
            <p>This product is intended strictly for laboratory, analytical, and in-vitro research use only. Not for human or veterinary consumption. By purchasing, you confirm that the material will be used only in a controlled research setting.</p>
          </div>
        </div>
      </section>

      <?php
      $why_choose = get_template_directory() . '/product-page/why-choose-us.php';
      if (file_exists($why_choose)) {
          include $why_choose;
      }
      ?>
    </div>
  </section>
</main>

<div class="sticky-product-bar" id="stickyProductBar" aria-hidden="true">
  <div class="sticky-product-bar-inner">
    <div class="sticky-product-thumb">
      <img id="stickyProductImage" src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product_name); ?>">
    </div>

    <div class="sticky-product-bar-copy">
      <strong><?php echo esc_html($product_name); ?></strong>
      <span id="stickyProductVariant"><?php echo $is_variable ? 'Select variant' : 'Ready to add'; ?></span>
      <span id="stickyProductPrice"><?php echo wp_kses_post($product_price); ?></span>
    </div>

    <div class="sticky-product-bar-actions">
      <div class="sticky-product-bar-qty">
        <button type="button" id="stickyQtyMinus">−</button>
        <span id="stickyQtyValue">1</span>
        <button type="button" id="stickyQtyPlus">+</button>
      </div>

      <button type="button" class="sticky-product-bar-btn" id="stickyAddToCartBtn">
        Add To Cart
      </button>
    </div>
  </div>
</div>

<script>
window.AXIOM_PRODUCT_PAGE = <?php echo wp_json_encode(array(
    'isVariable'             => $is_variable,
    'productId'              => $product_id,
    'variations'             => $variation_options,
    'simpleProduct'          => array(
        'managingStock'      => $simple_manage_stock,
        'stockQuantity'      => $simple_manage_stock ? (int) $product->get_stock_quantity() : 0,
        'maxQty'             => $simple_backorders_allowed ? '' : $simple_max_qty,
        'backordersAllowed'  => $simple_backorders_allowed,
    ),
)); ?>;
</script>
