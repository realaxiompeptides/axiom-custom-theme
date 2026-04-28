<?php
/**
 * Template Name: Floating Vials Test
 */

if (!defined('ABSPATH')) exit;

get_header();

$random_products = wc_get_products(array(
    'status'  => 'publish',
    'limit'   => 5,
    'orderby' => 'rand',
    'return'  => 'objects',
));
?>

<main class="axiom-floating-test-page">

    <section class="axiom-floating-hero">

        <div class="axiom-floating-bg-glow"></div>

        <div class="axiom-floating-stats">
            <div><strong>99%+</strong><span>Purity Guaranteed</span></div>
            <div><strong>3rd Party</strong><span>Lab Tested</span></div>
            <div><strong>Same Day</strong><span>Fulfillment</span></div>
            <div><strong>Discreet</strong><span>Packaging</span></div>
        </div>

        <p class="axiom-floating-proof">
            10,000+ Orders Shipped · 99.4% Average Purity · 4.9★ Customer Rating
        </p>

        <div class="axiom-vial-stage">

            <?php
            $i = 1;

            foreach ($random_products as $product) :
                $image_id  = $product->get_image_id();
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src();

                if (!$image_url) {
                    continue;
                }
            ?>
                <a class="axiom-floating-vial vial-<?php echo esc_attr($i); ?>"
                   href="<?php echo esc_url(get_permalink($product->get_id())); ?>"
                   aria-label="<?php echo esc_attr($product->get_name()); ?>">

                    <img src="<?php echo esc_url($image_url); ?>"
                         alt="<?php echo esc_attr($product->get_name()); ?>">
                </a>
            <?php
                $i++;
            endforeach;
            ?>

        </div>

    </section>

</main>

<?php get_footer(); ?>
