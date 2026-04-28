<?php
/**
 * Template Name: Floating Vials
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

<main class="axiom-floating-page">
    <section class="axiom-floating-white-section">
        <div class="axiom-vial-stage">
            <?php foreach ($random_products as $product) :
                $image_id  = $product->get_image_id();
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src();
            ?>
                <a class="axiom-floating-vial" href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
