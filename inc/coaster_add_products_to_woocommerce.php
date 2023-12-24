<?php

// Product insert to WooCommerce shortcode
add_shortcode( 'coaster_product_insert_to_woocommerce', 'insert_new_products_to_woocommerce' );

function insert_new_products_to_woocommerce() {
    ob_start();

    global $wpdb;
    $table_name_products = $wpdb->prefix . 'sync_products';
    $table_name_prices   = $wpdb->prefix . 'sync_price';

    $products = $wpdb->get_results( "SELECT * FROM $table_name_products WHERE status = 'pending' LIMIT 1" );

    foreach ( $products as $product ) {
        // Retrieve data from the database record
        $product_data = json_decode( $product->operation_value, true );

        // Extract product details from the database record
        $title        = isset( $product_data['Name'] ) ? $product_data['Name'] : '';
        $description  = isset( $product_data['Description'] ) ? $product_data['Description'] : '';
        $sku          = isset( $product_data['ProductNumber'] ) ? $product_data['ProductNumber'] : '';
        $stock        = isset( $product_data['stock'] ) ? $product_data['stock'] : 0;
        $manage_stock = $stock > 0 ? 'yes' : 'no';

        // Retrieve the price from the separate table based on product_number
        $price_row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table_name_prices WHERE product_number = %s LIMIT 1", $sku )
        );

        // echo '<pre>';
        // print_r( $price_row );
        // wp_die();

        // Check if a price exists for the product
        if ( $price_row ) {
            $price = $price_row->price;

            // echo $price;
            // wp_die(  );

            // Check if the product already exists
            $existing_product = wc_get_product_id_by_sku( $sku );

            $query = new WP_Query( $existing_product );

            if ( $query->have_posts() ) {
                $product_id = $query->posts[0]->ID;
                wp_update_post( [
                    'ID'           => $existing_product,
                    'post_title'   => $title,
                    'post_content' => $description,
                    'post_status'  => 'publish',
                    'post_type'    => 'product',
                ] );
            } else {
                $product_id = wp_insert_post( [
                    'post_title'   => $title,
                    'post_content' => $description,
                    'post_status'  => 'publish',
                    'post_type'    => 'product',
                ] );

                if ( $product_id ) {
                    wp_set_object_terms( $product_id, 'simple', 'product_type' );
                    update_post_meta( $product_id, '_visibility', 'visible' );
                    update_post_meta( $product_id, '_stock_status', 'instock' );
                    update_post_meta( $product_id, '_regular_price', $price );
                    update_post_meta( $product_id, '_sku', $sku );
                    update_post_meta( $product_id, '_manage_stock', $manage_stock );
                    update_post_meta( $product_id, '_stock', $stock );

                    // Update the status of the processed product in your database
                    $wpdb->update(
                        $table_name_products,
                        ['status' => 'completed'],
                        ['id' => $product->id]
                    );
                }
            }
        }
    }

    echo '<h4>Products imported successfully to WooCommerce</h4>';
    return ob_get_clean();
}

?>
