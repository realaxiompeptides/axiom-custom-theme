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
 * Build a set of possible product matching keys.
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

    // Additional tolerant variations.
    $keys[] = str_replace('nad-', 'nad-plus-', axiom_coa_normalize_text($product_slug));
    $keys[] = str_replace('nad-', 'nad-plus-', axiom_coa_normalize_text($product_name));
    $keys[] = str_replace('plus', '', axiom_coa_normalize_text($product_name));
    $keys[] = str_replace('plus', '', axiom_coa_normalize_text($product_slug));

    // Remove empty / duplicates.
    $keys = array_filter(array_unique(array_map('trim', $keys)));

    return array_values($keys);
}

/**
 * Build searchable strings from attachment.
 */
function axiom_coa_get_attachment_match_string($attachment_id) {
    $title = get_the_title($attachment_id);
    $file  = get_attached_file($attachment_id);
    $base  = $file ? pathinfo($file, PATHINFO_FILENAME) : '';

    $parts = array(
        axiom_coa_normalize_text($title),
        axiom_coa_normalize_text($base),
    );

    $full = implode(' ', array_filter($parts));
    $full = str_replace('axiom-', '', $full);
    $full = str_replace('-coa', '', $full);
    $full = preg_replace('/\s+/', ' ', trim($full));

    return $full;
}

/**
 * Score attachment against a product.
 */
function axiom_coa_score_attachment_for_product($attachment_id, $product) {
    $keys = axiom_coa_get_product_match_keys($product);
    if (empty($keys)) {
        return 0;
    }

    $match_string = axiom_coa_get_attachment_match_string($attachment_id);
    $title        = axiom_coa_normalize_text(get_the_title($attachment_id));

    $score = 0;

    foreach ($keys as $key) {
        if (!$key) {
            continue;
        }

        if ($match_string === $key) {
            $score += 100;
        }

        if (strpos($match_string, $key) !== false) {
            $score += 50;
        }

        if (strpos($title, $key) !== false) {
            $score += 25;
        }
    }

    // Must still look like a COA asset.
    if (strpos($title, 'coa') !== false || strpos($match_string, 'coa') !== false || strpos($match_string, 'janoshik') !== false) {
        $score += 10;
    }

    return $score;
}

/**
 * Get all uploaded COA attachments from media library.
 */
function axiom_coa_get_all_coa_attachments() {
    $attachments = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => array('image/png', 'image/jpeg', 'image/jpg', 'application/pdf'),
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        's'              => 'coa',
    ));

    $filtered = array();

    foreach ($attachments as $attachment) {
        $title = axiom_coa_normalize_text($attachment->post_title);
        $file  = get_attached_file($attachment->ID);
        $base  = $file ? axiom_coa_normalize_text(pathinfo($file, PATHINFO_FILENAME)) : '';

        if (
            strpos($title, 'coa') !== false ||
            strpos($base, 'coa') !== false ||
            strpos($base, 'janoshik') !== false
        ) {
            $filtered[] = $attachment;
        }
    }

    return $filtered;
}

/**
 * Get matching COAs for a product.
 */
function axiom_coa_get_matching_attachments_for_product($product) {
    if (!$product || !is_a($product, 'WC_Product')) {
        return array();
    }

    $attachments = axiom_coa_get_all_coa_attachments();
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
 * Render one COA card.
 */
function axiom_coa_render_attachment_card($attachment_id) {
    $title      = get_the_title($attachment_id);
    $mime_type  = get_post_mime_type($attachment_id);
    $file_url   = wp_get_attachment_url($attachment_id);
    $image_html = wp_get_attachment_image($attachment_id, 'large', false, array(
        'class' => 'axiom-coa-card-image',
        'loading' => 'lazy',
    ));

    ob_start();
    ?>
    <article class="axiom-coa-card">
      <a href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener" class="axiom-coa-card-media">
        <?php if (strpos((string) $mime_type, 'image/') === 0) : ?>
          <?php echo $image_html; ?>
        <?php else : ?>
          <div class="axiom-coa-pdf-placeholder">PDF</div>
        <?php endif; ?>
      </a>

      <div class="axiom-coa-card-body">
        <h3><?php echo esc_html($title); ?></h3>
        <a href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener" class="axiom-coa-view-btn">
          View COA
        </a>
      </div>
    </article>
    <?php
    return ob_get_clean();
}

/**
 * Shortcode: [axiom_coa_page]
 */
function axiom_coa_page_shortcode() {
    if (!function_exists('wc_get_products')) {
        return '<p>WooCommerce is required for the COA page.</p>';
    }

    $selected_product = null;

    if (!empty($_GET['product_id'])) {
        $selected_product = wc_get_product(absint($_GET['product_id']));
    } elseif (!empty($_GET['product'])) {
        $product_slug = sanitize_title(wp_unslash($_GET['product']));
        $product_post = get_page_by_path($product_slug, OBJECT, 'product');

        if ($product_post) {
            $selected_product = wc_get_product($product_post->ID);
        }
    }

    $all_products = wc_get_products(array(
        'status' => 'publish',
        'limit'  => -1,
        'orderby'=> 'menu_order',
        'order'  => 'ASC',
    ));

    ob_start();
    ?>
    <div class="axiom-coa-page">
      <div class="axiom-coa-header">
        <p class="axiom-coa-kicker">Batch Documentation</p>
        <h1>Certificates of Analysis</h1>
        <p>Browse product COAs and open the corresponding certificate for each research compound.</p>
      </div>

      <form class="axiom-coa-filter" method="get">
        <label for="axiom_coa_product">Select Product</label>
        <div class="axiom-coa-filter-row">
          <select id="axiom_coa_product" name="product">
            <option value="">Choose a product</option>
            <?php foreach ($all_products as $product) :
                $slug = get_post_field('post_name', $product->get_id());
                ?>
                <option value="<?php echo esc_attr($slug); ?>" <?php selected($selected_product && $selected_product->get_id() === $product->get_id()); ?>>
                  <?php echo esc_html($product->get_name()); ?>
                </option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Load COA</button>
        </div>
      </form>

      <?php if ($selected_product) : ?>
        <?php
        $matches = axiom_coa_get_matching_attachments_for_product($selected_product);
        ?>
        <div class="axiom-coa-results-header">
          <h2><?php echo esc_html($selected_product->get_name()); ?></h2>
          <p><?php echo count($matches); ?> matching COA<?php echo count($matches) === 1 ? '' : 's'; ?> found.</p>
        </div>

        <?php if (!empty($matches)) : ?>
          <div class="axiom-coa-grid">
            <?php
            foreach ($matches as $attachment_id) {
                echo axiom_coa_render_attachment_card($attachment_id);
            }
            ?>
          </div>
        <?php else : ?>
          <div class="axiom-coa-empty">
            <p>No COA found for this product yet.</p>
          </div>
        <?php endif; ?>
      <?php else : ?>
        <div class="axiom-coa-empty">
          <p>Select a product above to load its COA.</p>
        </div>
      <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('axiom_coa_page', 'axiom_coa_page_shortcode');

/**
 * Enqueue COA page CSS.
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
            '1.0.0'
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
