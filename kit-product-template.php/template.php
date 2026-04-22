<?php
if (!defined('ABSPATH') || empty($kit_data) || !is_array($kit_data)) {
    return;
}
?>
<section class="axiom-kit-template" aria-labelledby="axiomKitTemplateHeading">
    <div class="axiom-kit-template__top">
        <p class="axiom-kit-template__eyebrow">Kit Value</p>
        <h2 id="axiomKitTemplateHeading"><?php echo esc_html($kit_data['vial_count']); ?> vials. Better bundle value.</h2>
        <p class="axiom-kit-template__intro"><?php echo esc_html($kit_data['microcopy']); ?></p>
    </div>

    <div class="axiom-kit-template__grid">
        <div class="axiom-kit-template__card">
            <span class="axiom-kit-template__label">Kit price</span>
            <strong class="axiom-kit-template__value"><?php echo wp_kses_post($kit_data['kit_price_html']); ?></strong>
        </div>

        <div class="axiom-kit-template__card">
            <span class="axiom-kit-template__label">Per-vial cost</span>
            <strong class="axiom-kit-template__value"><?php echo wp_kses_post($kit_data['per_vial_price_html']); ?></strong>
        </div>

        <div class="axiom-kit-template__card axiom-kit-template__card--highlight">
            <span class="axiom-kit-template__label">Your savings</span>
            <?php if ($kit_data['save_vs_singles'] > 0) : ?>
                <strong class="axiom-kit-template__value"><?php echo wp_kses_post($kit_data['save_vs_singles_html']); ?></strong>
                <span class="axiom-kit-template__subtext">
                    Save <?php echo esc_html($kit_data['save_percent']); ?>% versus buying <?php echo esc_html($kit_data['vial_count']); ?> singles.
                </span>
            <?php else : ?>
                <strong class="axiom-kit-template__value">Bulk value</strong>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($kit_data['comparison_rows'])) : ?>
        <div class="axiom-kit-template__comparison">
            <h3>Comparable market pricing</h3>
            <table class="axiom-kit-template__comparison-table">
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Comparable price</th>
                        <th>Axiom advantage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Axiom</strong></td>
                        <td><?php echo wp_kses_post($kit_data['kit_price_html']); ?></td>
                        <td>Current kit price</td>
                    </tr>
                    <?php foreach ($kit_data['comparison_rows'] as $row) : ?>
                        <tr>
                            <td><?php echo esc_html($row['name']); ?></td>
                            <td><?php echo wp_kses_post($row['price_html']); ?></td>
                            <td>
                                <?php if ($row['is_better']) : ?>
                                    Save <?php echo wp_kses_post($row['difference_html']); ?> with Axiom
                                <?php else : ?>
                                    Match manually before making price claims
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
