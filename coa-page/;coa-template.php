<?php
/*
Template Name: COA Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$args = array(
    'status' => 'publish',
    'limit'  => -1,
    'return' => 'objects',
);

$products = wc_get_products($args);
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
      <?php foreach ($products as $product) : ?>
        <?php
        $product_id    = $product->get_id();
        $product_name  = $product->get_name();
        $product_image = $product->get_image_id()
            ? wp_get_attachment_image_url($product->get_image_id(), 'medium')
            : wc_placeholder_img_src();

        $product_coa = axiom_get_product_coa_data($product);
        $product_status = !empty($product_coa['status']) ? $product_coa['status'] : 'not_ready';
        $product_label  = !empty($product_coa['label']) ? $product_coa['label'] : 'Janoshik Tested';
        ?>

        <article class="axiom-coa-card" data-search="<?php echo esc_attr(strtolower($product_name)); ?>">
          <div class="axiom-coa-card-head">
            <div class="axiom-coa-product-media">
              <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_name); ?>">
            </div>

            <div class="axiom-coa-product-meta">
              <p class="axiom-coa-tested-badge"><?php echo esc_html($product_label); ?></p>
              <h2><?php echo esc_html($product_name); ?></h2>
              <div class="axiom-coa-status-row">
                <?php if ($product_status === 'ready') : ?>
                  <span class="axiom-coa-status axiom-coa-status-ready">COA Ready</span>
                <?php else : ?>
                  <span class="axiom-coa-status axiom-coa-status-not-ready">COA Not Ready</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php if (!empty($product_coa['image']) || !empty($product_coa['pdf'])) : ?>
            <div class="axiom-coa-actions">
              <?php if (!empty($product_coa['image'])) : ?>
                <a class="axiom-coa-btn" href="<?php echo esc_url($product_coa['image']); ?>" target="_blank" rel="noopener noreferrer">View Product COA</a>
              <?php endif; ?>

              <?php if (!empty($product_coa['pdf'])) : ?>
                <a class="axiom-coa-btn axiom-coa-btn-secondary" href="<?php echo esc_url($product_coa['pdf']); ?>" target="_blank" rel="noopener noreferrer">Download PDF</a>
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
                  if (!$variation) {
                      continue;
                  }

                  $variation_coa = axiom_get_variation_coa_data($variation_id);
                  $variation_status = !empty($variation_coa['status']) ? $variation_coa['status'] : 'not_ready';
                  $variation_label = axiom_get_variation_display_label($variation);
                  ?>
                  <div class="axiom-coa-variant-row" data-search="<?php echo esc_attr(strtolower($product_name . ' ' . $variation_label)); ?>">
                    <div class="axiom-coa-variant-copy">
                      <strong><?php echo esc_html($variation_label ?: 'Variant'); ?></strong>
                      <?php if ($variation_status === 'ready') : ?>
                        <span class="axiom-coa-status axiom-coa-status-ready">COA Ready</span>
                      <?php else : ?>
                        <span class="axiom-coa-status axiom-coa-status-not-ready">COA Not Ready</span>
                      <?php endif; ?>
                    </div>

                    <div class="axiom-coa-variant-actions">
                      <?php if (!empty($variation_coa['image'])) : ?>
                        <a class="axiom-coa-btn axiom-coa-btn-small" href="<?php echo esc_url($variation_coa['image']); ?>" target="_blank" rel="noopener noreferrer">View</a>
                      <?php endif; ?>

                      <?php if (!empty($variation_coa['pdf'])) : ?>
                        <a class="axiom-coa-btn axiom-coa-btn-secondary axiom-coa-btn-small" href="<?php echo esc_url($variation_coa['pdf']); ?>" target="_blank" rel="noopener noreferrer">PDF</a>
                      <?php endif; ?>

                      <?php if (empty($variation_coa['image']) && empty($variation_coa['pdf'])) : ?>
                        <span class="axiom-coa-empty">No file yet</span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </section>
  </div>
</main>

<?php get_footer(); ?>
