<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * COA matching map
 *
 * product_aliases = words that identify the product
 * variant_aliases = optional words that identify likely variants/sizes
 */
function axiom_coa_file_map() {
    return array(
        'BPC-157' => array(
            'product_aliases' => array('bpc-157', 'bpc157'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'NAD+' => array(
            'product_aliases' => array('nad+', 'nad-plus', 'nad'),
            'variant_aliases' => array('500mg'),
        ),

        'GHK-CU' => array(
            'product_aliases' => array('ghk-cu', 'ghkcu'),
            'variant_aliases' => array('50mg', '100mg', '3ml'),
        ),

        'GLP-3 RT' => array(
            'product_aliases' => array('glp-3-rt', 'glp3rt', 'reta', 'retatrutide'),
            'variant_aliases' => array('10mg', '20mg', '3ml'),
        ),

        'SELANK' => array(
            'product_aliases' => array('selank'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'SEMAX' => array(
            'product_aliases' => array('semax'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'TB-500' => array(
            'product_aliases' => array('tb-500', 'tb500'),
            'variant_aliases' => array('10mg'),
        ),

        'SS-31' => array(
            'product_aliases' => array('ss-31', 'ss31'),
            'variant_aliases' => array('10mg'),
        ),

        'GLOW' => array(
            'product_aliases' => array('glow'),
            'variant_aliases' => array('50mg', '70mg'),
        ),

        'BAC WATER' => array(
            'product_aliases' => array('bac-water', 'bacwater', 'bacteriostatic-water'),
            'variant_aliases' => array('10ml'),
        ),

        'MOTS-C' => array(
            'product_aliases' => array('mots-c', 'motsc'),
            'variant_aliases' => array('10mg'),
        ),

        'DSIP' => array(
            'product_aliases' => array('dsip'),
            'variant_aliases' => array('5mg'),
        ),

        'MT-2' => array(
            'product_aliases' => array('mt-2', 'mt2', 'melanotan-2', 'melanotan-2-acetate'),
            'variant_aliases' => array('10mg'),
        ),
    );
}
