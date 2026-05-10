<?php
if (!defined('ABSPATH')) {
    exit;
}

$theme_uri = get_template_directory_uri();

$axiom_floating_products = array(
    array(
        'title'    => 'GHK-Cu',
        'subtitle' => '50mg / 3mL',
        'image'    => $theme_uri . '/assets/images/floating-vials/ghk-cu-50mg.PNG',
        'slug'     => 'ghk-cu',
    ),
    array(
        'title'    => 'GLP-3 RT',
        'subtitle' => '60mg / 3mL',
        'image'    => $theme_uri . '/assets/images/floating-vials/glp-3-rt-60mg.PNG',
        'slug'     => 'glp-3-rt',
    ),
    array(
        'title'    => '5-Amino 1MQ',
        'subtitle' => '5mg / 3mL',
        'image'    => $theme_uri . '/assets/images/floating-vials/5-amino-1mq-5mg.PNG',
        'slug'     => '5-amino-1mq',
    ),
    array(
        'title'    => 'MT-1',
        'subtitle' => '10mg / 3mL',
        'image'    => $theme_uri . '/assets/images/floating-vials/mt1-10mg.PNG',
        'slug'     => 'mt-1',
    ),
    array(
        'title'    => 'NAD+',
        'subtitle' => '500mg / 3mL',
        'image'    => $theme_uri . '/assets/images/floating-vials/nad-500mg.PNG',
        'slug'     => 'nad',
    ),
);
?>

<section class="axiom-floating-vials axiom-home-floating-vials" aria-label="Featured research products">
    <div class="axiom-floating-shell">
        <div class="axiom-floating-panel">

            <div class="axiom-floating-header">
                <span class="axiom-floating-kicker">
                    <i class="fa-solid fa-vials" aria-hidden="true"></i>
                    Featured Research Compounds
                </span>

                <h2>Explore Axiom Best Sellers</h2>

                <p>
                    Premium research-use products with clean documentation, transparent batch standards, and fast fulfillment.
                </p>
            </div>

            <div class="axiom-floating-products">
                <?php foreach ($axiom_floating_products as $product) : ?>
                    <?php
                    $product_post = get_page_by_path($product['slug'], OBJECT, 'product');
                    $product_link = $product_post ? get_permalink($product_post->ID) : home_url('/shop/');
                    ?>

                    <a
                        class="axiom-floating-product"
                        href="<?php echo esc_url($product_link); ?>"
                        aria-label="<?php echo esc_attr($product['title']); ?>"
                    >
                        <img
                            src="<?php echo esc_url($product['image']); ?>"
                            alt="<?php echo esc_attr($product['title']); ?>"
                            loading="lazy"
                            decoding="async"
                        >

                        <span class="axiom-floating-product-title">
                            <?php echo esc_html($product['title']); ?>
                        </span>

                        <span class="axiom-floating-product-subtitle">
                            <?php echo esc_html($product['subtitle']); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>
