<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * COA map:
 * KEY   = exact WooCommerce product name
 * VALUE = array of exact media title / file basename matches
 *
 * Keep these names lowercase and matching your uploaded file names/titles.
 */
function axiom_coa_file_map() {
    return array(
        'BPC-157' => array(
            'axiom-bpc-157-5mg-coa',
        ),

        'NAD+' => array(
            'axiom-nad-plus-500mg-coa',
        ),

        'GHK-CU' => array(
            'axiom-ghk-cu-100mg-3ml-coa',
        ),

        'GLP-3 RT' => array(
            'axiom-glp-3-rt-10mg-3ml-coa',
            'axiom-glp-3-rt-20mg-3ml-coa',
        ),

        'SELANK' => array(
            'axiom-selank-10mg-coa',
        ),

        'SEMAX' => array(
            'axiom-semax-10mg-3ml-coa',
        ),

        'TB-500' => array(
            'axiom-tb-500-10mg-coa',
        ),

        'SS-31' => array(
            'axiom-ss-31-10mg-coa',
        ),

        'GLOW' => array(
            'axiom-glow-50mg-coa',
        ),

        'BAC WATER' => array(
            'axiom-bac-water-10ml-coa',
        ),
    );
}
