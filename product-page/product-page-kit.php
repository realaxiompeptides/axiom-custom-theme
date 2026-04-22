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

$kit_vial_count = (int) get_post_meta($product_id, '_axiom_kit_vial_count', true);
if ($kit_vial_count < 1) {
    $kit_vial_count = 10;
}

$single_product_id = (int) get_post_meta($product_id, '_axiom_kit_single_product_id', true);
$single_product    = $single_product_id ? wc_get_product($single_product_id) : null;

$kit_price_value   = (float) $product->get_price();
$per_vial_price    = $kit_vial_count > 0 ? ($kit_price_value / $kit_vial_count) : 0;

$single_product_name = '';
$single_unit_price   = 0;
$single_total_price  = 0;
$kit_savings         = 0;
$kit_savings_percent = 0;

if ($single_product instanceof WC_Product) {
    $single_product_name = $single_product->get_name();
    $single_unit_price   = (float) $single_product->get_price();
    $single_total_price  = $single_unit_price * $kit_vial_count;

    if ($single_total_price > $kit_price_value) {
        $kit_savings = $single_total_price - $kit_price_value;
        $kit_savings_percent = round(($kit_savings / $single_total_price) * 100);
    }
}

$kit_microcopy = get_post_meta($product_id, '_axiom_kit_microcopy', true);
if (!$kit_microcopy) {
    $kit_microcopy = 'Built for bulk research ordering with lower per-vial pricing, streamlined checkout, and the same quality standards across the full bundle.';
}

$competitor_fields = array(
    'neuro'     => 'Neuro Labs',
    'onyx'      => 'Onyx Research',
    'core'      => 'Core Peptides',
    'limitless' => 'Limitless Biotech',
);

$competitor_rows = array();

foreach ($competitor_fields as $key => $label) {
    $competitor_price = (float) get_post_meta($product_id, '_axiom_competitor_' . $key . '_price', true);

    if ($competitor_price > 0) {
        $difference = $competitor_price - $kit_price_value;

        $competitor_rows[] = array(
            'name'       => $label,
            'price'      => $competitor_price,
            'difference' => $difference,
        );
    }
}

get_header();
?>

<main class="product-main product-main-kit">
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
          <p class="kit-page-eyebrow">Kit Bundle</p>
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

          <div class="kit-value-strip">
            <div class="kit-value-card">
              <span class="kit-value-label">Vials per kit</span>
              <strong><?php echo esc_html($kit_vial_count); ?></strong>
            </div>

            <div class="kit-value-card">
              <span class="kit-value-label">Per-vial cost</span>
              <strong><?php echo wp_kses_post(wc_price($per_vial_price)); ?></strong>
            </div>

            <div class="kit-value-card">
              <span class="kit-value-label">Kit savings</span>
              <strong><?php echo $kit_savings > 0 ? wp_kses_post(wc_price($kit_savings)) : 'Bulk value'; ?></strong>
            </div>
          </div>

          <div class="kit-intro-copy">
            <p><?php echo esc_html($kit_microcopy); ?></p>
          </div>

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
          </div>
        </div>
      </div>

      <section class="kit-pricing-explainer">
        <div class="product-description-header">
          <p class="product-section-kicker">Why Buy The Kit</p>
          <h2>Better bundle pricing built for repeat ordering.</h2>
        </div>

        <div class="kit-pricing-explainer__grid">
          <div class="kit-pricing-explainer__card">
            <h3><?php echo esc_html($kit_vial_count); ?> vials in one order</h3>
            <p>Built for labs and repeat buyers who want one bundle instead of piecing together singles.</p>
          </div>

          <div class="kit-pricing-explainer__card">
            <h3><?php echo wp_kses_post(wc_price($per_vial_price)); ?> per vial</h3>
            <p>Lower average per-vial pricing compared with buying the full quantity individually.</p>
          </div>

          <div class="kit-pricing-explainer__card">
            <h3>
              <?php if ($kit_savings > 0) : ?>
                Save <?php echo wp_kses_post(wc_price($kit_savings)); ?>
              <?php else : ?>
                Bulk-value pricing
              <?php endif; ?>
            </h3>
            <p>
              <?php if ($kit_savings > 0) : ?>
                Save <?php echo esc_html($kit_savings_percent); ?>% versus buying <?php echo esc_html($kit_vial_count); ?> singles separately.
              <?php else : ?>
                Bundle pricing designed to make larger orders more efficient.
              <?php endif; ?>
            </p>
          </div>
        </div>
      </section>

      <?php if ($single_total_price > 0 || !empty($competitor_rows)) : ?>
        <section class="kit-comparison-card">
          <div class="product-description-header">
            <p class="product-section-kicker">Value Comparison</p>
            <h2>How this kit stacks up</h2>
          </div>

          <div class="kit-comparison-table-wrap">
            <table class="kit-comparison-table">
              <thead>
                <tr>
                  <th>Option</th>
                  <th>Price</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Axiom Kit</strong></td>
                  <td><?php echo wp_kses_post($product_price); ?></td>
                  <td>Current bundle price</td>
                </tr>

                <?php if ($single_total_price > 0) : ?>
                  <tr>
                    <td><?php echo esc_html($kit_vial_count); ?> singles<?php echo $single_product_name ? ' (' . esc_html($single_product_name) . ')' : ''; ?></td>
                    <td><?php echo wp_kses_post(wc_price($single_total_price)); ?></td>
                    <td>
                      <?php if ($kit_savings > 0) : ?>
                        Costs <?php echo wp_kses_post(wc_price($kit_savings)); ?> more than the kit
                      <?php else : ?>
                        Manual bundle comparison
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php foreach ($competitor_rows as $row) : ?>
                  <tr>
                    <td><?php echo esc_html($row['name']); ?></td>
                    <td><?php echo wp_kses_post(wc_price($row['price'])); ?></td>
                    <td>
                      <?php if ($row['difference'] > 0) : ?>
                        Save <?php echo wp_kses_post(wc_price($row['difference'])); ?> with Axiom
                      <?php else : ?>
                        Verify exact match manually
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <section class="product-description-card">
        <div class="product-description-header">
          <p class="product-section-kicker">Kit Details</p>
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
    </div>
  </section>
</main>

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

<?php get_footer(); ?>
