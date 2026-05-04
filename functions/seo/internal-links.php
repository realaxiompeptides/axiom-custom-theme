<?php
/**
 * Axiom Internal SEO Links
 *
 * Adds safe internal linking blocks for products, pages, posts, and categories.
 * Keeps language research-use-only and avoids medical/dosage claims.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper: return URL only if page exists by path.
 */
function axiom_seo_url($path, $fallback = '') {
    $path = trim($path, '/');
    $page = get_page_by_path($path);

    if ($page) {
        return get_permalink($page->ID);
    }

    return $fallback ? home_url($fallback) : home_url('/' . $path . '/');
}

/**
 * Core authority links.
 */
function axiom_get_authority_links() {
    return array(
        array(
            'label' => 'COA Library',
            'url'   => axiom_seo_url('coa-page', '/coa-page/'),
            'desc'  => 'View available batch documentation and third-party testing information.',
        ),
        array(
            'label' => 'Research Use Only Disclaimer',
            'url'   => axiom_seo_url('research-use-disclaimer', '/research-use-disclaimer/'),
            'desc'  => 'Learn how Axiom Research positions all compounds for laboratory research use only.',
        ),
        array(
            'label' => 'Third-Party Tested Research Peptides',
            'url'   => axiom_seo_url('third-party-tested-peptides', '/third-party-tested-peptides/'),
            'desc'  => 'Understand why COA documentation and batch testing matter for research compounds.',
        ),
        array(
            'label' => 'USA-Fulfilled Research Compounds',
            'url'   => axiom_seo_url('usa-fulfilled-research-peptides', '/usa-fulfilled-research-peptides/'),
            'desc'  => 'Learn about Axiom Research fulfillment and shipping standards.',
        ),
        array(
            'label' => 'Peptide Storage Guide',
            'url'   => axiom_seo_url('peptide-storage-guide', '/peptide-storage-guide/'),
            'desc'  => 'Review general storage and handling information for research-use-only compounds.',
        ),
        array(
            'label' => 'About Axiom Research',
            'url'   => axiom_seo_url('about-axiom-research', '/about-axiom-research/'),
            'desc'  => 'Axiom Research is the main brand behind Axiom Peptides, Real Axiom Peptides, Axiom Labs, and Axiom Biotech search variations.',
        ),
    );
}

/**
 * Brand alias links.
 */
function axiom_get_brand_alias_links() {
    return array(
        array(
            'label' => 'Axiom Research',
            'url'   => axiom_seo_url('axiom-research', '/axiom-research/'),
        ),
        array(
            'label' => 'Axiom Peptides',
            'url'   => axiom_seo_url('axiom-peptides', '/axiom-peptides/'),
        ),
        array(
            'label' => 'Real Axiom Peptides',
            'url'   => axiom_seo_url('real-axiom-peptides', '/real-axiom-peptides/'),
        ),
        array(
            'label' => 'Axiom Labs',
            'url'   => axiom_seo_url('axiom-labs', '/axiom-labs/'),
        ),
        array(
            'label' => 'Axiom Biotech',
            'url'   => axiom_seo_url('axiom-biotech', '/axiom-biotech/'),
        ),
    );
}

/**
 * Render authority resource block.
 */
function axiom_render_research_resources_block($context = 'default') {
    $links = axiom_get_authority_links();

    if (empty($links)) {
        return;
    }

    echo '<section class="axiom-seo-block axiom-research-resources" aria-label="Research resources">';
    echo '<div class="axiom-seo-block-inner">';
    echo '<p class="axiom-seo-eyebrow">Research Resources</p>';
    echo '<h2>Helpful Axiom Research Resources</h2>';
    echo '<p class="axiom-seo-intro">Explore COA documentation, research-use-only information, storage guidance, and Axiom Research brand resources.</p>';

    echo '<div class="axiom-seo-link-grid">';

    foreach ($links as $link) {
        echo '<a class="axiom-seo-link-card" href="' . esc_url($link['url']) . '">';
        echo '<span class="axiom-seo-link-title">' . esc_html($link['label']) . '</span>';
        echo '<span class="axiom-seo-link-desc">' . esc_html($link['desc']) . '</span>';
        echo '</a>';
    }

    echo '</div>';
    echo '</div>';
    echo '</section>';
}

/**
 * Render brand alias block.
 */
function axiom_render_brand_alias_block() {
    $links = axiom_get_brand_alias_links();

    echo '<section class="axiom-seo-block axiom-brand-aliases" aria-label="Axiom brand information">';
    echo '<div class="axiom-seo-block-inner">';
    echo '<p class="axiom-seo-eyebrow">Official Brand</p>';
    echo '<h2>Axiom Research Brand Information</h2>';
    echo '<p class="axiom-seo-intro">Axiom Research is the main brand for axiomresearch.shop. Customers may also search for Axiom Peptides, Real Axiom Peptides, Axiom Labs, or Axiom Biotech when looking for the official Axiom Research website.</p>';

    echo '<div class="axiom-brand-pills">';

    foreach ($links as $link) {
        echo '<a href="' . esc_url($link['url']) . '">' . esc_html($link['label']) . '</a>';
    }

    echo '</div>';
    echo '</div>';
    echo '</section>';
}

/**
 * Product page resource block.
 */
function axiom_output_product_internal_links() {
    if (!function_exists('is_product') || !is_product()) {
        return;
    }

    axiom_render_research_resources_block('product');
}

add_action('woocommerce_after_single_product_summary', 'axiom_output_product_internal_links', 18);

/**
 * Product category block.
 */
function axiom_output_category_internal_links() {
    if (!function_exists('is_product_category') || !is_product_category()) {
        return;
    }

    axiom_render_research_resources_block('category');
}

add_action('woocommerce_after_shop_loop', 'axiom_output_category_internal_links', 20);

/**
 * Add resources to posts/pages, but not checkout/cart/account.
 */
function axiom_append_internal_links_to_content($content) {
    if (is_admin() || !is_singular()) {
        return $content;
    }

    if (function_exists('is_product') && is_product()) {
        return $content;
    }

    if (function_exists('is_cart') && is_cart()) {
        return $content;
    }

    if (function_exists('is_checkout') && is_checkout()) {
        return $content;
    }

    if (function_exists('is_account_page') && is_account_page()) {
        return $content;
    }

    ob_start();

    axiom_render_research_resources_block('content');

    if (is_page(array(
        'about-axiom-research',
        'axiom-research',
        'axiom-peptides',
        'real-axiom-peptides',
        'axiom-labs',
        'axiom-biotech',
    ))) {
        axiom_render_brand_alias_block();
    }

    $block = ob_get_clean();

    return $content . $block;
}

add_filter('the_content', 'axiom_append_internal_links_to_content', 20);

/**
 * Shortcodes:
 * [axiom_research_resources]
 * [axiom_brand_aliases]
 */
add_shortcode('axiom_research_resources', function () {
    ob_start();
    axiom_render_research_resources_block('shortcode');
    return ob_get_clean();
});

add_shortcode('axiom_brand_aliases', function () {
    ob_start();
    axiom_render_brand_alias_block();
    return ob_get_clean();
});
