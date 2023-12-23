<?php

// define a empty array
$categories_array = [];
function fetch_all_categories_from_db() {
    ob_start();

    global $categories_array;
    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_category';

    // Your SQL query to retrieve data from the column
    $query      = "SELECT operation_value FROM $table_name";
    $categories = $wpdb->get_results( $query, ARRAY_A );

    foreach ( $categories as $category ) {

        $category_json  = $category['operation_value'];
        $category_array = json_decode( $category_json, true );

        // extract category name from category_array
        $category_name = $category_array['CategoryName'] . '<br>';

        // Convert sting to array with <br> separator
        $cat_arr = explode( '<br>', $category_name );

        // filter array to remove empty space
        $cat_arr = array_filter( $cat_arr );

        $categories_array = $cat_arr;
        // return $categories_array;
        // print_r( $categories_array);
    }

    return ob_get_clean();
}

// create shortcode for fetch category from db
add_shortcode( 'fetch_all_categories', 'fetch_all_categories_from_db' );

/*
// Add categories to woocommerce category
function add_product_category_callback() {
    ob_start();

    global $categories_array;

    foreach ( $categories_array as $category ) {

        // Set the category name and slug
        $category_name = $category;
        $category_slug = sanitize_title( $category_name );

        // Check if the category already exists
        $term = get_term_by( 'slug', $category_slug, 'product_cat' );

        if ( !$term ) {
            // If the category doesn't exist, add it
            $args = [
                'cat_name'             => $category_name,
                'category_nicename'    => $category_slug,
                'category_description' => '',
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
    }

    return ob_get_clean();
}

// Add categories to woocommerce category
add_shortcode( 'add_product_category_shortcode', 'add_product_category_callback' );
*/

?>