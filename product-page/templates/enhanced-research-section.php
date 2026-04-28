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
?>

<section class="axiom-enhanced-product-section">

    <div class="axiom-enhanced-container">

        <div class="axiom-enhanced-header">
            <p class="axiom-enhanced-kicker">
                <?php echo esc_html($enhanced_data['category']); ?>
            </p>

            <h2><?php echo esc_html($enhanced_data['title']); ?> Research Data Sheet</h2>

            <p><?php echo esc_html($enhanced_data['subtitle']); ?></p>

            <?php if (!empty($enhanced_data['badges'])) : ?>
                <div class="axiom-enhanced-badges">
                    <?php foreach ($enhanced_data['badges'] as $badge) : ?>
                        <span><?php echo esc_html($badge); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($enhanced_data['data_sheet'])) : ?>
            <div class="axiom-data-sheet-card">
                <div class="axiom-data-sheet-top">
                    <strong>AXIOM PEPTIDES</strong>
                    <span>Research Grade · 99%+ Purity</span>
                </div>

                <div class="axiom-data-sheet-grid">
                    <?php foreach ($enhanced_data['data_sheet'] as $label => $value) : ?>
                        <div class="axiom-data-point">
                            <span><?php echo esc_html($label); ?></span>
                            <strong><?php echo esc_html($value); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($enhanced_data['quick_stats'])) : ?>
            <div class="axiom-quick-stats">
                <?php foreach ($enhanced_data['quick_stats'] as $stat) : ?>
                    <div class="axiom-quick-stat">
                        <strong><?php echo esc_html($stat['value']); ?></strong>
                        <span><?php echo esc_html($stat['label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="axiom-mechanism-card">
            <p class="axiom-enhanced-kicker">Mechanism of Action</p>
            <h3><?php echo esc_html($enhanced_data['mechanism_title']); ?></h3>
            <p><?php echo esc_html($enhanced_data['mechanism_text']); ?></p>
        </div>

        <?php if (!empty($enhanced_data['research_profile'])) : ?>
            <div class="axiom-research-profile-card">
                <p class="axiom-enhanced-kicker">Research Profile</p>
                <h3>Research Background</h3>

                <ul>
                    <?php foreach ($enhanced_data['research_profile'] as $item) : ?>
                        <li><?php echo esc_html($item); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="axiom-safety-disclaimer-card">
            <p class="axiom-enhanced-kicker">Research Disclaimer</p>
            <h3>Research Use Only</h3>
            <p><?php echo esc_html($enhanced_data['safety_disclaimer']); ?></p>
        </div>

    </div>

</section>
