<?php
defined('ABSPATH') || exit;

get_header('shop');

$shop_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;
$shop_title   = $shop_page_id ? get_the_title($shop_page_id) : __('Shop', 'axiom');

$current_term   = get_queried_object();
$is_tax_archive = is_tax('product_cat') || is_tax('product_tag');

$page_title = $shop_title;
$page_desc  = 'Browse all research products in our catalog.';

if ($is_tax_archive && $current_term && !empty($current_term->name)) {
    $page_title = $current_term->name;

    if (!empty($current_term->description)) {
        $page_desc = wp_strip_all_tags($current_term->description);
    }
}

/**
 * Load all visible catalog products.
 */
$product_query_args = array(
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'posts_per_page'      => -1,
    'orderby'             => 'menu_order title',
    'order'               => 'ASC',
    'ignore_sticky_posts' => true,
);

$tax_query = array(
    'relation' => 'AND',
);

if (function_exists('wc_get_product_visibility_term_ids')) {
    $product_visibility_terms = wc_get_product_visibility_term_ids();

    $hidden_visibility_terms = array_filter(
        array(
            isset($product_visibility_terms['exclude-from-catalog'])
                ? (int) $product_visibility_terms['exclude-from-catalog']
                : 0,

            isset($product_visibility_terms['exclude-from-search'])
                ? (int) $product_visibility_terms['exclude-from-search']
                : 0,
        )
    );

    if (!empty($hidden_visibility_terms)) {
        $tax_query[] = array(
            'taxonomy' => 'product_visibility',
            'field'    => 'term_taxonomy_id',
            'terms'    => $hidden_visibility_terms,
            'operator' => 'NOT IN',
        );
    }
}

if ($is_tax_archive && $current_term && !empty($current_term->term_id)) {
    $taxonomy = is_tax('product_tag') ? 'product_tag' : 'product_cat';

    $tax_query[] = array(
        'taxonomy' => $taxonomy,
        'field'    => 'term_id',
        'terms'    => array((int) $current_term->term_id),
    );
}

$product_query_args['tax_query'] = $tax_query;

$products = new WP_Query($product_query_args);

/**
 * Custom featured product order.
 *
 * Products listed here appear first in this exact order.
 * Products not listed here appear underneath them.
 *
 * WordPress product slugs are normally lowercase, so Aqualyx is matched
 * using "aqualyx" even if WooCommerce displays it as "Aqualyx".
 */
$featured_product_slugs = array(
    'glp-3-rt',
    'hgh-191aa-2',
    'aqualyx',
    'ghk-cu',
    'igf-1-lr3',
    'retatrutide',
    'tesamorelin',
    'bpc-157',
    'tb-500',
    'mots-c',
    'nad',
    'cjc-1295-no-dac',
    'ipamorelin',
    'semax',
    'selank',
);

/**
 * Products that should appear near the bottom.
 */
$low_priority_product_slugs = array(
    '5-amino-1mq',
    '5-amino-1mq-5mg',
    '5-amino-1mq-20mg',
);

/**
 * Sort products into featured, regular, and low-priority groups.
 */
