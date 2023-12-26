<?php

/**
 * Retrieves the name of a product category based on category code.
 *
 * @param int $category_code The code of the category.
 * @return string|null The name of the category, or null if not found.
 */
function getCategoryByCode( $category_code ) {
    // Get all product categories
    $categories = get_terms( [
        'taxonomy' => 'product_cat', // The taxonomy of the terms to retrieve
        'hide_empty' => false, // Whether to hide categories with no products
    ] );

    // Filter out only parent categories
    $parent_categories = array_filter( $categories, function ( $category ) {
        return $category->parent == 0; // Parent categories have a parent ID of 0
    } );

    // Loop through parent categories
    foreach ( $parent_categories as $category ) {
        // Check if category code matches
        if ( $category instanceof WP_Term && $category->description === $category_code ) {
            return $category->name; // Return the category name if code matches
        }
    }

    return null; // Return null if category not found
}

?>