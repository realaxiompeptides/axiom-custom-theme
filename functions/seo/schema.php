<?php
/**
 * Axiom Schema Markup
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_head', 'axiom_output_schema_markup', 20 );

function axiom_output_schema_markup() {
    if ( is_front_page() ) {
        axiom_output_organization_schema();
        axiom_output_website_schema();
    }

    if ( is_singular( 'product' ) ) {
        axiom_output_product_schema();
    }

    if ( is_product_category() ) {
        axiom_output_collection_schema();
    }

    axiom_output_breadcrumb_schema();
}

function axiom_output_organization_schema() {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'Axiom Research',
        'url'      => home_url( '/' ),
        'logo'     => get_stylesheet_directory_uri() . '/assets/images/axiom-logo.PNG',
        'email'    => 'support@axiomresearch.shop',
        'sameAs'   => array(),
    );

    axiom_print_schema( $schema );
}

function axiom_output_website_schema() {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => 'Axiom Research',
        'url'      => home_url( '/' ),
        'potentialAction' => array(
            '@type'       => 'SearchAction',
            'target'      => home_url( '/?s={search_term_string}' ),
            'query-input' => 'required name=search_term_string',
        ),
    );

    axiom_print_schema( $schema );
}

function axiom_output_product_schema() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $image_id  = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

    $availability = $product->is_in_stock()
        ? 'https://schema.org/InStock'
        : 'https://schema.org/OutOfStock';

    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product->get_name(),
        'image'       => $image_url ? array( $image_url ) : array(),
        'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
        'sku'         => $product->get_sku(),
        'brand'       => array(
            '@type' => 'Brand',
            'name'  => 'Axiom Research',
        ),
        'offers'      => array(
            '@type'         => 'Offer',
            'url'           => get_permalink( $product->get_id() ),
            'priceCurrency' => get_woocommerce_currency(),
            'price'         => $product->get_price(),
            'availability'  => $availability,
            'itemCondition' => 'https://schema.org/NewCondition',
        ),
    );

    axiom_print_schema( $schema );
}

function axiom_output_collection_schema() {
    $term = get_queried_object();

    if ( ! $term || is_wp_error( $term ) ) {
        return;
    }

    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'CollectionPage',
        'name'        => $term->name,
        'description' => wp_strip_all_tags( term_description( $term ) ),
        'url'         => get_term_link( $term ),
    );

    axiom_print_schema( $schema );
}

function axiom_output_breadcrumb_schema() {
    if ( is_front_page() ) {
        return;
    }

    $items = array(
        array(
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Home',
            'item'     => home_url( '/' ),
        ),
    );

    if ( is_singular( 'product' ) ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => 'Shop',
            'item'     => wc_get_page_permalink( 'shop' ),
        );

        $items[] = array(
            '@type'    => 'ListItem',
            'position' => 3,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );
    } elseif ( is_page() || is_single() ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );
    }

    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    );

    axiom_print_schema( $schema );
}

function axiom_print_schema( $schema ) {
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
