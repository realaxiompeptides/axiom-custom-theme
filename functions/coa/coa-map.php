<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * COA matching map
 *
 * The array key should match the product name used on your COA page / product listings.
 * product_aliases = loose names that may appear in media titles or filenames
 * variant_aliases = strength / size words that may appear in filenames
 */
function axiom_coa_file_map() {
    return array(

        'Reta' => array(
            'product_aliases' => array('reta', 'retatrutide', 'rt', 'glp-3-rt', 'glp3rt'),
            'variant_aliases' => array('5mg', '10mg', '20mg', 'rt10', 'rt20'),
        ),

        'RT10' => array(
            'product_aliases' => array('reta', 'retatrutide', 'rt', 'glp-3-rt', 'glp3rt', 'rt10'),
            'variant_aliases' => array('10mg', 'rt10'),
        ),

        'RT20' => array(
            'product_aliases' => array('reta', 'retatrutide', 'rt', 'glp-3-rt', 'glp3rt', 'rt20'),
            'variant_aliases' => array('20mg', 'rt20'),
        ),

        'TB-500' => array(
            'product_aliases' => array('tb-500', 'tb500'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'BPC-157' => array(
            'product_aliases' => array('bpc-157', 'bpc157'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'GHK-Cu' => array(
            'product_aliases' => array('ghk-cu', 'ghkcu', 'ghk'),
            'variant_aliases' => array('50mg', '100mg'),
        ),

        'IPA' => array(
            'product_aliases' => array('ipa', 'ipamorelin'),
            'variant_aliases' => array('5mg'),
        ),

        'Ipamorelin' => array(
            'product_aliases' => array('ipa', 'ipamorelin'),
            'variant_aliases' => array('5mg'),
        ),

        'CJC 1295 No DAC' => array(
            'product_aliases' => array('cjc 1295 no dac', 'cjc-1295 no dac', 'cjc1295 no dac', 'cjc-no-dac', 'cjc no dac'),
            'variant_aliases' => array('5mg'),
        ),

        'CJC with DAC' => array(
            'product_aliases' => array('cjc with dac', 'cjc-1295 with dac', 'cjc1295 with dac', 'cjc dac', 'cjc-dac', 'cjc1295 dac'),
            'variant_aliases' => array('2mg'),
        ),

        'CJC + IPA' => array(
            'product_aliases' => array('cjc + ipa', 'cjc ipa', 'cjc+ipa', 'cjc/ipamorelin', 'cjc ipamorelin'),
            'variant_aliases' => array('5mg'),
        ),

        'MT-2' => array(
            'product_aliases' => array('mt-2', 'mt2', 'melanotan 2', 'melanotan-2', 'melanotan 2 acetate', 'melanotan-2-acetate'),
            'variant_aliases' => array('10mg'),
        ),

        'MT1' => array(
            'product_aliases' => array('mt1', 'mt-1', 'melanotan 1', 'melanotan-1'),
            'variant_aliases' => array('10mg'),
        ),

        'MOTS-C' => array(
            'product_aliases' => array('mots-c', 'motsc'),
            'variant_aliases' => array('10mg'),
        ),

        'BAC Water' => array(
            'product_aliases' => array('bac water', 'bac-water', 'bacwater', 'bacteriostatic water', 'bacteriostatic-water'),
            'variant_aliases' => array('10ml'),
        ),

        'Semax' => array(
            'product_aliases' => array('semax'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'Sermorelin' => array(
            'product_aliases' => array('sermorelin'),
            'variant_aliases' => array('5mg'),
        ),

        'Tesamorelin' => array(
            'product_aliases' => array('tesamorelin'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'NAD+' => array(
            'product_aliases' => array('nad+', 'nad plus', 'nad-plus', 'nad'),
            'variant_aliases' => array('500mg', '1000mg'),
        ),

        'Wolverine' => array(
            'product_aliases' => array('wolverine', 'bpc tb', 'bpc + tb', 'bpc tb500', 'bpc157 tb500'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'Glutathione' => array(
            'product_aliases' => array('glutathione'),
            'variant_aliases' => array('600mg', '1500mg'),
        ),

        'KPV' => array(
            'product_aliases' => array('kpv'),
            'variant_aliases' => array('10mg'),
        ),

        'Cerebrolysin' => array(
            'product_aliases' => array('cerebrolysin'),
            'variant_aliases' => array('60mg'),
        ),

        'DSIP' => array(
            'product_aliases' => array('dsip'),
            'variant_aliases' => array('5mg'),
        ),

        'ARA 290' => array(
            'product_aliases' => array('ara 290', 'ara-290', 'ara290'),
            'variant_aliases' => array('10mg'),
        ),

        'KissPeptin' => array(
            'product_aliases' => array('kisspeptin', 'kiss peptin', 'kiss-peptin'),
            'variant_aliases' => array('5mg'),
        ),

        'SS-31' => array(
            'product_aliases' => array('ss-31', 'ss31'),
            'variant_aliases' => array('10mg'),
        ),

        'GLOW' => array(
            'product_aliases' => array('glow'),
            'variant_aliases' => array('50mg', '70mg'),
        ),

        'PT-141' => array(
            'product_aliases' => array('pt-141', 'pt141'),
            'variant_aliases' => array('10mg'),
        ),

        'Pinealon' => array(
            'product_aliases' => array('pinealon'),
            'variant_aliases' => array('10mg'),
        ),

        'Selank' => array(
            'product_aliases' => array('selank'),
            'variant_aliases' => array('5mg', '10mg'),
        ),

        'Lemon Bottle' => array(
            'product_aliases' => array('lemon bottle', 'lemon-bottle', 'lemonbottle'),
            'variant_aliases' => array('10ml'),
        ),
    );
}
