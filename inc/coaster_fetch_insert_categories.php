<?php

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

?>