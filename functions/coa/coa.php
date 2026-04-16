<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalize text for matching.
 */
function axiom_coa_normalize_text($text) {
    $text = wp_strip_all_tags((string) $text);
    $text = strtolower($text);
    $text = str_replace(array('&', '+'), ' plus ', $text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');

    return $text;
}

/**
 * Get product image HTML.
 */
function axiom_coa_get_product_image_html($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return '';
    }

    $image_id = $product->get_image_id();

    if ($image_id) {
        return wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, array(
            'class'   => 'axiom-coa-product-image',
            'loading' => 'lazy',
            'alt'     => $product->get_name(),
        ));
    }

    return wc_placeholder_img('woocommerce_thumbnail');
}

/**
 * Get all possible product matching keys.
 */
function axiom_coa_get_product_match_keys($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return array();
    }

    $keys = array();

    $product_name = $product->get_name();
    $product_slug = get_post_field('post_name', $product->get_id());

    $keys[] = axiom_coa_normalize_text($product_name);
    $keys[] = axiom_coa_normalize_text($product_slug);

    // Common variants of naming.
    $keys[] = str_replace('plus', '', axiom_coa_normalize_text($product_name));
    $keys[] = str_replace('plus', '', axiom_coa_normalize_text($product_slug));

    $keys[] = str_replace('nad-', 'nad-plus-', axiom_coa_normalize_text($product_name));
    $keys[] = str_replace('nad-', 'nad-plus-', axiom_coa_normalize_text($product_slug));

    $keys[] = str_replace('-with-dac', '', axiom_coa_normalize_text($product_name));
    $keys[] = str_replace('-with-dac', '', axiom_coa_normalize_text($product_slug));

    $keys[] = str_replace('-no-dac', '', axiom_coa_normalize_text($product_name));
    $keys[] = str_replace('-no-dac', '', axiom_coa_normalize_text($product_slug));

    $keys = array_filter(array_unique(array_map('trim', $keys)));

    return array_values($keys);
}

/**
 * Get clean searchable string from attachment.
 */
function axiom_coa_get_attachment_search_string($attachment_id) {
    $title = get_the_title($attachment_id);
    $file  = get_attached_file($attachment_id);
    $base  = $file ? pathinfo($file, PATHINFO_FILENAME) : '';

    $title_norm = axiom_coa_normalize_text($title);
    $base_norm  = axiom_coa_normalize_text($base);

    $search = trim($title_norm . ' ' . $base_norm);

    $search = str_replace('axiom-', '', $search);
    $search = str_replace('-coa', '', $search);
    $search = str_replace(' coa', '', $search);
    $search = preg_replace('/\s+/', ' ', trim($search));

    return $search;
}

/**
 * Score attachment for product.
 */
function axiom_coa_score_attachment_for_product($attachment_id, $product) {
    $keys = axiom_coa_get_product_match_keys($product);
    if (empty($keys)) {
        return 0;
    }

    $title       = axiom_coa_normalize_text(get_the_title($attachment_id));
    $file        = get_attached_file($attachment_id);
    $base        = $file ? axiom_coa_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';
    $search_full = axiom_coa_get_attachment_search_string($attachment_id);

    $score = 0;

    foreach ($keys as $key) {
        if (!$key) {
            continue;
        }

        if ($title === $key || $base === $key || $search_full === $key) {
            $score += 100;
        }

        if (strpos($title, $key) !== false) {
            $score += 40;
        }

        if (strpos($base, $key) !== false) {
            $score += 60;
        }

        if (strpos($search_full, $key) !== false) {
            $score += 50;
        }

        // Also try the reverse: sometimes shorter product key is inside longer filename.
        $short_key = str_replace(array('-plus', 'plus-'), '', $key);
        if ($short_key && strpos($base, $short_key) !== false) {
            $score += 20;
        }
    }

    // Strong boost if it really looks like a COA file.
    if (strpos($title, 'coa') !== false || strpos($base, 'coa') !== false) {
        $score += 25;
    }

    return $score;
}

/**
 * Get all uploaded COA attachments.
 */
function axiom_coa_get_all_attachments() {
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
        $title = axiom_coa_normalize_text($attachment->post_title);
        $file  = get_attached_file($attachment->ID);
        $base  = $file ? axiom_coa_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';

        if (
            strpos($title, 'coa') !== false ||
            strpos($base, 'coa') !== false
        ) {
            $results[] = $attachment;
        }
    }

    return $results;
}

/**
 * Get all matching attachments for one product.
 */
function axiom_coa_get_matching_attachments_for_product($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return array();
    }

    $attachments = axiom_coa_get_all_attachments();
    $matches     = array();

    foreach ($attachments as $attachment) {
        $score = axiom_coa_score_attachment_for_product($attachment->ID, $product);

        if ($score > 0) {
            $matches[$attachment->ID] = $score;
        }
    }

    arsort($matches);

    return array_keys($matches);
}

