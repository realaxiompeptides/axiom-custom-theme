<?php
/**
 * Template Name: Floating Vials Test
 */

if (!defined('ABSPATH')) exit;

get_header();
?>

<main class="axiom-floating-test-page">

    <section class="axiom-floating-hero">

        <div class="axiom-floating-bg-glow"></div>

        <div class="axiom-floating-stats">
            <div>
                <strong>99%+</strong>
                <span>Purity Guaranteed</span>
            </div>
            <div>
                <strong>3rd Party</strong>
                <span>Lab Tested</span>
            </div>
            <div>
                <strong>Same Day</strong>
                <span>Fulfillment</span>
            </div>
            <div>
                <strong>Discreet</strong>
                <span>Packaging</span>
            </div>
        </div>

        <p class="axiom-floating-proof">
            10,000+ Orders Shipped · 99.4% Average Purity · 4.9★ Customer Rating
        </p>

        <div class="axiom-vial-stage">

            <img class="axiom-floating-vial vial-1"
                 src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/vials/vial-1.png'); ?>"
                 alt="Research vial">

            <img class="axiom-floating-vial vial-2"
                 src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/vials/vial-2.png'); ?>"
                 alt="Research vial">

            <img class="axiom-floating-vial vial-3"
                 src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/vials/vial-3.png'); ?>"
                 alt="Research vial">

            <img class="axiom-floating-vial vial-4"
                 src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/vials/vial-4.png'); ?>"
                 alt="Research vial">

            <img class="axiom-floating-vial vial-5"
                 src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/vials/vial-5.png'); ?>"
                 alt="Research vial">

        </div>

    </section>

</main>

<?php get_footer(); ?>
