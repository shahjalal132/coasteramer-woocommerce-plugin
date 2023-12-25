<?php

/**
 * Set category to product static test
 */

function coaster_set_category_to_product_callback() {
    // Replace these with your actual product ID and category ID
    $product_id  = 1570;
    $category_id = 3378;

// Set the product category
    wp_set_object_terms( $product_id, $category_id, 'product_cat' );

}

add_shortcode( 'coaster_set_category_to_product', 'coaster_set_category_to_product_callback' );

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