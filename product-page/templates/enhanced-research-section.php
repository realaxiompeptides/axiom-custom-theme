<?php
if (!defined('ABSPATH')) {
    exit;
}

global $product;

$enhanced_data = function_exists('axiom_get_enhanced_product_data')
    ? axiom_get_enhanced_product_data($product)
    : false;

if (!$enhanced_data) {
    return;
}

$category          = !empty($enhanced_data['category']) ? $enhanced_data['category'] : 'Research Compound';
$title             = !empty($enhanced_data['title']) ? $enhanced_data['title'] : get_the_title();
$subtitle          = !empty($enhanced_data['subtitle']) ? $enhanced_data['subtitle'] : '';
$badges            = !empty($enhanced_data['badges']) && is_array($enhanced_data['badges']) ? $enhanced_data['badges'] : array();
$data_sheet        = !empty($enhanced_data['data_sheet']) && is_array($enhanced_data['data_sheet']) ? $enhanced_data['data_sheet'] : array();
$quick_stats       = !empty($enhanced_data['quick_stats']) && is_array($enhanced_data['quick_stats']) ? $enhanced_data['quick_stats'] : array();
$mechanism_title   = !empty($enhanced_data['mechanism_title']) ? $enhanced_data['mechanism_title'] : 'Research Mechanism';
$mechanism_text    = !empty($enhanced_data['mechanism_text']) ? $enhanced_data['mechanism_text'] : '';
$research_profile  = !empty($enhanced_data['research_profile']) && is_array($enhanced_data['research_profile']) ? $enhanced_data['research_profile'] : array();
?>

<section class="axiom-enhanced-product-section">
    <div class="axiom-enhanced-container">

        <div class="axiom-enhanced-header axiom-enhanced-compact-header">
            <p class="axiom-enhanced-kicker"><?php echo esc_html($category); ?></p>

            <h2><?php echo esc_html($title); ?> Research Profile</h2>

            <?php if (!empty($subtitle)) : ?>
                <p><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>

            <?php if (!empty($badges)) : ?>
                <div class="axiom-enhanced-badges">
                    <?php foreach ($badges as $badge) : ?>
                        <span><?php echo esc_html($badge); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($data_sheet)) : ?>
            <div class="axiom-data-sheet-card axiom-compact-data-sheet">
                <div class="axiom-data-sheet-top">
                    <strong>AXIOM PEPTIDES</strong>
                    <span>Research Grade · 99%+ Purity</span>
                </div>

                <div class="axiom-data-sheet-grid">
                    <?php foreach ($data_sheet as $label => $value) : ?>
                        <div class="axiom-data-point">
                            <span><?php echo esc_html($label); ?></span>
                            <strong><?php echo esc_html($value); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($quick_stats)) : ?>
            <div class="axiom-quick-stats axiom-compact-quick-stats">
                <?php foreach ($quick_stats as $stat) : ?>
                    <?php
                    $stat_value = !empty($stat['value']) ? $stat['value'] : '';
                    $stat_label = !empty($stat['label']) ? $stat['label'] : '';
                    ?>
                    <div class="axiom-quick-stat">
                        <strong><?php echo esc_html($stat_value); ?></strong>
                        <span><?php echo esc_html($stat_label); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mechanism_text)) : ?>
            <details class="axiom-enhanced-accordion axiom-mechanism-card" open>
                <summary>
                    <span>
                        <small>Mechanism of Action</small>
                        <strong><?php echo esc_html($mechanism_title); ?></strong>
                    </span>
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>

                <div class="axiom-enhanced-accordion-body">
                    <p><?php echo esc_html($mechanism_text); ?></p>
                </div>
            </details>
        <?php endif; ?>

        <?php if (!empty($research_profile)) : ?>
            <details class="axiom-enhanced-accordion axiom-research-profile-card">
                <summary>
                    <span>
                        <small>Research Profile</small>
                        <strong>Research Background</strong>
                    </span>
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>

                <div class="axiom-enhanced-accordion-body">
                    <ul>
                        <?php foreach ($research_profile as $item) : ?>
                            <li><?php echo esc_html($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </details>
        <?php endif; ?>

    </div>
</section>
