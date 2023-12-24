<?php

// Product insert to WooCommerce shortcode
add_shortcode( 'coaster_product_insert_to_woocommerce', 'insert_new_products_to_woocommerce' );

function insert_new_products_to_woocommerce() {
    ob_start();

    global $wpdb;

    // Define table names
    $table_name_products = $wpdb->prefix . 'sync_products';
    $table_name_prices   = $wpdb->prefix . 'sync_price';

    // Retrieve pending products from the database
    $products = $wpdb->get_results( "SELECT * FROM $table_name_products WHERE status = 'pending' LIMIT 1" );

    foreach ( $products as $product ) {

        $product_data = json_decode( $product->operation_value, true );

        // Extract product details from the database record
        $title           = isset( $product_data['Name'] ) ? $product_data['Name'] : '';
        $description     = isset( $product_data['Description'] ) ? $product_data['Description'] : '';
        $sku             = isset( $product_data['ProductNumber'] ) ? $product_data['ProductNumber'] : '';
        $pictures        = isset( $product_data['PictureFullURLs'] ) ? $product_data['PictureFullURLs'] : '';
        $measurementList = isset( $product_data['MeasurementList'] ) ? $product_data['MeasurementList'] : '';
        $boxSize         = isset( $product_data['BoxSize'] ) ? $product_data['BoxSize'] : '';

        // Retrieve the price from the separate table based on product_number
        $price_row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table_name_prices WHERE product_number = %s LIMIT 1", $sku )
        );

        // Check if a price exists for the product
        if ( $price_row ) {

            // Extract price details from the database record
            $base_regular_price = $price_row->map;

            // Calculate the new regular price and sale price with the specified percentages
            $regular_price = round( $base_regular_price * 1.12 ); // Increase by 12%
            $sale_price    = round( $base_regular_price * 1.024 ); // Increase by 2.4%

            // Check if the product already exists
            $existing_product_id = wc_get_product_id_by_sku( $sku );

            if ( $existing_product_id ) {
                // Update existing product
                wp_update_post( [
                    'ID'           => $existing_product_id,
                    'post_title'   => $title,
                    'post_content' => $description,
                    'post_status'  => 'publish',
                    'post_type'    => 'product',
                ] );
            } else {
                // Insert new product
                $product_id = wp_insert_post( [
                    'post_title'   => $title,
                    'post_content' => $description,
                    'post_status'  => 'publish',
                    'post_type'    => 'product',
                ] );

                if ( $product_id ) {
                    // Set product details
                    wp_set_object_terms( $product_id, 'simple', 'product_type' );
                    update_post_meta( $product_id, '_visibility', 'visible' );
                    update_post_meta( $product_id, '_stock_status', 'instock' );
                    update_post_meta( $product_id, '_regular_price', $regular_price );
                    update_post_meta( $product_id, '_sale_price', $sale_price );
                    update_post_meta( $product_id, '_price', $sale_price );
                    update_post_meta( $product_id, '_sku', $sku );

                    // Update the status of the processed product in your database
                    $wpdb->update(
                        $table_name_products,
                        ['status' => 'completed'],
                        ['id' => $product->id]
                    );

                    // Set product images
                    if ( !empty( $pictures ) && is_array( $pictures ) ) {
                        $attachment_ids = [];

                        foreach ( $pictures as $picture_url ) {
                            // Download image and add it to the media library
                            $image_id = media_sideload_image( $picture_url, $product_id, $title );

                            // Check if the image was added successfully
                            if ( !is_wp_error( $image_id ) ) {
                                $attachment_ids[] = $image_id;
                            }
                        }

                        // Set the first image as the product thumbnail
                        if ( !empty( $attachment_ids ) ) {
                            set_post_thumbnail( $product_id, $attachment_ids[0] );
                            update_post_meta( $product_id, '_product_image_gallery', implode( ',', $attachment_ids ) );
                        }

                    }
                }

                // Flush WooCommerce transients
                delete_transient( 'wc_products_onsale' );
                delete_transient( 'wc_var_prices_' . md5( implode( ',', array_keys( $products ) ) ) );
                wc_delete_product_transients();

                // Clear WordPress object cache
                // wp_cache_clear();

                if ( function_exists( 'w3tc_flush_all' ) ) {
                    w3tc_flush_all();
                }

            }
        }
    }

    echo '<h4>Products imported successfully to WooCommerce</h4>';
    return ob_get_clean();
}

?>
