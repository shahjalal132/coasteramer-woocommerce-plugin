<?php

add_shortcode( 'insert_categories_to_woocommerce_from_db', 'add_categories_to_woocommerce_from_db' );

function add_categories_to_woocommerce_from_db() {
    ob_start();

    $categories_response    = fetch_categories_from_db(); // Fetch category data from the DB
    $categories_data = json_decode( $categories_response, true ); // Decode the JSON response

    if ( $categories_data ) {
        foreach ( $categories_data as $category ) {

            // echo '<pre>';
            // print_r( $category );
            // wp_die();

            $categories_items = $category['operation_value'];

            $category_value = json_decode( $categories_items, true );

            // echo '<pre>';
            // print_r( $category_value );
            // wp_die();

            foreach( $category_value as $value ) {
                $category_name  = $value['CategoryName'];
            $category_code  = $value['CategoryCode'];
            $category_slug  = $category_name; // Slug for the category

            // Initialize $piece_code before the loop
            $piece_code = '';

            // Add category to WooCommerce product categories
            $new_category = wp_insert_term( $category_name, 'product_cat', [
                'slug' => $category_slug,
                'description' => $piece_code,
            ] );

            if ( !is_wp_error( $new_category ) ) {
                // Category added successfully
                $category_id = $new_category['term_id'];

                // Handle subcategories and pieces insertion here
                // You need to implement logic for subcategories and pieces within this loop

                foreach ( $value['SubCategoryList'] as $subcategory ) {
                    $subcategory_name = $subcategory['SubCategoryName'];
                    $subcategory_slug = $subcategory_name;

                    // Insert subcategory to WooCommerce under its parent category
                    $new_subcategory = wp_insert_term( $subcategory_name, 'product_cat', [
                        'slug'   => $subcategory_slug,
                        'parent' => $category_id, // Assign parent category ID
                    ] );

                    if ( !is_wp_error( $new_subcategory ) ) {
                        // Subcategory added successfully
                        $subcategory_id = $new_subcategory['term_id'];

                        // Loop through pieces and add them as sub-subcategories under the subcategory
                        foreach ( $subcategory['PieceList'] as $piece ) {
                            $piece_name     = $piece['PieceName'];
                            $piece_code     = $piece['PieceCode'];
                            // $piece_top_code = $piece_code;
                            $piece_slug     = $piece_name;

                            // Insert piece as a sub-subcategory (nested under subcategory)
                            $new_piece = wp_insert_term( $piece_name, 'product_cat', [
                                'slug'   => $piece_slug,
                                'parent' => $subcategory_id, // Assign parent subcategory ID
                            ] );

                            if ( is_wp_error( $new_piece ) ) {
                                // Handle error when adding pieces
                                echo 'Failed to add piece: ' . $new_piece->get_error_message();
                            }
                        }
                    } else {
                        // Handle error when adding subcategories
                        echo 'Failed to add subcategory: ' . $new_subcategory->get_error_message();
                    }
                }
            } else {
                // Handle error when adding categories
                echo 'Failed to add category: ' . $new_category->get_error_message();
            }
        }

        return 'Categories, subcategories, and pieces added successfully';
    } 
}else {
    return 'No data retrieved from the API';
}

?>