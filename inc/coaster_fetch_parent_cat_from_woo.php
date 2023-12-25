<?php

function coaster_get_parent_categories_from_woocommerce() {
    // Get all product categories
    $categories = get_terms( [
        'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
        'hide_empty' => false, // Set to true if you only want non-empty categories
    ] );

// Filter out only parent categories
    $parent_categories = array_filter( $categories, function ( $category ) {
        return $category->parent == 0; // Parent categories have a parent ID of 0
    } );

    echo '<pre>';
    print_r( $parent_categories );
    wp_die();
}

add_shortcode( 'coaster_get_parent_categories_shortcode', 'coaster_get_parent_categories_from_woocommerce' );

?>