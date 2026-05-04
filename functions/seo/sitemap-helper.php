<?php
/**
 * Axiom HTML Sitemap + SEO Asset Loader
 *
 * Adds a human-readable HTML sitemap shortcode and loads SEO block CSS.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue SEO block styles.
 */
function axiom_enqueue_seo_block_styles() {
    $css_path = get_template_directory() . '/assets/css/seo/seo-blocks.css';
    $css_uri  = get_template_directory_uri() . '/assets/css/seo/seo-blocks.css';

    if (file_exists($css_path)) {
        wp_enqueue_style(
            'axiom-seo-blocks',
            $css_uri,
            array(),
            filemtime($css_path)
        );
    }
}

add_action('wp_enqueue_scripts', 'axiom_enqueue_seo_block_styles', 30);

/**
 * Get important pages by slug.
 */
function axiom_get_important_seo_pages() {
    $slugs = array(
        'research-peptides',
        'coa-page',
        'coa-tested-research-peptides',
        'third-party-tested-peptides',
        'usa-fulfilled-research-peptides',
        'peptide-storage-guide',
        'what-does-research-use-only-mean',
        'research-use-disclaimer',
        'about-axiom-research',
        'axiom-research',
        'axiom-peptides',
        'real-axiom-peptides',
        'axiom-labs',
        'axiom-biotech',
        'affiliate-program',
        'research-creator-program',
        'shipping-policy',
        'refund-policy',
        'contact',
    );

    $pages = array();

    foreach ($slugs as $slug) {
        $page = get_page_by_path($slug);

        if ($page && $page->post_status === 'publish') {
            $pages[] = $page;
        }
    }

    return $pages;
}

/**
 * Render list of pages.
 */
function axiom_render_sitemap_page_list($pages) {
    if (empty($pages)) {
        return;
    }

    echo '<ul class="axiom-sitemap-list">';

    foreach ($pages as $page) {
        echo '<li><a href="' . esc_url(get_permalink($page->ID)) . '">' . esc_html(get_the_title($page->ID)) . '</a></li>';
    }

    echo '</ul>';
}

/**
 * Render product categories.
 */
function axiom_render_sitemap_product_categories() {
    if (!taxonomy_exists('product_cat')) {
        return;
    }

    $terms = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));

    if (empty($terms) || is_wp_error($terms)) {
        return;
    }

    echo '<ul class="axiom-sitemap-list">';

    foreach ($terms as $term) {
        if ($term->slug === 'uncategorized') {
            continue;
        }

        $link = get_term_link($term);

        if (is_wp_error($link)) {
            continue;
        }

        echo '<li><a href="' . esc_url($link) . '">' . esc_html($term->name) . '</a></li>';
    }

    echo '</ul>';
}

/**
 * Render products.
 */
function axiom_render_sitemap_products($limit = 100) {
    if (!post_type_exists('product')) {
        return;
    }

    $products = get_posts(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => absint($limit),
        'orderby'        => 'title',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ));

    if (empty($products)) {
        return;
    }

    echo '<ul class="axiom-sitemap-list axiom-sitemap-products">';

    foreach ($products as $product_id) {
        echo '<li><a href="' . esc_url(get_permalink($product_id)) . '">' . esc_html(get_the_title($product_id)) . '</a></li>';
    }

    echo '</ul>';
}

/**
 * Render recent posts.
 */
function axiom_render_sitemap_posts($limit = 30) {
    $posts = get_posts(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => absint($limit),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ));

    if (empty($posts)) {
        return;
    }

    echo '<ul class="axiom-sitemap-list">';

    foreach ($posts as $post_id) {
        echo '<li><a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a></li>';
    }

    echo '</ul>';
}

/**
 * Shortcode:
 * [axiom_html_sitemap]
 */
function axiom_html_sitemap_shortcode() {
    ob_start();

    echo '<section class="axiom-seo-block axiom-html-sitemap">';
    echo '<div class="axiom-seo-block-inner">';
    echo '<p class="axiom-seo-eyebrow">Axiom Research Sitemap</p>';
    echo '<h2>Explore Axiom Research</h2>';
    echo '<p class="axiom-seo-intro">Use this sitemap to find Axiom Research pages, product categories, research-use-only resources, and published products.</p>';

    echo '<div class="axiom-sitemap-grid">';

    echo '<div class="axiom-sitemap-column">';
    echo '<h3>Important Pages</h3>';
    axiom_render_sitemap_page_list(axiom_get_important_seo_pages());
    echo '</div>';

    echo '<div class="axiom-sitemap-column">';
    echo '<h3>Product Categories</h3>';
    axiom_render_sitemap_product_categories();
    echo '</div>';

    echo '<div class="axiom-sitemap-column">';
    echo '<h3>Products</h3>';
    axiom_render_sitemap_products(100);
    echo '</div>';

    echo '<div class="axiom-sitemap-column">';
    echo '<h3>Research Articles</h3>';
    axiom_render_sitemap_posts(30);
    echo '</div>';

    echo '</div>';
    echo '</div>';
    echo '</section>';

    return ob_get_clean();
}

add_shortcode('axiom_html_sitemap', 'axiom_html_sitemap_shortcode');

/**
 * Add sitemap link in document head.
 */
function axiom_output_sitemap_link_tag() {
    echo '<link rel="sitemap" type="application/xml" title="XML Sitemap" href="' . esc_url(home_url('/wp-sitemap.xml')) . '">' . "\n";
}

add_action('wp_head', 'axiom_output_sitemap_link_tag', 4);
