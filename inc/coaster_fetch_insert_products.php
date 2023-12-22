<?php

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

?>