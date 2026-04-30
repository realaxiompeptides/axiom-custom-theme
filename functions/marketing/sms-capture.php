<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SMS country selector data.
 * This does NOT send SMS yet.
 * It provides country validation data to popup.js.
 */

add_action('wp_enqueue_scripts', function () {
    if (!wp_script_is('axiom-popup', 'enqueued')) {
        return;
    }

    wp_localize_script('axiom-popup', 'AXIOM_SMS_COUNTRIES', array(
        'defaultCountry' => 'US',
        'countries' => array(
            array(
                'code' => 'US',
                'name' => 'United States',
                'dial' => '+1',
                'min'  => 10,
                'max'  => 10,
                'flag' => '🇺🇸',
            ),
            array(
                'code' => 'CA',
                'name' => 'Canada',
                'dial' => '+1',
                'min'  => 10,
                'max'  => 10,
                'flag' => '🇨🇦',
            ),
            array(
                'code' => 'GB',
                'name' => 'United Kingdom',
                'dial' => '+44',
                'min'  => 10,
                'max'  => 10,
                'flag' => '🇬🇧',
            ),
            array(
                'code' => 'AU',
                'name' => 'Australia',
                'dial' => '+61',
                'min'  => 9,
                'max'  => 9,
                'flag' => '🇦🇺',
            ),
            array(
                'code' => 'NZ',
                'name' => 'New Zealand',
                'dial' => '+64',
                'min'  => 8,
                'max'  => 10,
                'flag' => '🇳🇿',
            ),
            array(
                'code' => 'DE',
                'name' => 'Germany',
                'dial' => '+49',
                'min'  => 10,
                'max'  => 12,
                'flag' => '🇩🇪',
            ),
            array(
                'code' => 'FR',
                'name' => 'France',
                'dial' => '+33',
                'min'  => 9,
                'max'  => 9,
                'flag' => '🇫🇷',
            ),
            array(
                'code' => 'ES',
                'name' => 'Spain',
                'dial' => '+34',
                'min'  => 9,
                'max'  => 9,
                'flag' => '🇪🇸',
            ),
            array(
                'code' => 'IT',
                'name' => 'Italy',
                'dial' => '+39',
                'min'  => 9,
                'max'  => 11,
                'flag' => '🇮🇹',
            ),
        ),
    ));
}, 30);
