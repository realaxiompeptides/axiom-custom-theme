<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/helpers.php';

add_action('woocommerce_product_options_general_product_data', 'axiom_kit_template_admin_fields');
add_action('woocommerce_process_product_meta', 'axiom_kit_template_save_admin_fields');

function axiom_kit_template_admin_fields() {
    echo '<div class="options_group axiom-kit-template-fields">';

    woocommerce_wp_checkbox(array(
        'id'          => '_axiom_force_kit_template',
        'label'       => 'Enable kit template',
        'description' => 'Force the kit conversion section to show for this product even if it is not in the kits category.',
    ));

    woocommerce_wp_text_input(array(
        'id'                => '_axiom_kit_vial_count',
        'label'             => 'Kit vial count',
        'description'       => 'Example: 10',
        'desc_tip'          => true,
        'type'              => 'number',
        'custom_attributes' => array(
            'min'  => '1',
            'step' => '1',
        ),
    ));

    woocommerce_wp_text_input(array(
        'id'          => '_axiom_kit_single_product_id',
        'label'       => 'Single-vial product ID',
        'description' => 'Optional. Link the matching single-vial product so savings can be calculated automatically.',
        'desc_tip'    => true,
        'type'        => 'number',
    ));

    woocommerce_wp_textarea_input(array(
        'id'          => '_axiom_kit_microcopy',
        'label'       => 'Kit microcopy',
        'description' => 'Short sales line shown under the heading.',
        'desc_tip'    => true,
    ));

    $competitors = axiom_kit_template_get_competitor_fields();

    foreach ($competitors as $key => $label) {
        woocommerce_wp_text_input(array(
            'id'                => '_axiom_competitor_' . $key . '_price',
            'label'             => sprintf('%s comparable price', $label),
            'description'       => 'Enter the full comparable kit price only when the match is real.',
            'desc_tip'          => true,
            'type'              => 'number',
            'custom_attributes' => array(
                'min'  => '0',
                'step' => '0.01',
            ),
        ));
    }

    echo '</div>';
}

function axiom_kit_template_save_admin_fields($post_id) {
    update_post_meta($post_id, '_axiom_force_kit_template', isset($_POST['_axiom_force_kit_template']) ? 'yes' : 'no');

    foreach (array('_axiom_kit_vial_count', '_axiom_kit_single_product_id') as $field_key) {
        if (isset($_POST[$field_key])) {
            update_post_meta($post_id, $field_key, absint(wp_unslash($_POST[$field_key])));
        }
    }

    if (isset($_POST['_axiom_kit_microcopy'])) {
        update_post_meta($post_id, '_axiom_kit_microcopy', sanitize_textarea_field(wp_unslash($_POST['_axiom_kit_microcopy'])));
    }

    foreach (array_keys(axiom_kit_template_get_competitor_fields()) as $key) {
        $meta_key = '_axiom_competitor_' . $key . '_price';
        if (!isset($_POST[$meta_key])) {
            continue;
        }

        $raw = wc_clean(wp_unslash($_POST[$meta_key]));
        if ($raw === '') {
            delete_post_meta($post_id, $meta_key);
            continue;
        }

        update_post_meta($post_id, $meta_key, wc_format_decimal($raw));
    }
}
