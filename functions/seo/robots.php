<?php
/**
 * Axiom Robots / Crawl Control
 *
 * Helps keep thin/private utility pages out of search results.
 * Does not block important products, categories, pages, posts, or COA content.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Determine if current page should be noindex.
 */
function axiom_should_noindex_page() {
    if (is_admin()) {
        return false;
    }

    if (is_404() || is_search()) {
        return true;
    }

    if (is_author() || is_date()) {
        return true;
    }

    if (is_attachment()) {
        return true;
    }

    if (function_exists('is_cart') && is_cart()) {
        return true;
    }

    if (function_exists('is_checkout') && is_checkout()) {
        return true;
    }

    if (function_exists('is_account_page') && is_account_page()) {
        return true;
    }

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
        return true;
    }

    if (!empty($_GET['add-to-cart'])) {
        return true;
    }

    if (!empty($_GET['orderby'])) {
        return true;
    }

    if (!empty($_GET['min_price']) || !empty($_GET['max_price'])) {
        return true;
    }

    foreach ($_GET as $key => $value) {
        if (strpos((string) $key, 'filter_') === 0) {
            return true;
        }
    }

    return false;
}

/**
 * Output robots meta.
 */
function axiom_output_robots_meta() {
    if (axiom_should_noindex_page()) {
        echo '<meta name="robots" content="noindex,follow">' . "\n";
        return;
    }

    echo '<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">' . "\n";
}

add_action('wp_head', 'axiom_output_robots_meta', 2);

/**
 * Add helpful robots.txt rules.
 */
function axiom_custom_robots_txt($output, $public) {
    $site_url = home_url('/');

    $extra = "\n";
    $extra .= "# Axiom Research crawl guidance\n";
    $extra .= "User-agent: *\n";
    $extra .= "Disallow: /wp-admin/\n";
    $extra .= "Allow: /wp-admin/admin-ajax.php\n";
    $extra .= "Disallow: /cart/\n";
    $extra .= "Disallow: /checkout/\n";
    $extra .= "Disallow: /my-account/\n";
    $extra .= "Disallow: /*?add-to-cart=\n";
    $extra .= "Disallow: /*?orderby=\n";
    $extra .= "Disallow: /*?min_price=\n";
    $extra .= "Disallow: /*?max_price=\n";
    $extra .= "\n";
    $extra .= "Sitemap: " . esc_url($site_url . 'wp-sitemap.xml') . "\n";

    if (function_exists('wc_get_page_permalink')) {
        $extra .= "Sitemap: " . esc_url($site_url . 'product-sitemap.xml') . "\n";
    }

    return trim($output . "\n" . $extra);
}

add_filter('robots_txt', 'axiom_custom_robots_txt', 20, 2);

/**
 * Remove WordPress generator tag for cleaner source.
 */
remove_action('wp_head', 'wp_generator');

/**
 * Remove shortlink output.
 */
remove_action('wp_head', 'wp_shortlink_wp_head');

/**
 * Remove Really Simple Discovery link.
 */
remove_action('wp_head', 'rsd_link');

/**
 * Remove Windows Live Writer manifest.
 */
remove_action('wp_head', 'wlwmanifest_link');