if (!empty($products->posts)) {
    $featured_rank = array_flip($featured_product_slugs);
    $bottom_rank   = array_flip($low_priority_product_slugs);

    $featured_products = array();
    $regular_products  = array();
    $bottom_products   = array();

    foreach ($products->posts as $product_post) {
        $product_slug = strtolower((string) $product_post->post_name);

        if (isset($featured_rank[$product_slug])) {
            $featured_products[] = $product_post;
        } elseif (isset($bottom_rank[$product_slug])) {
            $bottom_products[] = $product_post;
        } else {
            $regular_products[] = $product_post;
        }
    }

    usort(
        $featured_products,
        function ($product_a, $product_b) use ($featured_rank) {
            $slug_a = strtolower((string) $product_a->post_name);
            $slug_b = strtolower((string) $product_b->post_name);

            $rank_a = isset($featured_rank[$slug_a])
                ? $featured_rank[$slug_a]
                : PHP_INT_MAX;

            $rank_b = isset($featured_rank[$slug_b])
                ? $featured_rank[$slug_b]
                : PHP_INT_MAX;

            return $rank_a <=> $rank_b;
        }
    );

    usort(
        $bottom_products,
        function ($product_a, $product_b) use ($bottom_rank) {
            $slug_a = strtolower((string) $product_a->post_name);
            $slug_b = strtolower((string) $product_b->post_name);

            $rank_a = isset($bottom_rank[$slug_a])
                ? $bottom_rank[$slug_a]
                : PHP_INT_MAX;

            $rank_b = isset($bottom_rank[$slug_b])
                ? $bottom_rank[$slug_b]
                : PHP_INT_MAX;

            return $rank_a <=> $rank_b;
        }
    );

    $products->posts = array_merge(
        $featured_products,
        $regular_products,
        $bottom_products
    );

    $products->post_count = count($products->posts);
}

/**
 * Catalog category cards.
 */
$catalog_filter_groups = array(
    array(
        'label' => 'All Products',
        'slug'  => 'all',
        'icon'  => 'all',
    ),
    array(
        'label' => 'Peptides',
        'slug'  => 'peptides',
        'icon'  => 'peptides',
    ),
    array(
        'label' => 'HGH',
        'slug'  => 'hgh',
        'icon'  => 'hgh',
    ),
    array(
        'label' => 'Anabolics',
        'slug'  => 'anabolics',
        'icon'  => 'anabolics',
        'note'  => 'Coming Soon',
    ),
    array(
        'label' => 'Kits',
        'slug'  => 'kits',
        'icon'  => 'kits',
    ),
);

/**
 * Return the inline SVG for each category.
 */
function axiom_catalog_filter_icon($icon) {
    switch ($icon) {
        case 'peptides':
            return '
                <svg viewBox="0 0 64 64" aria-hidden="true">
                    <rect x="23" y="10" width="18" height="7" rx="2"></rect>
                    <rect x="20" y="17" width="24" height="37" rx="5"></rect>
                    <path d="M20 28h24"></path>
                    <path d="M27 37h10"></path>
                    <path d="M27 43h10"></path>
                </svg>
            ';

        case 'hgh':
            return '
                <svg viewBox="0 0 64 64" aria-hidden="true">
                    <path d="M32 5 54 18v28L32 59 10 46V18z"></path>
                    <path d="M23 20v24"></path>
                    <path d="M41 20v24"></path>
                    <path d="M23 32h18"></path>
                </svg>
            ';

        case 'anabolics':
            return '
                <svg viewBox="0 0 64 64" aria-hidden="true">
                    <path d="M18 26v12"></path>
                    <path d="M12 23v18"></path>
                    <path d="M46 26v12"></path>
                    <path d="M52 23v18"></path>
                    <path d="M18 32h28"></path>
                    <path d="M8 27v10"></path>
                    <path d="M56 27v10"></path>
                </svg>
            ';

        case 'kits':
            return '
                <svg viewBox="0 0 64 64" aria-hidden="true">
                    <path d="M10 20 32 9l22 11-22 11z"></path>
                    <path d="M10 20v26l22 10 22-10V20"></path>
                    <path d="M32 31v25"></path>
                    <path d="M22 25v12"></path>
                    <path d="M42 25v12"></path>
                </svg>
            ';

        case 'all':
        default:
            return '
                <svg viewBox="0 0 64 64" aria-hidden="true">
                    <rect x="9" y="9" width="18" height="18" rx="4"></rect>
                    <rect x="37" y="9" width="18" height="18" rx="4"></rect>
                    <rect x="9" y="37" width="18" height="18" rx="4"></rect>
                    <rect x="37" y="37" width="18" height="18" rx="4"></rect>
                </svg>
            ';
    }
}
?>

