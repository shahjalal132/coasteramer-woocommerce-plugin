<?php

/**
 * Programmatically add a product category in WooCommerce.
 */
function add_custom_product_category_callback() {
    ob_start();

    // Set the category name and slug
    $category_name = 'Jalal Category';
    $category_slug = sanitize_title( $category_name );

    // Check if the category already exists
    $term = get_term_by( 'slug', $category_slug, 'product_cat' );

    if ( !$term ) {
        // If the category doesn't exist, add it
        $args = [
            'cat_name'             => $category_name,
            'category_nicename'    => $category_slug,
            'category_description' => 'Description for the jalal category',
            'category_parent'      => '', // You can set a parent category if needed
            'taxonomy'             => 'product_cat',
        ];

        $result = wp_insert_category( $args );

        if ( is_wp_error( $result ) ) {
            // Handle error
            echo 'Error adding category: ' . $result->get_error_message();
        } else {
            echo 'Category added successfully!';
        }
    } else {
        echo 'Category already exists!';
    }

    return ob_get_clean();
}

add_shortcode( 'add_custom_product_category', 'add_custom_product_category_callback' );

?>