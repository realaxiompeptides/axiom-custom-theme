<?php
if (!defined('ABSPATH')) {
    exit;
}

$axiom_floating_products = wc_get_products(array(
    'status'  => 'publish',
    'limit'   => 5,
    'orderby' => 'rand',
    'return'  => 'objects',
));
?>

<section class="axiom-floating-white-section axiom-home-floating-vials" aria-label="Featured research products">
    <div class="axiom-vial-stage">

        <?php foreach ($axiom_floating_products as $product) :

            if (!$product || !is_a($product, 'WC_Product')) {
                continue;
            }

            $image_id  = $product->get_image_id();
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src();
            ?>

            <a class="axiom-floating-vial"
               href="<?php echo esc_url(get_permalink($product->get_id())); ?>"
               aria-label="<?php echo esc_attr($product->get_name()); ?>">

                <img src="<?php echo esc_url($image_url); ?>"
                     alt="<?php echo esc_attr($product->get_name()); ?>"
                     loading="lazy">

            </a>

        <?php endforeach; ?>

    </div>
</section>
