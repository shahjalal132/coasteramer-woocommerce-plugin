<?php

require_once COASTERAMER_PLUGIN_PATH . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

add_action( 'rest_api_init', function () {
    register_rest_route( 'coasteramer/v1', '/sync-product', array(
        'methods'  => 'GET',
        'callback' => 'add_new_product_to_woocommerce_callback',
    ) );
} );

/**
 * Inserts new products to WooCommerce.
 * 
 * *This is updated code.
 */

function add_new_product_to_woocommerce_callback() {
    ob_start();

    // Get global $wpdb object
    global $wpdb;

    // Define table names
    $table_name_products   = $wpdb->prefix . 'sync_products';
    $table_name_prices     = $wpdb->prefix . 'sync_price';
    $table_name_inventory  = $wpdb->prefix . 'sync_inventory';
    $table_name_collection = $wpdb->prefix . 'sync_collections';

    // Retrieve pending products from the database
    $products = $wpdb->get_results( "SELECT * FROM $table_name_products WHERE status = 'pending' LIMIT 1" );

    // Loop through each pending product
    foreach ( $products as $product ) {

        // Decode the JSON data stored in the database
        $product_data = json_decode( $product->operation_value, true );

        $website_url     = home_url();
        $consumer_key    = 'ck_b02f9e74a802655803fdb11e55e873cf45fe0cb7';
        $consumer_secret = 'cs_d6eb867be919817cf7ce871e94ffe2c11d6eba39';

        // Extract product details from the decoded data
        $title       = isset( $product_data['Name'] ) ? $product_data['Name'] : '';
        $description = isset( $product_data['Description'] ) ? $product_data['Description'] : '';
        $sku         = isset( $product_data['ProductNumber'] ) ? $product_data['ProductNumber'] : '';
        $pictures    = isset( $product_data['PictureFullURLs'] ) ? $product_data['PictureFullURLs'] : '';
        $image_urls  = explode( ',', $pictures );

        // set image limit to 5
        $image_urls      = array_slice( $image_urls, 0, 5 );
        $measurementList = isset( $product_data['MeasurementList'] ) ? $product_data['MeasurementList'] : '';

        // extract box size array
        $box_sizes       = isset( $product_data['BoxSize'] ) ? $product_data['BoxSize'] : [];
        $boxLength       = isset( $box_sizes['Length'] ) ? $box_sizes['Length'] : '';
        $boxWidth        = isset( $box_sizes['Width'] ) ? $box_sizes['Width'] : '';
        $boxHeight       = isset( $box_sizes['Height'] ) ? $box_sizes['Height'] : '';
        $collection_code = isset( $product_data['CollectionCode'] ) ? $product_data['CollectionCode'] : '';

        $category_code    = isset( $product_data['CategoryCode'] ) ? $product_data['CategoryCode'] : '';
        $subcategory_code = isset( $product_data['SubCategoryCode'] ) ? $product_data['SubCategoryCode'] : '';
        $piece_code       = isset( $product_data['PieceCode'] ) ? $product_data['PieceCode'] : '';

        // Extract additional infestations
        $upc          = isset( $product_data['UPC'] ) ? $product_data['UPC'] : '';
        $MainColor    = isset( $product_data['MainColor'] ) ? $product_data['MainColor'] : '';
        $MainFinish   = isset( $product_data['MainFinish'] ) ? $product_data['MainFinish'] : '';
        $MainMaterial = isset( $product_data['MainMaterial'] ) ? $product_data['MainMaterial'] : '';
        $BoxWeight    = isset( $product_data['BoxWeight'] ) ? $product_data['BoxWeight'] : '';
        $Cubes_value  = isset( $product_data['Cubes'] ) ? $product_data['Cubes'] : null;

        // extract category name
        $categories_infos     = getCategoryByCode( $category_code );
        $parent_category_name = $categories_infos['category_name'] ?? '';
        $parent_category_id   = $categories_infos['category_id'] ?? '';

        // extract subcategory information
        $subcategories = get_subcategory_by_parent_category_code( $parent_category_id );

        // Check if the subcategory exists, and if not, insert it
        $subcategory_name = '';

        foreach ( $subcategories as $subcategory ) {

            if ( $subcategory instanceof WP_Term && $subcategory->description === $subcategory_code ) {
                $subcategory_name = $subcategory->name; // Return the category name if code matches
                $subcategory_id   = $subcategory->term_id;
            }
        }

        // Retrieve the price from the separate table based on product_number
        $price_row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table_name_prices WHERE product_number = %s LIMIT 1", $sku )
        );

        // Extract price details from the database record
        $base_regular_price = $price_row->map;
        $db_regular_price   = $price_row->price;

        // If product map is 0, use $db_regular_price as regular price
        if ( $base_regular_price == 0 ) {
            $base_regular_price = $db_regular_price;
        }

        // Calculate the new regular price and sale price with specified percentages
        $regular_price = round( $base_regular_price * 1.12 ); // Increase by 12%
        $sale_price    = round( $base_regular_price * 1.024 ); // Increase by 2.4%

        // Get collection information
        $collections = $wpdb->get_results( "SELECT * FROM $table_name_collection WHERE collection_code = '$collection_code' LIMIT 1" );

        $collection_name = '';
        if ( !empty( $collections ) && is_array( $collections ) ) {
            foreach ( $collections as $collection ) {
                $collection_name = $collection->collection_name;
            }
        }

        // Brand name
        $brand_name = 'Coaster';
        $tag_name   = $collection_name;

        // Set up the API client with your WooCommerce store URL and credentials
        $client = new Client(
            $website_url,
            $consumer_key,
            $consumer_secret,
            [
                'verify_ssl' => false,
            ]
        );

        // if sku already exists, update the product
        $args = array(
            'post_type'  => 'product',
            'meta_query' => array(
                array(
                    'key'     => '_sku',
                    'value'   => $sku,
                    'compare' => '=',
                ),
            ),
        );

        // Check if the product already exists
        $exiting_products = new WP_Query( $args );

        if ( $exiting_products->have_posts() ) {
            $exiting_products->the_post();

            // get product id
            $product_id = get_the_ID();

            // Update the status of the processed product in your database
            $wpdb->update(
                $table_name_products,
                [ 'status' => 'completed' ],
                [ 'id' => $product->id ]
            );

            // Update the product
            $product_data = [
                'name'        => $title,
                'sku'         => $sku,
                'type'        => 'simple',
                'description' => $description,
                'attributes'  => [
                    [
                        'name'        => 'Collection',
                        'options'     => explode( separator: '|', string: $tag_name ),
                        'position'    => 0,
                        'visible'     => true,
                        'variation'   => true,
                        'is_taxonomy' => false,
                    ],
                ],
            ];

            // update product
            $client->put( 'products/' . $product_id, $product_data );

            if ( $parent_category_name ) {
                // Update product categories
                wp_set_object_terms( $product_id, $parent_category_name, 'product_cat' );
            }

            if ( $subcategory_name ) {
                // Update product categories
                wp_set_object_terms( $product_id, $subcategory_name, 'product_cat' );
            }

            // Set Brand name to products
            wp_set_object_terms( $product_id, $brand_name, 'brand' );
            // wp_set_object_terms( $product_id, $brand_name, 'pwb-brand' ); // client site taxonomy name is pwb-brand

            // Update the custom field
            update_post_meta( $product_id, '_brand', $brand_name );

            // set tag
            wp_set_object_terms( $product_id, $tag_name, 'product_tag', false );

            return "Product Updated Successfully";

        } else {

            // Update the status of the processed product in your database
            $wpdb->update(
                $table_name_products,
                [ 'status' => 'completed' ],
                [ 'id' => $product->id ]
            );

            // Create a new product
            $product_data = [
                'name'        => $title,
                'sku'         => $sku,
                'type'        => 'simple',
                'description' => $description,
                'attributes'  => [
                    [
                        'name'        => 'Collection',
                        'options'     => explode( separator: '|', string: $tag_name ),
                        'position'    => 0,
                        'visible'     => true,
                        'variation'   => true,
                        'is_taxonomy' => false,
                    ],
                ],
            ];

            // Create the product
            $product    = $client->post( 'products', $product_data );
            $product_id = $product->id;

            // Set product categories
            wp_set_object_terms( $product_id, $parent_category_name, 'product_cat' );

            // Set subcategory to products
            wp_set_object_terms( $product_id, $subcategory_name, 'product_cat', true );

            // Set Brand name to products
            wp_set_object_terms( $product_id, $brand_name, 'brand' );

            // Update the custom field
            update_post_meta( $product_id, '_brand', $brand_name );

            // set tag
            wp_set_object_terms( $product_id, $tag_name, 'product_tag', true );

            // Set product dimensions
            foreach ( $measurementList as $measurement ) {
                $length   = isset( $measurement['Length'] ) ? $measurement['Length'] : '';
                $width    = isset( $measurement['Width'] ) ? $measurement['Width'] : '';
                $height   = isset( $measurement['Height'] ) ? $measurement['Height'] : '';
                $diameter = isset( $measurement['Diameter'] ) ? $measurement['Diameter'] : '';
                $weight   = isset( $measurement['Weight'] ) ? $measurement['Weight'] : '';

                update_post_meta( $product_id, '_length', $length );
                update_post_meta( $product_id, '_width', $width );
                update_post_meta( $product_id, '_height', $height );
                update_post_meta( $product_id, '_diameter', $diameter );
                update_post_meta( $product_id, '_weight', $weight );
            }

            // Set product details
            wp_set_object_terms( $product_id, 'simple', 'product_type' );
            update_post_meta( $product_id, '_visibility', 'visible' );
            update_post_meta( $product_id, '_stock_status', 'instock' );
            update_post_meta( $product_id, '_regular_price', $regular_price );
            update_post_meta( $product_id, '_sale_price', $sale_price );
            update_post_meta( $product_id, '_price', $sale_price );
            // Set Brand name to products
            // wp_set_object_terms( $product_id, $brand_name, 'pwb-brand' );

            // set additional information
            update_post_meta( $product_id, '_op_barcode', $upc );
            update_post_meta( $product_id, '_maincolor', $MainColor );
            update_post_meta( $product_id, '_mainmaterial', $MainMaterial );
            update_post_meta( $product_id, '_mainfinish', $MainFinish );
            update_post_meta( $product_id, '_boxweight', $BoxWeight );
            update_post_meta( $product_id, '_cubes', $Cubes_value );

            // update products additional information's box size
            update_post_meta( $product_id, '_jalalboxsize', $boxLength );
            update_post_meta( $product_id, '_jalalboxwidth', $boxWidth );
            update_post_meta( $product_id, '_jalalboxheight', $boxHeight );

            // Set product gallery and thumbnail
            $specific_image_attached = false; // Flag to track the attachment of the specific image

            foreach ( $image_urls as $image_url ) {

                // Extract image name
                $image_name = basename( $image_url );
                // Get WordPress upload directory
                $upload_dir = wp_upload_dir();

                // Download the image from URL and save it to the upload directory
                $image_data = file_get_contents( $image_url );

                if ( $image_data !== false ) {
                    $image_file = $upload_dir['path'] . '/' . $image_name;
                    file_put_contents( $image_file, $image_data );

                    // Prepare image data to be attached to the product
                    $file_path = $upload_dir['path'] . '/' . $image_name;
                    $file_name = basename( $file_path );

                    // Insert the image as an attachment
                    $attachment = [
                        'post_mime_type' => mime_content_type( $file_path ),
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
                        'post_content'   => '',
                        'post_status'    => 'inherit',
                    ];

                    $attach_id = wp_insert_attachment( $attachment, $file_path, $product_id );

                    // Check if the image should be added to the gallery
                    if ( $attach_id && !is_wp_error( $attach_id ) ) {

                        // Add the image to the product gallery
                        $gallery_ids   = get_post_meta( $product_id, '_product_image_gallery', true );
                        $gallery_ids   = explode( ',', $gallery_ids );
                        $gallery_ids[] = $attach_id;
                        update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery_ids ) );

                        // Check if this image should be set as the product thumbnail
                        if (
                            strpos( $image_url, '_001x900.jpg' ) !== false
                            || strpos( $image_url, '_01x900.jpg' ) !== false
                            || strpos( $image_url, '_1x900.jpg' ) !== false
                            || strpos( $image_url, '_21x900.jpg' ) !== false
                            || strpos( $image_url, '-S6x900.jpg' ) !== false
                            || strpos( $image_url, '-S4Lx900.jpg' ) !== false
                            || strpos( $image_url, '_22x900.jpg' ) !== false
                        ) {
                            set_post_thumbnail( $product_id, $attach_id );
                            $specific_image_attached = true; // Flag the attachment of specific image as product thumbnail
                            continue;
                        }
                    }

                    // If specific image condition is not met, set a random image as thumbnail
                    if ( !$specific_image_attached ) {
                        $gallery_ids = get_post_meta( $product_id, '_product_image_gallery', true );
                        $gallery_ids = explode( ',', $gallery_ids );

                        // Check if there are images in the gallery
                        if ( !empty( $gallery_ids ) ) {
                            // Select a random image from the gallery
                            $random_attach_id = $gallery_ids[array_rand( $gallery_ids )];

                            // Set the randomly selected image as the product thumbnail
                            set_post_thumbnail( $product_id, $random_attach_id );
                        }
                    }
                }

                // Fetch inventory information from the database
                $total_inventory = $wpdb->get_results( "SELECT * FROM $table_name_inventory WHERE product_number = '$sku'" );

                foreach ( $total_inventory as $inventory ) {
                    // Extract relevant information from the database result
                    $Product_num   = isset( $inventory->product_number ) ? $inventory->product_number : '';
                    $inventory_qty = isset( $inventory->qty_avail ) ? $inventory->qty_avail : '';

                    // Update product meta data in WordPress
                    update_post_meta( $product_id, '_stock', $inventory_qty );

                    // display out of stock message if stock is 0
                    if ( $inventory_qty <= 0 ) {
                        update_post_meta( $product_id, '_stock_status', 'outofstock' );
                    } else {
                        update_post_meta( $product_id, '_stock_status', 'instock' );
                    }
                    update_post_meta( $product_id, '_manage_stock', 'yes' );
                }

                // Mark the inventory update as completed in the database
                $wpdb->update(
                    $table_name_inventory,
                    [ 'status' => 'completed' ],
                    [ 'product_number' => $Product_num ]
                );
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

    // Output success message
    echo '<h4>Product inserted successfully</h4>';

    return ob_get_clean();
}

add_shortcode( 'add_new_product_to_woocommerce', 'add_new_product_to_woocommerce_callback' );