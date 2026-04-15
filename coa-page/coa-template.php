<?php
/*
Template Name: COA Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$products = function_exists('wc_get_products') ? wc_get_products(array(
    'status' => 'publish',
    'limit'  => -1,
    'return' => 'objects',
)) : array();

$coa_css_path = get_template_directory() . '/assets/css/coa/coa.css';
if (file_exists($coa_css_path)) {
    echo '<style id="axiom-coa-inline-styles">';
    readfile($coa_css_path);
    echo '</style>';
}
?>

<main class="axiom-coa-page">
  <div class="container">
    <section class="axiom-coa-hero">
      <p class="axiom-coa-kicker">Certificates of Analysis</p>
      <h1>Janoshik Tested COAs</h1>
      <p class="axiom-coa-subtitle">
        Browse product and variant-specific certificates of analysis. COAs are listed by product and variant when available.
      </p>
    </section>

    <section class="axiom-coa-toolbar">
      <div class="axiom-coa-search-wrap">
        <input type="text" id="axiomCoaSearch" placeholder="Search by product or variant...">
      </div>
    </section>

    <section class="axiom-coa-grid" id="axiomCoaGrid">
      <?php if (!empty($products)) : ?>
        <?php foreach ($products as $product) : ?>
          <?php
          if (!$product || !is_a($product, 'WC_Product')) {
              continue;
          }

          $product_name = $product->get_name();

          $product_image_html = $product->get_image(
              'woocommerce_thumbnail',
              array(
                  'class'   => 'axiom-coa-product-image',
                  'loading' => 'lazy',
                  'alt'     => $product_name,
              )
          );

          if (empty($product_image_html)) {
              $product_image_html = '<img class="axiom-coa-product-image" src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr($product_name) . '">';
          }

          $product_coa    = function_exists('axiom_get_product_coa_data') ? axiom_get_product_coa_data($product) : array();
          $product_status = !empty($product_coa['status']) ? $product_coa['status'] : 'not_ready';
          $product_label  = !empty($product_coa['label']) ? $product_coa['label'] : 'Janoshik Tested';
          $product_image  = !empty($product_coa['image']) ? $product_coa['image'] : '';
          $product_pdf    = !empty($product_coa['pdf']) ? $product_coa['pdf'] : '';
          ?>
          <article class="axiom-coa-card" data-search="<?php echo esc_attr(strtolower($product_name)); ?>">
            <div class="axiom-coa-card-head">
              <div class="axiom-coa-product-media">
                <?php echo $product_image_html; ?>
              </div>

              <div class="axiom-coa-product-meta">
                <p class="axiom-coa-tested-badge"><?php echo esc_html($product_label); ?></p>
                <h2><?php echo esc_html($product_name); ?></h2>

                <div class="axiom-coa-status-row">
                  <?php if ($product_status === 'ready') : ?>
                    <span class="axiom-coa-status axiom-coa-status-ready">COA READY</span>
                  <?php else : ?>
                    <span class="axiom-coa-status axiom-coa-status-not-ready">COA NOT READY</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <?php if (!empty($product_image) || !empty($product_pdf)) : ?>
              <div class="axiom-coa-actions">
                <?php if (!empty($product_image)) : ?>
                  <button
                    type="button"
                    class="axiom-coa-btn axiom-coa-open-modal"
                    data-coa-title="<?php echo esc_attr($product_name . ' COA'); ?>"
                    data-coa-image="<?php echo esc_url($product_image); ?>"
                  >
                    View COA
                  </button>
                <?php endif; ?>

                <?php if (!empty($product_image)) : ?>
                  <a class="axiom-coa-btn axiom-coa-btn-secondary" href="<?php echo esc_url($product_image); ?>" target="_blank" rel="noopener noreferrer">Open Image</a>
                <?php endif; ?>

                <?php if (!empty($product_pdf)) : ?>
                  <a class="axiom-coa-btn axiom-coa-btn-secondary" href="<?php echo esc_url($product_pdf); ?>" target="_blank" rel="noopener noreferrer">Open PDF</a>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if ($product->is_type('variable')) : ?>
              <?php
              $variation_ids = $product->get_children();
              if (!empty($variation_ids)) :
              ?>
                <div class="axiom-coa-variant-list">
                  <?php foreach ($variation_ids as $variation_id) : ?>
                    <?php
                    $variation = wc_get_product($variation_id);
                    if (!$variation || !is_a($variation, 'WC_Product_Variation')) {
                        continue;
                    }

                    $variation_coa    = function_exists('axiom_get_variation_coa_data') ? axiom_get_variation_coa_data($variation_id) : array();
                    $variation_status = !empty($variation_coa['status']) ? $variation_coa['status'] : 'not_ready';
                    $variation_label  = function_exists('axiom_get_variation_display_label') ? axiom_get_variation_display_label($variation) : 'Variant';
                    $variation_image  = !empty($variation_coa['image']) ? $variation_coa['image'] : '';
                    $variation_pdf    = !empty($variation_coa['pdf']) ? $variation_coa['pdf'] : '';
                    ?>
                    <div class="axiom-coa-variant-row" data-search="<?php echo esc_attr(strtolower($product_name . ' ' . $variation_label)); ?>">
                      <div class="axiom-coa-variant-copy">
                        <strong><?php echo esc_html(strtoupper($variation_label ?: 'VARIANT')); ?></strong>

                        <?php if ($variation_status === 'ready') : ?>
                          <span class="axiom-coa-status axiom-coa-status-ready">COA READY</span>
                        <?php else : ?>
                          <span class="axiom-coa-status axiom-coa-status-not-ready">COA NOT READY</span>
                        <?php endif; ?>

                        <?php if (empty($variation_image) && empty($variation_pdf)) : ?>
                          <span class="axiom-coa-empty">No file yet</span>
                        <?php endif; ?>
                      </div>

                      <div class="axiom-coa-variant-actions">
                        <?php if (!empty($variation_image)) : ?>
                          <button
                            type="button"
                            class="axiom-coa-btn axiom-coa-btn-small axiom-coa-open-modal"
                            data-coa-title="<?php echo esc_attr($product_name . ' - ' . $variation_label); ?>"
                            data-coa-image="<?php echo esc_url($variation_image); ?>"
                          >
                            View COA
                          </button>
                        <?php endif; ?>

                        <?php if (!empty($variation_image)) : ?>
                          <a class="axiom-coa-btn axiom-coa-btn-secondary axiom-coa-btn-small" href="<?php echo esc_url($variation_image); ?>" target="_blank" rel="noopener noreferrer">Open Image</a>
                        <?php endif; ?>

                        <?php if (!empty($variation_pdf)) : ?>
                          <a class="axiom-coa-btn axiom-coa-btn-secondary axiom-coa-btn-small" href="<?php echo esc_url($variation_pdf); ?>" target="_blank" rel="noopener noreferrer">Open PDF</a>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      <?php else : ?>
        <div class="axiom-coa-card">
          <h2>No products found</h2>
          <p>No published products were found yet.</p>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<div class="axiom-coa-modal" id="axiomCoaModal" aria-hidden="true">
  <div class="axiom-coa-modal-backdrop" data-close-coa-modal></div>
  <div class="axiom-coa-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="axiomCoaModalTitle">
    <button type="button" class="axiom-coa-modal-close" data-close-coa-modal aria-label="Close COA preview">×</button>
    <h3 id="axiomCoaModalTitle">COA Preview</h3>
    <div class="axiom-coa-modal-body">
      <img id="axiomCoaModalImage" src="" alt="COA Preview">
    </div>
  </div>
</div>

<?php
$coa_js_path = get_template_directory() . '/assets/js/coa/coa.js';
if (file_exists($coa_js_path)) {
    echo '<script id="axiom-coa-inline-script">';
    readfile($coa_js_path);
    echo '</script>';
}
?>

<?php get_footer(); ?>
