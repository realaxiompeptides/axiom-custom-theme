<?php
if (!defined('ABSPATH')) {
    exit;
}

$theme_uri = get_template_directory_uri();

$axiom_floating_vials = array(
    array(
        'class' => 'axiom-vial-float--one',
        'src'   => $theme_uri . '/assets/images/floating-vials/ghk-cu-50mg.PNG',
        'alt'   => 'GHK-Cu 50mg',
    ),
    array(
        'class' => 'axiom-vial-float--two',
        'src'   => $theme_uri . '/assets/images/floating-vials/glp-3-rt-60mg.PNG',
        'alt'   => 'GLP-3 RT 60mg',
    ),
    array(
        'class' => 'axiom-vial-float--three',
        'src'   => $theme_uri . '/assets/images/floating-vials/5-amino-1mq-5mg.PNG',
        'alt'   => '5-Amino 1MQ 5mg',
    ),
    array(
        'class' => 'axiom-vial-float--four',
        'src'   => $theme_uri . '/assets/images/floating-vials/mt1-10mg.PNG',
        'alt'   => 'MT-1 10mg',
    ),
    array(
        'class' => 'axiom-vial-float--five',
        'src'   => $theme_uri . '/assets/images/floating-vials/nad-500mg.PNG',
        'alt'   => 'NAD+ 500mg',
    ),
);
?>

<section class="axiom-vial-showcase" aria-label="Axiom research product showcase">
    <div class="axiom-vial-showcase-bg" aria-hidden="true">
        <div class="axiom-vial-showcase-grid"></div>
        <div class="axiom-vial-showcase-glow axiom-vial-showcase-glow--one"></div>
        <div class="axiom-vial-showcase-glow axiom-vial-showcase-glow--two"></div>
    </div>

    <div class="axiom-vial-showcase-inner">
        <div class="axiom-vial-float-stage">
            <?php foreach ($axiom_floating_vials as $vial) : ?>
                <div class="axiom-vial-float <?php echo esc_attr($vial['class']); ?>">
                    <img
                        src="<?php echo esc_url($vial['src']); ?>"
                        alt="<?php echo esc_attr($vial['alt']); ?>"
                        loading="lazy"
                        decoding="async"
                    />
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
