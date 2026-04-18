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
    $text = str_replace('&', ' and ', $text);
    $text = str_replace('+', ' plus ', $text);
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
 * Get all image/pdf attachments.
 */
function axiom_coa_get_all_attachments() {
    return get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => array('image/png', 'image/jpeg', 'image/jpg', 'application/pdf'),
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
}

/**
 * Find best map config for the current product name using loose matching.
 */
function axiom_coa_get_map_config_for_product_name($product_name) {
    if (!function_exists('axiom_coa_file_map')) {
        return null;
    }

    $map = axiom_coa_file_map();
    if (empty($map) || !is_array($map)) {
        return null;
    }

    $normalized_product_name = axiom_coa_normalize_text($product_name);

    foreach ($map as $map_key => $config) {
        if (axiom_coa_normalize_text($map_key) === $normalized_product_name) {
            return is_array($config) ? $config : null;
        }
    }

    foreach ($map as $map_key => $config) {
        if (empty($config['product_aliases']) || !is_array($config['product_aliases'])) {
            continue;
        }

        foreach ($config['product_aliases'] as $alias) {
            $alias_normalized = axiom_coa_normalize_text($alias);

            if (
                $alias_normalized &&
                (
                    strpos($normalized_product_name, $alias_normalized) !== false ||
                    strpos($alias_normalized, $normalized_product_name) !== false
                )
            ) {
                return is_array($config) ? $config : null;
            }
        }
    }

    foreach ($map as $map_key => $config) {
        $normalized_map_key = axiom_coa_normalize_text($map_key);

        if (
            $normalized_map_key &&
            (
                strpos($normalized_product_name, $normalized_map_key) !== false ||
                strpos($normalized_map_key, $normalized_product_name) !== false
            )
        ) {
            return is_array($config) ? $config : null;
        }
    }

    return null;
}

/**
 * Match attachments using loose product + variant aliases from coa-map.php
 */
function axiom_coa_get_matching_attachments_for_product($product) {
    if (
        !$product ||
        !is_a($product, 'WC_Product') ||
        !function_exists('axiom_coa_file_map')
    ) {
        return array();
    }

    $product_name = trim($product->get_name());
    $config = axiom_coa_get_map_config_for_product_name($product_name);

    if (empty($config) || !is_array($config)) {
        return array();
    }

    $product_aliases = !empty($config['product_aliases'])
        ? array_map('axiom_coa_normalize_text', (array) $config['product_aliases'])
        : array(axiom_coa_normalize_text($product_name));

    $variant_aliases = !empty($config['variant_aliases'])
        ? array_map('axiom_coa_normalize_text', (array) $config['variant_aliases'])
        : array();

    $product_aliases[] = axiom_coa_normalize_text($product_name);
    $product_aliases = array_values(array_unique(array_filter($product_aliases)));

    $attachments = axiom_coa_get_all_attachments();
    $matches = array();

    foreach ($attachments as $attachment) {
        $title = axiom_coa_normalize_text(get_the_title($attachment->ID));
        $file  = get_attached_file($attachment->ID);
        $base  = $file ? axiom_coa_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';
        $haystack = trim($title . ' ' . $base);

        if (!$haystack) {
            continue;
        }

        $product_hit = false;
        foreach ($product_aliases as $alias) {
            if ($alias && strpos($haystack, $alias) !== false) {
                $product_hit = true;
                break;
            }
        }

        if (!$product_hit) {
            continue;
        }

        if (!empty($variant_aliases)) {
            $variant_hit = false;

            foreach ($variant_aliases as $variant) {
                if ($variant && strpos($haystack, $variant) !== false) {
                    $variant_hit = true;
                    break;
                }
            }

            if ($variant_hit || !$variant_hit) {
                $matches[] = $attachment->ID;
            }
        } else {
            $matches[] = $attachment->ID;
        }
    }

    return array_values(array_unique($matches));
}

/**
 * Build a nice variant label from the filename.
 */
function axiom_coa_get_attachment_variant_label($attachment_id) {
    $file = get_attached_file($attachment_id);
    $base = $file ? axiom_coa_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';

    $base = str_replace('axiom-', '', $base);
    $base = str_replace('-coa', '', $base);
    $base = str_replace('-cow', '', $base);
    $base = trim($base, '- ');

    if (!$base) {
        return 'COA FILE';
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
          <h1>Janoshik Tested COAs</h1>
          <p class="axiom-coa-subtitle">
            Browse product and variant-specific certificates of analysis. COAs are listed by product and variant when available.
          </p>
        </div>

        <div class="axiom-coa-toolbar">
          <div class="axiom-coa-search-wrap">
            <input type="text" id="axiomCoaSearch" placeholder="Search products..." />
          </div>
        </div>

        <div class="axiom-coa-grid" id="axiomCoaGrid">
          <?php foreach ($all_products as $product) :
              if (!$product || !is_a($product, 'WC_Product')) {
                  continue;
              }

              $matches = axiom_coa_get_matching_attachments_for_product($product);
              $has_coa = !empty($matches);
              ?>
              <article class="axiom-coa-card" data-product-name="<?php echo esc_attr(strtolower($product->get_name())); ?>">
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
                        $label    = axiom_coa_get_attachment_variant_label($attachment_id);
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
                        <strong>VARIANT</strong>
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('axiomCoaSearch');
      const cards = document.querySelectorAll('.axiom-coa-card');

      if (!searchInput || !cards.length) return;

      searchInput.addEventListener('input', function () {
        const query = (searchInput.value || '').toLowerCase().trim();

        cards.forEach((card) => {
          const name = (card.getAttribute('data-product-name') || '').toLowerCase();
          card.style.display = !query || name.includes(query) ? '' : 'none';
        });
      });
    });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('axiom_coa_page', 'axiom_coa_page_shortcode');

/**
 * Enqueue COA CSS.
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
            '1.0.4'
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

    $url = get_permalink($coa_page->ID);

    echo '<div class="axiom-product-coa-link-wrap">';
    echo '<a class="axiom-product-coa-link" href="' . esc_url($url) . '">View COA</a>';
    echo '</div>';
}
add_action('woocommerce_single_product_summary', 'axiom_render_product_coa_link', 31);
