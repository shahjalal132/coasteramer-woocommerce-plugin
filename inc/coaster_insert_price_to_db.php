<?php

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
