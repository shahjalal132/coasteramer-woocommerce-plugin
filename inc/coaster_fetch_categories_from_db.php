<?php
/**
 * Fetches all categories from the database and adds them to WooCommerce product categories.
 *
 * @return string Output buffer containing any error messages.
 */
function fetch_all_categories_from_db() {
    ob_start();

    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_category';

    // Retrieve category data from the database
    $query      = "SELECT operation_value FROM $table_name";
    $categories = $wpdb->get_results( $query, ARRAY_A );

    foreach ( $categories as $category ) {
        $category_json  = $category['operation_value'];
        $category_array = json_decode( $category_json, true );

        $category_code = $category_array['CategoryCode'];
        $category_name = $category_array['CategoryName'];
        $category_slug = sanitize_title( $category_name );

        // Insert category into WooCommerce product categories
        $new_category = wp_insert_term( $category_name, 'product_cat', [
            'slug'        => $category_slug,
            'description' => $category_code,
        ] );

        if ( !is_wp_error( $new_category ) ) {
            // Category added successfully
            $category_id = $new_category['term_id'];

            // Handle subcategories and pieces insertion here
            if ( isset( $category_array['SubCategoryList'] ) && is_array( $category_array['SubCategoryList'] ) ) {

                foreach ( $category_array['SubCategoryList'] as $subcategory ) {

                    $subcategory_name = $subcategory['SubCategoryName'];
                    $subcategory_code = $subcategory['SubCategoryCode'];
                    $subcategory_slug = sanitize_title( $subcategory_name );

                    // Insert subcategory into WooCommerce under its parent category
                    $new_subcategory = wp_insert_term( $subcategory_name, 'product_cat', [
                        'slug'   => $subcategory_slug,
                        'parent' => $category_id, // Assign parent category ID
                        'description' => $subcategory_code, // Use $piece_code as subcategory description
                    ] );

                    if ( !is_wp_error( $new_subcategory ) ) {
                        // Subcategory added successfully
                        $subcategory_id = $new_subcategory['term_id'];

                        // Loop through pieces and add them as sub-subcategories under the subcategory
                        if ( isset( $subcategory['PieceList'] ) && is_array( $subcategory['PieceList'] ) ) {

                            foreach ( $subcategory['PieceList'] as $piece ) {

                                $piece_name = $piece['PieceName'];
                                $piece_slug = sanitize_title( $piece_name );
                                $piece_code = $piece['PieceCode'];

                                // Insert piece as a sub-subcategory (nested under subcategory)
                                $new_piece = wp_insert_term( $piece_name, 'product_cat', [
                                    'slug'   => $piece_slug,
                                    'parent' => $subcategory_id, // Assign parent subcategory ID
                                    'description' => $piece_code, // Use $piece_code as sub-subcategory description
                                ] );

                                if ( is_wp_error( $new_piece ) ) {
                                    // Handle error when adding pieces
                                    echo 'Failed to add piece: ' . $new_piece->get_error_message();
                                }
                            }
                        }
                    } else {
                        // Handle error when adding subcategories
                        echo 'Failed to add subcategory: ' . $new_subcategory->get_error_message();
                    }
                }
            }
        } else {
            // Handle error when adding categories
            echo 'Failed to add category: ' . $new_category->get_error_message();
        }
    }

    return ob_get_clean();
}

// Shortcode to trigger the category fetch process
add_shortcode( 'fetch_all_categories', 'fetch_all_categories_from_db' );

/**
 * Deletes all WooCommerce product categories.
 */
function delete_woocommerce_category_callback() {
    // Load WooCommerce functions
    if ( class_exists( 'WooCommerce' ) ) {
        $woocommerce = WooCommerce::instance();
    }

    // Get all product categories
    $categories = get_terms( 'product_cat', ['hide_empty' => false] );

    // Loop through each category and delete it
    foreach ( $categories as $category ) {
        wp_delete_term( $category->term_id, 'product_cat' );
    }

    echo 'All product categories have been deleted.';
}

// Shortcode to trigger the category deletion process
add_shortcode( 'delete_woocommerce_category', 'delete_woocommerce_category_callback' );
?>
