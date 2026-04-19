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

$product_query_args = array(
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'posts_per_page'      => -1,
    'orderby'             => 'menu_order title',
    'order'               => 'ASC',
    'ignore_sticky_posts' => true,
);

if ($is_tax_archive && $current_term && !empty($current_term->term_id)) {
    $taxonomy = is_tax('product_tag') ? 'product_tag' : 'product_cat';

    $product_query_args['tax_query'] = array(
        array(
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => array((int) $current_term->term_id),
        ),
    );
}

$products = new WP_Query($product_query_args);

$catalog_terms = get_terms(array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
));
?>

<main class="axiom-catalog-page">
    <section class="axiom-catalog-hero">
        <div class="axiom-catalog-hero-inner">
            <p class="axiom-catalog-kicker">Research Catalog</p>
            <h1><?php echo esc_html($page_title); ?></h1>
            <p class="axiom-catalog-subtitle"><?php echo esc_html($page_desc); ?></p>
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
                <select id="axiomCatalogSort" class="axiom-catalog-sort" aria-label="Sort products">
                    <option value="default">Sort: Featured</option>
                    <option value="name-asc">Name: A to Z</option>
                    <option value="name-desc">Name: Z to A</option>
                    <option value="price-asc">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                    <option value="newest">Newest</option>
                </select>
            </div>
        </div>

        <div class="axiom-catalog-filter-pills" id="axiomCatalogFilters">
            <button type="button" class="axiom-filter-pill is-active" data-filter="all">All Products</button>

            <?php if (!is_wp_error($catalog_terms) && !empty($catalog_terms)) : ?>
                <?php foreach ($catalog_terms as $term) : ?>
                    <button
                        type="button"
                        class="axiom-filter-pill"
                        data-filter="<?php echo esc_attr($term->slug); ?>"
                    >
                        <?php echo esc_html($term->name); ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="axiom-catalog-results-row">
            <span id="axiomCatalogCount"><?php echo intval($products->found_posts); ?> results</span>
        </div>
    </section>

    <section class="axiom-catalog-grid-section">
        <div class="axiom-catalog-grid" id="axiomCatalogGrid">
            <?php if ($products->have_posts()) : ?>
                <?php while ($products->have_posts()) : $products->the_post(); ?>
                    <?php
                    $product = wc_get_product(get_the_ID());
                    if (!$product) {
                        continue;
                    }

                    $product_id    = $product->get_id();
                    $product_name  = $product->get_name();
                    $product_link  = get_permalink($product_id);
                    $image_html    = $product->get_image('woocommerce_thumbnail');
                    $price_html    = $product->get_price_html();
                    $is_on_sale    = $product->is_on_sale();
                    $is_in_stock   = $product->is_in_stock();
                    $date_created  = $product->get_date_created() ? $product->get_date_created()->date('U') : 0;
                    $raw_price     = $product->get_price() !== '' ? (float) $product->get_price() : 0;

                    $product_terms = get_the_terms($product_id, 'product_cat');
                    $term_slugs    = array();
                    $term_names    = array();

                    if (!is_wp_error($product_terms) && !empty($product_terms)) {
                        foreach ($product_terms as $term) {
                            $term_slugs[] = $term->slug;
                            $term_names[] = $term->name;
                        }
                    }

                    $term_slugs_string = implode(' ', $term_slugs);
                    $term_names_string = implode(', ', $term_names);
                    ?>
                    <article
                        class="axiom-product-card<?php echo !$is_in_stock ? ' axiom-product-card-out-of-stock' : ''; ?>"
                        data-name="<?php echo esc_attr(strtolower($product_name)); ?>"
                        data-price="<?php echo esc_attr($raw_price); ?>"
                        data-date="<?php echo esc_attr($date_created); ?>"
                        data-categories="<?php echo esc_attr($term_slugs_string); ?>"
                    >
                        <a href="<?php echo esc_url($product_link); ?>" class="axiom-product-card-link">
                            <div class="axiom-product-image-wrap">
                                <?php if (!$is_in_stock) : ?>
                                    <span class="axiom-product-badge axiom-product-badge-out">Out of stock</span>
                                <?php elseif ($is_on_sale) : ?>
                                    <span class="axiom-product-badge">Sale</span>
                                <?php endif; ?>

                                <div class="axiom-product-image">
                                    <?php echo $image_html ? $image_html : wc_placeholder_img('woocommerce_thumbnail'); ?>
                                </div>
                            </div>

                            <div class="axiom-product-content">
                                <?php if (!empty($term_names_string)) : ?>
                                    <p class="axiom-product-category"><?php echo esc_html($term_names_string); ?></p>
                                <?php endif; ?>

                                <h2 class="axiom-product-title"><?php echo esc_html($product_name); ?></h2>

                                <div class="axiom-product-price">
                                    <?php echo wp_kses_post($price_html); ?>
                                </div>
                            </div>
                        </a>

                        <div class="axiom-product-actions">
                            <?php if ($product->is_purchasable() && $is_in_stock) : ?>
                                <?php if ($product->is_type('simple')) : ?>
                                    <?php
                                    echo sprintf(
                                        '<a href="%s" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart axiom-product-button" data-product_id="%s" data-product_sku="%s" aria-label="%s" rel="nofollow">%s</a>',
                                        esc_url($product->add_to_cart_url()),
                                        esc_attr($product_id),
                                        esc_attr($product->get_sku()),
                                        esc_attr(sprintf(__('Add %s to cart', 'axiom'), $product_name)),
                                        esc_html__('Add to cart', 'axiom')
                                    );
                                    ?>
                                <?php else : ?>
                                    <a href="<?php echo esc_url($product_link); ?>" class="axiom-product-button axiom-product-button-secondary">
                                        <?php esc_html_e('Choose options', 'axiom'); ?>
                                    </a>
                                <?php endif; ?>
                            <?php else : ?>
                                <a href="<?php echo esc_url($product_link); ?>" class="axiom-product-button axiom-product-button-secondary">
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
                All products listed on this page are intended solely for in vitro laboratory research by qualified researchers.
                These compounds are not approved for human or veterinary use and are not intended to diagnose, treat, cure,
                or prevent any disease or condition. Not for human consumption.
            </p>
        </div>
    </section>
</main>

<?php get_footer('shop'); ?>
