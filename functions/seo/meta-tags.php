<?php
/**
 * Axiom SEO Meta Tags
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_head', 'axiom_output_seo_meta_tags', 1 );

function axiom_output_seo_meta_tags() {
    if ( is_admin() ) {
        return;
    }

    $title       = axiom_get_seo_title();
    $description = axiom_get_seo_description();
    $canonical   = axiom_get_canonical_url();

    if ( $title ) {
        echo '<title>' . esc_html( $title ) . '</title>' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    }

    if ( $description ) {
        echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
    }

    if ( $canonical ) {
        echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
    }

    echo '<meta property="og:site_name" content="Axiom Research">' . "\n";
    echo '<meta property="og:type" content="' . ( is_singular( 'product' ) ? 'product' : 'website' ) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}

function axiom_get_seo_title() {
    if ( is_front_page() ) {
        return 'Axiom Research | COA-Tested Research Peptides, USA Fulfilled';
    }

    if ( is_singular( 'product' ) ) {
        global $post;
        return get_the_title( $post ) . ' | Research Use Only | Axiom Research';
    }

    if ( is_product_category() ) {
        $term = get_queried_object();
        return $term->name . ' | Research Compounds | Axiom Research';
    }

    if ( is_page() || is_single() ) {
        return get_the_title() . ' | Axiom Research';
    }

    return wp_get_document_title();
}

function axiom_get_seo_description() {
    if ( is_front_page() ) {
        return 'Axiom Research supplies COA-tested research peptides and compounds for laboratory research use only, with secure checkout and USA fulfillment from California.';
    }

    if ( is_singular( 'product' ) ) {
        global $post;

        $title = get_the_title( $post );

        return $title . ' from Axiom Research is offered for laboratory research use only. View product details, COA information when available, and USA fulfillment options.';
    }

    if ( is_product_category() ) {
        $term = get_queried_object();

        return 'Shop ' . $term->name . ' from Axiom Research for laboratory research use only. COA information, secure checkout, and USA fulfillment available.';
    }

    if ( is_page() || is_single() ) {
        $excerpt = get_the_excerpt();

        if ( $excerpt ) {
            return wp_trim_words( wp_strip_all_tags( $excerpt ), 28, '' );
        }
    }

    return 'Axiom Research provides research-use-only compounds with COA documentation, secure checkout, and USA fulfillment.';
}

function axiom_get_canonical_url() {
    if ( is_singular() ) {
        return get_permalink();
    }

    if ( is_product_category() || is_category() || is_tag() ) {
        $term = get_queried_object();

        if ( $term && ! is_wp_error( $term ) ) {
            return get_term_link( $term );
        }
    }

    return home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
}
