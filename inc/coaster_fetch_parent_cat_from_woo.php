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
            $category_name = $category->name; // Return the category name if code matches
            $category_id   = $category->term_id;

            // Return an array containing both the category name and ID
            return [
                'category_name' => $category_name,
                'category_id'   => $category_id,
            ];
        }
    }

    return null; // Return null if category not found
}

function get_subcategory_by_code_callback() {
    // Replace 'your_parent_category_id' with the ID of the parent category you are interested in.
    $parent_category_id = '3378';

    // Get subcategories of the specified parent category
    $subcategories = get_terms( [
        'taxonomy'   => 'product_cat', // WooCommerce product category taxonomy
        'parent' => $parent_category_id,
        'hide_empty' => false, // Set to true to hide empty categories
    ] );

    // Loop through the subcategories
    foreach ( $subcategories as $subcategory ) {
        // Access subcategory properties
        $subcategory_id   = $subcategory->term_id;
        $subcategory_name = $subcategory->name;
        $subcategory_slug = $subcategory->slug;

        // Output or use subcategory information as needed
        echo "Subcategory ID: $subcategory_id, Name: $subcategory_name, Slug: $subcategory_slug <br>";
    }

}

add_shortcode( 'get_subcategory_by_code', 'get_subcategory_by_code_callback' );

function get_subcategory_by_parent_category_code( $parent_category_code ) {

    // Get subcategories of the specified parent category
    $subcategories = get_terms( [
        'taxonomy'   => 'product_cat', // WooCommerce product category taxonomy
        'parent' => $parent_category_code,
        'hide_empty' => false, // Set to true to hide empty categories
    ] );

    return $subcategories;
}

?>