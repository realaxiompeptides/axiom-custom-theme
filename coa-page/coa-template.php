<?php
/*
Template Name: COA Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();

/*
 * Make sure the COA map file is available even if functions.php load order is wrong.
 */
$coa_map_file = get_template_directory() . '/functions/coa/coa-map.php';
if (file_exists($coa_map_file)) {
    require_once $coa_map_file;
}

/**
 * Helpers local to this template.
 */
if (!function_exists('axiom_coa_template_normalize_text')) {
    function axiom_coa_template_normalize_text($text) {
        $text = wp_strip_all_tags((string) $text);
        $text = strtolower($text);
        $text = str_replace('&', ' and ', $text);
        $text = str_replace('+', ' plus ', $text);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim($text, '-');

        return $text;
    }
}

if (!function_exists('axiom_coa_template_get_all_attachments')) {
    function axiom_coa_template_get_all_attachments() {
        $attachments = get_posts(array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => array('image/png', 'image/jpeg', 'image/jpg', 'application/pdf'),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        $results = array();

        foreach ($attachments as $attachment) {
            $title = axiom_coa_template_normalize_text($attachment->post_title);
            $file  = get_attached_file($attachment->ID);
            $base  = $file ? axiom_coa_template_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';

            if (strpos($title, 'coa') !== false || strpos($base, 'coa') !== false) {
                $results[] = $attachment;
            }
        }

        return $results;
    }
}

if (!function_exists('axiom_coa_template_get_mapped_attachments')) {
    function axiom_coa_template_get_mapped_attachments($product_name) {
        if (!function_exists('axiom_coa_file_map')) {
            return array();
        }

        $map = axiom_coa_file_map();

        if (empty($map[$product_name]) || !is_array($map[$product_name])) {
            return array();
        }

        $wanted_keys = array_map('axiom_coa_template_normalize_text', $map[$product_name]);
        $attachments = axiom_coa_template_get_all_attachments();
        $matches = array();

        foreach ($attachments as $attachment) {
            $title = axiom_coa_template_normalize_text(get_the_title($attachment->ID));
            $file  = get_attached_file($attachment->ID);
            $base  = $file ? axiom_coa_template_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';

            foreach ($wanted_keys as $wanted) {
                if (
                    $wanted &&
                    (
                        $title === $wanted ||
                        $base === $wanted ||
                        strpos($title, $wanted) !== false ||
                        strpos($base, $wanted) !== false
                    )
                ) {
                    $matches[] = $attachment->ID;
                    break;
                }
            }
        }

        return array_values(array_unique($matches));
    }
}

if (!function_exists('axiom_coa_template_get_variant_label')) {
    function axiom_coa_template_get_variant_label($attachment_id, $product_name = '') {
        $file = get_attached_file($attachment_id);
        $base = $file ? axiom_coa_template_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';

        $base = str_replace('axiom-', '', $base);
        $base = str_replace('-coa', '', $base);

        if ($product_name) {
            $product_norm = axiom_coa_template_normalize_text($product_name);
            $base = str_replace($product_norm, '', $base);

            // Extra cleanup for NAD+
            if ($product_norm === 'nad-plus') {
                $base = str_replace('nad', '', $base);
            }
        }

        $base = trim($base, '- ');

        if (!$base) {
            return 'COA';
        }

        $label = strtoupper(str_replace('-', ' ', $base));

        /*
         * Only liquid products should keep ML in the label.
         * Everything else should remove ML amounts.
         */
        $liquid_products = array(
            'BAC WATER',
            'LEMON BOTTLE',
        );

        $product_name_upper = strtoupper(trim((string) $product_name));
        $keep_ml = in_array($product_name_upper, $liquid_products, true);

        if (!$keep_ml) {
            $label = preg_replace('/\b\d+\s*ML\b/i', '', $label);
            $label = preg_replace('/\s+/', ' ', trim($label));
        }

        return $label ?: 'COA';
    }
}

$products = function_exists('wc_get_products') ? wc_get_products(array(
    'status'  => 'publish',
    'limit'   => -1,
    'return'  => 'objects',
    'orderby' => 'menu_order',
    'order'   => 'ASC',
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

          $product_matches = axiom_coa_template_get_mapped_attachments($product_name);
          $product_status  = !empty($product_matches) ? 'ready' : 'not_ready';
          $product_label   = 'Janoshik Tested';
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

            <div class="axiom-coa-variant-list">
              <?php if (!empty($product_matches)) : ?>
                <?php foreach ($product_matches as $attachment_id) : ?>
                  <?php
                  $file_url    = wp_get_attachment_url($attachment_id);
                  $mime_type   = get_post_mime_type($attachment_id);
                  $is_image    = strpos((string) $mime_type, 'image/') === 0;
                  $label       = axiom_coa_template_get_variant_label($attachment_id, $product_name);
                  $modal_title = $product_name . ($label && $label !== 'COA' ? ' — ' . $label : '');
                  ?>
                  <div class="axiom-coa-variant-row" data-search="<?php echo esc_attr(strtolower($product_name . ' ' . $label)); ?>">
                    <div class="axiom-coa-variant-copy">
                      <strong><?php echo esc_html($label ?: 'VARIANT'); ?></strong>
                      <span class="axiom-coa-status axiom-coa-status-ready">COA READY</span>
                    </div>

                    <div class="axiom-coa-variant-actions">
                      <?php if ($is_image && !empty($file_url)) : ?>
                        <button
                          type="button"
                          class="axiom-coa-btn axiom-coa-btn-small axiom-coa-open-modal"
                          data-coa-title="<?php echo esc_attr($modal_title); ?>"
                          data-coa-image="<?php echo esc_url($file_url); ?>"
                        >
                          View COA
                        </button>
                      <?php elseif (!empty($file_url)) : ?>
                        <a
                          class="axiom-coa-btn axiom-coa-btn-small"
                          href="<?php echo esc_url($file_url); ?>"
                          target="_blank"
                          rel="noopener noreferrer"
                        >
                          View COA
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else : ?>
                <div class="axiom-coa-variant-row">
                  <div class="axiom-coa-variant-copy">
                    <strong>VARIANT</strong>
                    <span class="axiom-coa-status axiom-coa-status-not-ready">COA NOT READY</span>
                    <span class="axiom-coa-empty">No file yet</span>
                  </div>
                </div>
              <?php endif; ?>
            </div>
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
