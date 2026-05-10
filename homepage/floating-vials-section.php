<?php
if (!defined('ABSPATH')) {
    exit;
}

$theme_uri = get_template_directory_uri();

$axiom_vials = array(
    array(
        'image' => $theme_uri . '/assets/images/floating-vials/ghk-cu-50mg.PNG',
        'alt'   => 'GHK-Cu Axiom vial',
        'class' => 'axiom-vial-float--one',
    ),
    array(
        'image' => $theme_uri . '/assets/images/floating-vials/glp-3-rt-60mg.PNG',
        'alt'   => 'GLP-3 RT Axiom vial',
        'class' => 'axiom-vial-float--two',
    ),
    array(
        'image' => $theme_uri . '/assets/images/floating-vials/5-amino-1mq-5mg.PNG',
        'alt'   => '5-Amino 1MQ Axiom vial',
        'class' => 'axiom-vial-float--three',
    ),
    array(
        'image' => $theme_uri . '/assets/images/floating-vials/mt1-10mg.PNG',
        'alt'   => 'MT-1 Axiom vial',
        'class' => 'axiom-vial-float--four',
    ),
    array(
        'image' => $theme_uri . '/assets/images/floating-vials/nad-500mg.PNG',
        'alt'   => 'NAD+ Axiom vial',
        'class' => 'axiom-vial-float--five',
    ),
);
?>

<section class="axiom-vial-showcase" aria-label="Axiom research vial showcase">
    <div class="axiom-vial-showcase-bg" aria-hidden="true">
        <div class="axiom-vial-showcase-grid"></div>
        <div class="axiom-vial-showcase-glow axiom-vial-showcase-glow--one"></div>
        <div class="axiom-vial-showcase-glow axiom-vial-showcase-glow--two"></div>
    </div>

    <div class="axiom-vial-showcase-inner">
        <div class="axiom-vial-showcase-copy">
            <span class="axiom-vial-showcase-pill">
                <i class="fa-solid fa-vials" aria-hidden="true"></i>
                Axiom Research Collection
            </span>

            <h2>Precision Compounds. Clean Presentation.</h2>

            <p>
                A premium research-use catalog built around batch transparency, clean fulfillment, and professional documentation.
            </p>
        </div>

        <div class="axiom-vial-float-stage" aria-hidden="true">
            <?php foreach ($axiom_vials as $vial) : ?>
                <div class="axiom-vial-float <?php echo esc_attr($vial['class']); ?>">
                    <img
                        src="<?php echo esc_url($vial['image']); ?>"
                        alt="<?php echo esc_attr($vial['alt']); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