<style>
    .axiom-catalog-category-section {
        position: relative;
        overflow: hidden;
        padding: 22px 16px 26px;
        background:
            radial-gradient(
                circle at top right,
                rgba(96, 205, 255, 0.38),
                transparent 38%
            ),
            linear-gradient(
                135deg,
                #071b3c 0%,
                #0d4e91 52%,
                #168dcc 100%
            );
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    }

    .axiom-catalog-category-heading {
        width: min(1200px, 100%);
        margin: 0 auto 16px;
    }

    .axiom-catalog-category-kicker {
        margin: 0 0 5px;
        color: #85d9ff;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .axiom-catalog-category-heading h2 {
        margin: 0;
        color: #ffffff;
        font-size: clamp(24px, 5vw, 36px);
        font-weight: 900;
        line-height: 1.05;
    }

    .axiom-category-scroll-shell {
        position: relative;
        width: min(1200px, 100%);
        margin: 0 auto;
    }

    .axiom-catalog-filter-pills {
        display: grid;
        width: 100%;
        margin: 0 auto;
        grid-template-columns: repeat(5, minmax(135px, 1fr));
        gap: 12px;
        overflow-x: auto;
        padding: 2px 1px 8px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .axiom-catalog-filter-pills::-webkit-scrollbar {
        display: none;
    }

    .axiom-filter-pill {
        position: relative;
        display: flex;
        min-width: 135px;
        min-height: 152px;
        padding: 16px 10px 14px;
        overflow: hidden;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        appearance: none;
        border: 1px solid rgba(255, 255, 255, 0.23);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.1);
        box-shadow:
            0 14px 32px rgba(0, 18, 49, 0.22),
            inset 0 1px 0 rgba(255, 255, 255, 0.16);
        color: #ffffff;
        cursor: pointer;
        text-align: center;
        transition:
            transform 0.2s ease,
            background 0.2s ease,
            border-color 0.2s ease,
            box-shadow 0.2s ease;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .axiom-filter-pill:hover {
        transform: translateY(-3px);
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.17);
    }

    .axiom-filter-pill.is-active {
        border-color: #ffffff;
        background: #ffffff;
        box-shadow:
            0 18px 38px rgba(0, 17, 54, 0.28),
            0 0 0 4px rgba(83, 190, 255, 0.22);
        color: #0a3971;
    }

    .axiom-filter-pill-icon {
        display: grid;
        width: 72px;
        height: 72px;
        flex: 0 0 72px;
        place-items: center;
        border-radius: 22px;
        background: linear-gradient(145deg, #148ed1, #07509a);
        box-shadow:
            0 10px 24px rgba(0, 28, 73, 0.34),
            inset 0 1px 0 rgba(255, 255, 255, 0.24);
        color: #ffffff;
    }

    .axiom-filter-pill.is-active .axiom-filter-pill-icon {
        background: linear-gradient(145deg, #167fc0, #092f68);
    }

    .axiom-filter-pill-icon svg {
        width: 42px;
        height: 42px;
        overflow: visible;
        fill: none;
        stroke: currentColor;
        stroke-width: 4;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .axiom-filter-pill-label {
        display: block;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.15;
    }

    .axiom-filter-pill-note {
        display: inline-flex;
        min-height: 21px;
        margin-top: -4px;
        padding: 4px 8px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        color: #cceeff;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.08em;
        line-height: 1;
        text-transform: uppercase;
    }

    .axiom-filter-pill.is-active .axiom-filter-pill-note {
        background: #e2f4ff;
        color: #0b5b99;
    }

    .axiom-category-scroll-cue {
        display: none;
        margin-top: 5px;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.02em;
        opacity: 0.95;
    }

    .axiom-category-scroll-arrow {
        display: inline-block;
        font-size: 23px;
        line-height: 1;
        animation: axiom-scroll-arrow 1.2s ease-in-out infinite;
    }

    @keyframes axiom-scroll-arrow {
        0%,
        100% {
            transform: translateX(0);
            opacity: 0.65;
        }

        50% {
            transform: translateX(8px);
            opacity: 1;
        }
    }

    @media (max-width: 900px) {
        .axiom-catalog-filter-pills {
            display: flex;
            width: 100%;
            scroll-snap-type: x proximity;
            scroll-padding-right: 56px;
        }

        .axiom-filter-pill {
            width: 145px;
            min-width: 145px;
            min-height: 148px;
            scroll-snap-align: start;
        }

        .axiom-category-scroll-cue {
            display: flex;
        }

        .axiom-category-scroll-shell::after {
            content: "";
            position: absolute;
            z-index: 4;
            top: 0;
            right: -1px;
            width: 52px;
            height: calc(100% - 30px);
            pointer-events: none;
            background: linear-gradient(
                90deg,
                rgba(13, 78, 145, 0),
                rgba(13, 78, 145, 0.96)
            );
        }
    }

    @media (max-width: 520px) {
        .axiom-catalog-category-section {
            padding-right: 12px;
            padding-left: 12px;
        }

        .axiom-filter-pill {
            width: 132px;
            min-width: 132px;
            min-height: 140px;
            border-radius: 18px;
        }

        .axiom-filter-pill-icon {
            width: 64px;
            height: 64px;
            flex-basis: 64px;
            border-radius: 19px;
        }

        .axiom-filter-pill-icon svg {
            width: 38px;
            height: 38px;
        }

        .axiom-filter-pill-label {
            font-size: 14px;
        }
    }
</style>

<main class="axiom-catalog-page">
    <section class="axiom-catalog-hero">
        <div class="axiom-catalog-hero-inner">
            <p class="axiom-catalog-kicker">Research Catalog</p>

            <h1>
                <?php echo esc_html($page_title); ?>
            </h1>

            <p class="axiom-catalog-subtitle">
                <?php echo esc_html($page_desc); ?>
            </p>
        </div>
    </section>

    <section class="axiom-catalog-category-section">
        <div class="axiom-catalog-category-heading">
            <p class="axiom-catalog-category-kicker">
                Browse the catalog
            </p>

            <h2>Shop by category</h2>
        </div>

        <div class="axiom-category-scroll-shell">
            <div
                class="axiom-catalog-filter-pills"
                id="axiomCatalogFilters"
            >
                <?php foreach ($catalog_filter_groups as $index => $filter_group) : ?>
                    <button
                        type="button"
                        class="axiom-filter-pill<?php echo $index === 0 ? ' is-active' : ''; ?>"
                        data-filter="<?php echo esc_attr($filter_group['slug']); ?>"
                        aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                    >
                        <span class="axiom-filter-pill-icon">
                            <?php
                            echo axiom_catalog_filter_icon(
                                $filter_group['icon']
                            );
                            ?>
                        </span>

                        <span class="axiom-filter-pill-label">
                            <?php echo esc_html($filter_group['label']); ?>
                        </span>

                        <?php if (!empty($filter_group['note'])) : ?>
                            <span class="axiom-filter-pill-note">
                                <?php echo esc_html($filter_group['note']); ?>
                            </span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="axiom-category-scroll-cue" aria-hidden="true">
                <span>Swipe to see more categories</span>
                <span class="axiom-category-scroll-arrow">→</span>
            </div>
        </div>
    </section>

    <section class="axiom-catalog-toolbar-section">
        <div class="axiom-catalog-toolbar">
            <div class="axiom-catalog-search-wrap">
                <input
                    type="search"
                    id="axiomCatalogSearch"
                    class="axiom-catalog-search"
                    placeholder="Search products..."
                    aria-label="Search products"
                />
            </div>

            <div class="axiom-catalog-sort-wrap">
                <select
                    id="axiomCatalogSort"
                    class="axiom-catalog-sort"
                    aria-label="Sort products"
                >
                    <option value="default">Sort: Featured</option>
                    <option value="name-asc">Name: A to Z</option>
                    <option value="name-desc">Name: Z to A</option>
                    <option value="price-asc">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                    <option value="newest">Newest</option>
                </select>
            </div>
        </div>

        <div class="axiom-catalog-results-row">
            <span id="axiomCatalogCount">
                <?php echo intval($products->post_count); ?> results
            </span>
        </div>
    </section>

    <section class="axiom-catalog-grid-section">
        <div
            class="axiom-catalog-grid"
            id="axiomCatalogGrid"
        >
            <?php if ($products->have_posts()) : ?>
                <?php while ($products->have_posts()) : ?>
                    <?php
                    $products->the_post();

                    $product = wc_get_product(get_the_ID());

                    if (!$product) {
                        continue;
                    }

                    if (method_exists($product, 'get_catalog_visibility')) {
                        $catalog_visibility = $product->get_catalog_visibility();

                        if (
                            in_array(
                                $catalog_visibility,
                                array('hidden', 'search'),
                                true
                            )
                        ) {
                            continue;
                        }
                    }

                    $product_id      = $product->get_id();
                    $product_name    = $product->get_name();
                    $product_link    = get_permalink($product_id);
                    $image_html      = $product->get_image('woocommerce_thumbnail');
                    $price_html      = $product->get_price_html();
                    $is_on_sale      = $product->is_on_sale();
                    $stock_status    = $product->get_stock_status();
                    $is_in_stock     = ($stock_status === 'instock');
                    $is_backorder    = ($stock_status === 'onbackorder');
                    $is_out_of_stock = ($stock_status === 'outofstock');

                    $date_created = $product->get_date_created()
                        ? $product->get_date_created()->date('U')
                        : 0;

                    $raw_price = $product->get_price() !== ''
                        ? (float) $product->get_price()
                        : 0;

                    $product_terms = get_the_terms(
                        $product_id,
                        'product_cat'
                    );

                    $term_slugs = array();
                    $term_names = array();

                    if (
                        !is_wp_error($product_terms) &&
                        !empty($product_terms)
                    ) {
                        foreach ($product_terms as $term) {
                            $term_slugs[] = $term->slug;
                            $term_names[] = $term->name;
                        }
                    }

                    $normalized_name = strtolower($product_name);

                    $is_kit_product = has_term(
                        array('kits', 'kit'),
                        'product_cat',
                        $product_id
                    );

                    $is_hgh_product = has_term(
                        array('hgh', 'human-growth-hormone'),
                        'product_cat',
                        $product_id
                    );

                    if (!$is_hgh_product) {
                        if (
                            strpos($normalized_name, 'hgh') !== false ||
                            strpos(
                                $normalized_name,
                                'human growth hormone'
                            ) !== false
                        ) {
                            $is_hgh_product = true;
                        }
                    }

                    $is_anabolic_product = has_term(
                        array(
                            'anabolics',
                            'anabolic',
                            'steroids',
                            'steroid',
                        ),
                        'product_cat',
                        $product_id
                    );

                    $is_research_supply = has_term(
                        array(
                            'research-supplies',
                            'research-supply',
                            'supplies',
                            'bac-water',
                            'bacteriostatic-water',
                        ),
                        'product_cat',
                        $product_id
                    );

                    if (!$is_research_supply) {
                        if (
                            strpos($normalized_name, 'bac water') !== false ||
                            strpos(
                                $normalized_name,
                                'bacteriostatic water'
                            ) !== false ||
                            strpos(
                                $normalized_name,
                                'research supply'
                            ) !== false
                        ) {
                            $is_research_supply = true;
                        }
                    }

                    /**
                     * Every product appears under All Products.
                     */
                    $term_slugs[] = 'all';

                    /**
                     * Assign each product to its front-end filters.
                     *
                     * Research supplies remain under All Products only.
                     */
                    if ($is_kit_product) {
                        $term_slugs[] = 'kits';
                    }

                    if ($is_hgh_product) {
                        $term_slugs[] = 'hgh';
                    }

                    if ($is_anabolic_product) {
                        $term_slugs[] = 'anabolics';
                    }

                    if (
                        !$is_kit_product &&
                        !$is_anabolic_product &&
                        !$is_research_supply
                    ) {
                        $term_slugs[] = 'peptides';
                    }

                    $term_slugs_string = implode(
                        ' ',
                        array_unique($term_slugs)
                    );

                    $term_names_string = implode(
                        ', ',
                        array_unique($term_names)
                    );
                    ?>

                    <article
                        class="axiom-product-card<?php echo $is_backorder ? ' axiom-product-card-backorder' : ($is_out_of_stock ? ' axiom-product-card-out-of-stock' : ''); ?>"
                        data-name="<?php echo esc_attr(strtolower($product_name)); ?>"
                        data-price="<?php echo esc_attr($raw_price); ?>"
                        data-date="<?php echo esc_attr($date_created); ?>"
                        data-categories="<?php echo esc_attr($term_slugs_string); ?>"
                    >
                        <a
                            href="<?php echo esc_url($product_link); ?>"
                            class="axiom-product-card-link"
                        >
                            <div class="axiom-product-image-wrap">
                                <?php if ($is_backorder) : ?>
                                    <span class="axiom-product-badge axiom-product-badge-backorder">
                                        Backorder
                                    </span>
                                <?php elseif ($is_out_of_stock) : ?>
                                    <span class="axiom-product-badge axiom-product-badge-out">
                                        Out of stock
                                    </span>
                                <?php elseif ($is_on_sale) : ?>
                                    <span class="axiom-product-badge">
                                        Sale
                                    </span>
                                <?php endif; ?>

                                <div class="axiom-product-image">
                                    <?php
                                    echo $image_html
                                        ? $image_html
                                        : wc_placeholder_img(
                                            'woocommerce_thumbnail'
                                        );
                                    ?>
                                </div>
                            </div>

                            <div class="axiom-product-content">
                                <?php if (!empty($term_names_string)) : ?>
                                    <p class="axiom-product-category">
                                        <?php echo esc_html($term_names_string); ?>
                                    </p>
                                <?php endif; ?>

                                <h2 class="axiom-product-title">
                                    <?php echo esc_html($product_name); ?>
                                </h2>

                                <div class="axiom-product-price">
                                    <?php echo wp_kses_post($price_html); ?>
                                </div>
                            </div>
                        </a>

                        <div class="axiom-product-actions">
                            <?php
                            if (
                                $product->is_purchasable() &&
                                ($is_in_stock || $is_backorder)
                            ) :
                                ?>

                                <?php if ($product->is_type('simple')) : ?>
                                    <?php
                                    echo sprintf(
                                        '<a href="%1$s" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart axiom-product-button" data-product_id="%2$s" data-product_sku="%3$s" aria-label="%4$s" rel="nofollow">%5$s</a>',
                                        esc_url($product->add_to_cart_url()),
                                        esc_attr($product_id),
                                        esc_attr($product->get_sku()),
                                        esc_attr(
                                            sprintf(
                                                __('Add %s to cart', 'axiom'),
                                                $product_name
                                            )
                                        ),
                                        esc_html__('Add to cart', 'axiom')
                                    );
                                    ?>
                                <?php else : ?>
                                    <a
                                        href="<?php echo esc_url($product_link); ?>"
                                        class="axiom-product-button axiom-product-button-secondary"
                                    >
                                        <?php esc_html_e('Choose options', 'axiom'); ?>
                                    </a>
                                <?php endif; ?>

                            <?php else : ?>
                                <a
                                    href="<?php echo esc_url($product_link); ?>"
                                    class="axiom-product-button axiom-product-button-secondary"
                                >
                                    <?php esc_html_e('View product', 'axiom'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; ?>

                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="axiom-catalog-empty">
                    <h2>No products found</h2>
                    <p>There are no products in the catalog yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="axiom-catalog-bottom-disclaimer">
        <div class="axiom-catalog-bottom-disclaimer-inner">
            <h2>Research Use Only</h2>

            <p>
                All products listed on this page are intended solely for
                in vitro laboratory research by qualified researchers.
                These compounds are not approved for human or veterinary
                use and are not intended to diagnose, treat, cure, or
                prevent any disease or condition. Not for human consumption.
            </p>
        </div>
    </section>
</main>

<?php get_footer('shop'); ?>
