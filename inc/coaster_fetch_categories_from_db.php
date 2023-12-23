<?php

function fetch_all_categories_from_db() {
    ob_start();

    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_category';

    // Your SQL query to retrieve data from the column
    $query   = "SELECT operation_value FROM $table_name";
    $categories = $wpdb->get_results( $query, ARRAY_A );
    
    // echo '<pre>';
    // print_r( $categories );

    foreach( $categories as $category ) {
        // echo $category['operation_value'] . '<br>';
        $category_json = $category['operation_value'];
        $category_array = json_decode( $category_json, true );

        /* echo '<pre>';
        print_r( $category_array );
        wp_die(); */

        $category_name = $category_array['CategoryName'] . '<br>';
        echo $category_name;
        
    }

    return ob_get_clean();
}

add_shortcode( 'fetch_all_categories', 'fetch_all_categories_from_db' );

?>