/**
 * Try to make a label from filename.
 */
function axiom_coa_get_attachment_variant_label($attachment_id, $product) {
    $file = get_attached_file($attachment_id);
    $base = $file ? axiom_coa_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';
    $keys = axiom_coa_get_product_match_keys($product);

    foreach ($keys as $key) {
        $base = str_replace($key, '', $base);
    }

    $base = str_replace('axiom-', '', $base);
    $base = str_replace('-coa', '', $base);
    $base = trim($base, '- ');

    if (!$base) {
        return 'Variant';
    }

    return strtoupper(str_replace('-', ' ', $base));
}

/**
 * Shortcode: [axiom_coa_page]
 */
function axiom_coa_page_shortcode() {
    if (!function_exists('wc_get_products')) {
        return '<p>WooCommerce is required for the COA page.</p>';
    }

    $all_products = wc_get_products(array(
        'status'  => 'publish',
        'limit'   => -1,
        'orderby' => 'menu_order',
        'order'   => 'ASC',
    ));

    ob_start();
    ?>
    <div class="axiom-coa-page">
      <div class="container">
        <div class="axiom-coa-hero">
          <p class="axiom-coa-kicker">Batch Documentation</p>
          <h1>Certificates of Analysis</h1>
          <p class="axiom-coa-subtitle">
            Browse product COAs and open the corresponding certificate for each research compound.
          </p>
        </div>

        <div class="axiom-coa-grid">
          <?php foreach ($all_products as $product) :
              if (!$product || !is_a($product, 'WC_Product')) {
                  continue;
              }

              $matches = axiom_coa_get_matching_attachments_for_product($product);
              $has_coa = !empty($matches);
              ?>
              <article class="axiom-coa-card">
                <div class="axiom-coa-card-head">
                  <div class="axiom-coa-product-media">
                    <?php echo axiom_coa_get_product_image_html($product); ?>
                  </div>

                  <div class="axiom-coa-product-meta">
                    <span class="axiom-coa-tested-badge">Janoshik Tested</span>
                    <h2><?php echo esc_html($product->get_name()); ?></h2>

                    <div class="axiom-coa-status-row">
                      <?php if ($has_coa) : ?>
                        <span class="axiom-coa-status axiom-coa-status-ready">COA Ready</span>
                      <?php else : ?>
                        <span class="axiom-coa-status axiom-coa-status-not-ready">COA Not Ready</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <div class="axiom-coa-variant-list">
                  <?php if ($has_coa) : ?>
                    <?php foreach ($matches as $attachment_id) :
                        $file_url = wp_get_attachment_url($attachment_id);
                        $label    = axiom_coa_get_attachment_variant_label($attachment_id, $product);
                        ?>
                        <div class="axiom-coa-variant-row">
                          <div class="axiom-coa-variant-copy">
                            <strong><?php echo esc_html($label); ?></strong>
                          </div>

                          <div class="axiom-coa-variant-actions">
                            <a class="axiom-coa-btn axiom-coa-btn-small" href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener">
                              View COA
                            </a>
                          </div>
                        </div>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <div class="axiom-coa-variant-row">
                      <div class="axiom-coa-variant-copy">
                        <strong>Variant</strong>
                        <span class="axiom-coa-status axiom-coa-status-not-ready">COA Not Ready</span>
                        <div class="axiom-coa-empty">No file yet</div>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('axiom_coa_page', 'axiom_coa_page_shortcode');

/**
 * Enqueue COA CSS only where shortcode exists.
 */
function axiom_enqueue_coa_assets() {
    if (!is_page()) {
        return;
    }

    global $post;

    if (!$post) {
        return;
    }

    if (has_shortcode($post->post_content, 'axiom_coa_page')) {
        wp_enqueue_style(
            'axiom-coa-page',
            get_template_directory_uri() . '/assets/css/coa-page.css',
            array(),
            '1.0.1'
        );
    }
}
add_action('wp_enqueue_scripts', 'axiom_enqueue_coa_assets');

/**
 * Product page COA link.
 */
function axiom_render_product_coa_link() {
    if (!is_product()) {
        return;
    }

    global $product;

    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    $coa_page = get_page_by_path('coa');

    if (!$coa_page) {
        return;
    }

    $url = add_query_arg(
        array(
            'product' => get_post_field('post_name', $product->get_id()),
        ),
        get_permalink($coa_page->ID)
    );

    echo '<div class="axiom-product-coa-link-wrap">';
    echo '<a class="axiom-product-coa-link" href="' . esc_url($url) . '">View COA</a>';
    echo '</div>';
}
add_action('woocommerce_single_product_summary', 'axiom_render_product_coa_link', 31);
