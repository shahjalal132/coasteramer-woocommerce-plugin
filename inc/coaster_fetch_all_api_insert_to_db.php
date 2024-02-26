<?php

// fetch categories from api
function get_api_category() {
    $curl = curl_init();
    curl_setopt_array( $curl, [
        CURLOPT_URL            => 'http://api.coasteramer.com/api/product/GetCategoryList',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'keycode: 46FE2CA20629404CA246EF3A98',
        ],
    ] );

    $response = curl_exec( $curl );

    curl_close( $curl );
    return $response;
}

// insert categories to database
function insert_category_to_db_callback() {
    ob_start();
    $api_response = get_api_category();

    $categories = json_decode( $api_response, true );

    // Insert to database
    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_category';
    $wpdb->query( "TRUNCATE TABLE $table_name" );

    foreach ( $categories as $category ) {
        $category_data = json_encode( $category );
        $wpdb->insert(
            $table_name,
            [
                'operation_type'  => 'category_create',
                'operation_value' => $category_data,
                'status'          => 'pending',
            ]
        );
    }

    echo '<h4>Categories inserted successfully</h4>';

    return ob_get_clean();
}
add_shortcode( 'insert_categories', 'insert_category_to_db_callback' );

// Fetch all products from API
function coaster_fetch_all_products() {
    $curl = curl_init();

    curl_setopt_array( $curl, [
        CURLOPT_URL            => 'http://api.coasteramer.com/api/Product/GetProductList',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'keycode: 46FE2CA20629404CA246EF3A98',
        ],
    ] );

    $response = curl_exec( $curl );

    curl_close( $curl );
    return $response;
}

// insert products to database
function insert_products_to_db_callback() {
    ob_start();
    $api_response = coaster_fetch_all_products();
    $products     = json_decode( $api_response, true );

    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_products';
    $wpdb->query( "TRUNCATE TABLE $table_name" );

    // Insert to database
    foreach ( $products as $product ) {
        $product_data = json_encode( $product );
        $wpdb->insert(
            $table_name,
            [
                'operation_type'  => 'product_create',
                'operation_value' => $product_data,
                'status'          => 'pending',
            ]
        );
    }

    echo '<h4>Products inserted successfully</h4>';

    return ob_get_clean();
}
add_shortcode( 'insert_products', 'insert_products_to_db_callback' );

/**
 * Fetches inventory data from the Coaster Amer API.
 *
 * @return string API response
 */
function get_inventory_api() {
    $curl = curl_init();
    curl_setopt_array( $curl, [
        CURLOPT_URL            => 'http://api.coasteramer.com/api/product/GetinventoryList',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'keycode: 46FE2CA20629404CA246EF3A98',
        ],
    ] );

    $response = curl_exec( $curl );

    curl_close( $curl );
    return $response;
}

/**
 * Shortcode function to import inventory data from the Coaster Amer API.
 *
 * @param array $atts Shortcode attributes (unused)
 * @return string HTML message indicating the result of the import
 */
function coaster_inventory_shortcode( $atts = [] ) {
    ob_start();

    // Get inventory data from the API
    $api_response = get_inventory_api();
    $inventory    = json_decode( $api_response, true );

    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_inventory';

    // Clear existing data in the sync_inventory table
    $wpdb->query( "TRUNCATE TABLE $table_name" );

    if ( isset( $inventory[0]['InventoryList'] ) && is_array( $inventory[0]['InventoryList'] ) ) {
        foreach ( $inventory[0]['InventoryList'] as $item ) {

            // Insert data into the database
            $wpdb->insert(
                $table_name,
                [
                    'operation_type' => 'stock_create',
                    'product_number' => $item['ProductNumber'],
                    'qty_avail'      => $item['QtyAvail'],
                    // 'incoming'       => $incoming,
                    'status'         => 'pending',
                ]
            );
        }
        echo '<h4>Inventory imported successfully</h4>';
    } else {
        echo '<h4>No or invalid Inventory data received</h4>';
    }

    return ob_get_clean();
}

// Register the shortcode
add_shortcode( 'coaster_inventory_api', 'coaster_inventory_shortcode' );

// fetch price from api
function get_price_from_api() {
    $curl = curl_init();

    curl_setopt_array( $curl, [
        CURLOPT_URL            => 'http://api.coasteramer.com/api/product/GetPriceList',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'keycode: 46FE2CA20629404CA246EF3A98',
        ],
    ] );

    $response = curl_exec( $curl );

    curl_close( $curl );
    return $response;
}

// insert price to database
function insert_price_to_db_callback() {
    ob_start();
    $api_response = get_price_from_api();

    $prices = json_decode( $api_response, true );

    // $priceCode = $prices[0]['PriceCode'];
    $price = $prices[0]['PriceList'][0]['Price'];

    $json_price = json_encode( $price );

    // Insert to database
    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_price';
    $wpdb->query( "TRUNCATE TABLE $table_name" );

    foreach ( $prices as $price ) {

        $price_data = $price['PriceList'];

        foreach ( $price_data as $value ) {
            $wpdb->insert(
                $table_name,
                [
                    'operation_type' => 'price_create',
                    'product_number' => $value['ProductNumber'],
                    'price'          => $value['Price'],
                    'map'            => $value['MAP'],
                    'status'         => 'pending',
                ]
            );
        }
    }

    echo '<h4>Price inserted successfully</h4>';

    return ob_get_clean();
}
add_shortcode( 'insert_price_to_db_shortcode', 'insert_price_to_db_callback' );

// fetch collection from api
function get_collection_from_api() {
    $curl = curl_init();

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL            => 'http://api.coasteramer.com/api/product/GetCollectionList',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'keycode: 46FE2CA20629404CA246EF3A98',
            ),
        )
    );


    $response = curl_exec( $curl );

    curl_close( $curl );
    return $response;
}

// insert price to database
function insert_collection_to_db_callback() {
    ob_start();

    $api_response = get_collection_from_api();

    $collections = json_decode( $api_response, true );

    // Insert to database
    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_collections';
    $wpdb->query( "TRUNCATE TABLE $table_name" );

    foreach ( $collections as $collection ) {
        // collection json data
        $collection_data = json_encode( $collection );

        // insert to database
        $wpdb->insert(
            $table_name,
            [
                'operation_type'  => 'collection_create',
                'operation_value' => $collection_data,
            ]
        );
    }

    echo '<h4>Price inserted successfully</h4>';

    return ob_get_clean();
}
add_shortcode( 'insert_collection_to_db_shortcode', 'insert_collection_to_db_callback' );