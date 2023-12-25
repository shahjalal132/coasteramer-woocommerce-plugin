<?php

// Product insert to WooCommerce shortcode
add_shortcode( 'coaster_product_insert_to_woocommerce', 'insert_new_products_to_woocommerce' );

function insert_new_products_to_woocommerce() {
    // Start output buffering
    ob_start();

    // Get global $wpdb object
    global $wpdb;

    // Define table names
    $table_name_products = $wpdb->prefix . 'sync_products';
    $table_name_prices   = $wpdb->prefix . 'sync_price';

    // Retrieve pending products from the database
    $products = $wpdb->get_results( "SELECT * FROM $table_name_products WHERE status = 'pending' LIMIT 1" );

    // Loop through each pending product
    foreach ( $products as $product ) {

        // Decode the JSON data stored in the database
        $product_data = json_decode( $product->operation_value, true );

        // Extract product details from the decoded data
        $title           = isset( $product_data['Name'] ) ? $product_data['Name'] : '';
        $description     = isset( $product_data['Description'] ) ? $product_data['Description'] : '';
        $sku             = isset( $product_data['ProductNumber'] ) ? $product_data['ProductNumber'] : '';
        $pictures        = isset( $product_data['PictureFullURLs'] ) ? $product_data['PictureFullURLs'] : '';
        $image_urls      = explode( ',', $pictures );
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

            // Calculate the new regular price and sale price with specified percentages
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
                    foreach ( $image_urls as $image_url ) {
                        // Extract image name
                        $image_name = basename( $image_url );
                        // Get WordPress upload directory
                        $upload_dir = wp_upload_dir();

                        // Download the image from URL and save it to the upload directory
                        $image_data = file_get_contents( $image_url );
                        $image_file = $upload_dir['path'] . '/' . $image_name;
                        file_put_contents( $image_file, $image_data );

                        // Prepare image data to be attached to the product
                        $file_path = $upload_dir['path'] . '/' . $image_name;
                        $file_name = basename( $file_path );

                        $attachment = [
                            'post_mime_type' => mime_content_type( $file_path ),
                            'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
                            'post_content'   => '',
                            'post_status'    => 'inherit',
                        ];

                        // Insert the image as an attachment
                        $attach_id = wp_insert_attachment( $attachment, $file_path, $product_id );

                        // Add image to the product gallery
                        if ( $attach_id && !is_wp_error( $attach_id ) ) {
                            // Set the product image
                            set_post_thumbnail( $product_id, $attach_id );

                            // Set gallery
                            $gallery_ids = get_post_meta( $product_id, '_product_image_gallery', true );
                            $gallery_ids = explode( ',', $gallery_ids );

                            // Add the new image to the existing gallery
                            $gallery_ids[] = $attach_id;

                            // Update the product gallery
                            update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery_ids ) );
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

    // Output success message
    echo '<h4>Products imported successfully to WooCommerce</h4>';

    // Return buffered content
    return ob_get_clean();
}
?>
