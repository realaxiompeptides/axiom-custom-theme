<?php
/**
 * Axiom SEO Breadcrumbs
 *
 * Adds clean breadcrumb navigation for products, categories, pages, and posts.
 * Safe for WooCommerce and research-use-only positioning.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get breadcrumb items.
 */
function axiom_get_breadcrumb_items() {
    $items = array();

    $items[] = array(
        'label' => 'Home',
        'url'   => home_url('/'),
    );

    if (function_exists('is_shop') && is_shop()) {
        $items[] = array(
            'label' => 'Shop',
            'url'   => '',
        );

        return $items;
    }

    if (function_exists('is_product') && is_product()) {
        $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

        $items[] = array(
            'label' => 'Shop',
            'url'   => $shop_url,
        );

        $terms = get_the_terms(get_the_ID(), 'product_cat');

        if (!empty($terms) && !is_wp_error($terms)) {
            $primary_term = null;

            foreach ($terms as $term) {
                if (!$primary_term || $term->parent > 0) {
                    $primary_term = $term;
                }
            }

            if ($primary_term) {
                $term_link = get_term_link($primary_term);

                if (!is_wp_error($term_link)) {
                    $items[] = array(
                        'label' => $primary_term->name,
                        'url'   => $term_link,
                    );
                }
            }
        }

        $items[] = array(
            'label' => get_the_title(),
            'url'   => '',
        );

        return $items;
    }

    if (function_exists('is_product_category') && is_product_category()) {
        $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

        $items[] = array(
            'label' => 'Shop',
            'url'   => $shop_url,
        );

        $term = get_queried_object();

        if ($term && !is_wp_error($term)) {
            if (!empty($term->parent)) {
                $ancestors = array_reverse(get_ancestors($term->term_id, 'product_cat'));

                foreach ($ancestors as $ancestor_id) {
                    $ancestor = get_term($ancestor_id, 'product_cat');

                    if ($ancestor && !is_wp_error($ancestor)) {
                        $ancestor_link = get_term_link($ancestor);

                        if (!is_wp_error($ancestor_link)) {
                            $items[] = array(
                                'label' => $ancestor->name,
                                'url'   => $ancestor_link,
                            );
                        }
                    }
                }
            }

            $items[] = array(
                'label' => $term->name,
                'url'   => '',
            );
        }

        return $items;
    }

    if (is_page()) {
        global $post;

        if ($post && $post->post_parent) {
            $parents = array_reverse(get_post_ancestors($post->ID));

            foreach ($parents as $parent_id) {
                $items[] = array(
                    'label' => get_the_title($parent_id),
                    'url'   => get_permalink($parent_id),
                );
            }
        }

        $items[] = array(
            'label' => get_the_title(),
            'url'   => '',
        );

        return $items;
    }

    if (is_single()) {
        $categories = get_the_category();

        if (!empty($categories)) {
            $category = $categories[0];

            $items[] = array(
                'label' => $category->name,
                'url'   => get_category_link($category->term_id),
            );
        }

        $items[] = array(
            'label' => get_the_title(),
            'url'   => '',
        );

        return $items;
    }

    if (is_category()) {
        $category = get_queried_object();

        if ($category && !is_wp_error($category)) {
            $items[] = array(
                'label' => $category->name,
                'url'   => '',
            );
        }

        return $items;
    }

    if (is_search()) {
        $items[] = array(
            'label' => 'Search Results',
            'url'   => '',
        );

        return $items;
    }

    if (is_404()) {
        $items[] = array(
            'label' => 'Page Not Found',
            'url'   => '',
        );

        return $items;
    }

    return $items;
}

/**
 * Render breadcrumbs.
 */
function axiom_render_breadcrumbs() {
    if (is_front_page()) {
        return;
    }

    $items = axiom_get_breadcrumb_items();

    if (empty($items)) {
        return;
    }

    echo '<nav class="axiom-breadcrumbs" aria-label="Breadcrumb">';
    echo '<ol>';

    $count = count($items);

    foreach ($items as $index => $item) {
        $is_last = ($index + 1) === $count;

        echo '<li>';

        if (!$is_last && !empty($item['url'])) {
            echo '<a href="' . esc_url($item['url']) . '">' . esc_html($item['label']) . '</a>';
        } else {
            echo '<span aria-current="page">' . esc_html($item['label']) . '</span>';
        }

        echo '</li>';
    }

    echo '</ol>';
    echo '</nav>';
}

/**
 * Auto-display breadcrumbs on WooCommerce pages.
 */
add_action('woocommerce_before_main_content', 'axiom_render_breadcrumbs', 5);

/**
 * Shortcode if you want to place manually:
 * [axiom_breadcrumbs]
 */
add_shortcode('axiom_breadcrumbs', function () {
    ob_start();
    axiom_render_breadcrumbs();
    return ob_get_clean();
});
