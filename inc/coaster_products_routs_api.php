<?php

// create an api endpoint for products
add_action( 'rest_api_init', 'coaster_products_api' );

function coaster_products_api() {
    // add new api endpoint to get products from api and add them to database
    register_rest_route( 'coasteramer/v1', '/products', [
        'methods'  => 'GET',
        'callback' => 'coaster_products_api_callback',
    ] );

    // add new api endpoint to get products from database add add them to woocommerce
    register_rest_route( 'coasteramer/v1', '/add-product', [
        'methods'  => 'GET',
        'callback' => 'coaster_add_new_product_from_db_callback',
    ] );
}

// callback function for api endpoint to get products from api and add them to database
function coaster_products_api_callback( $request ) {
    return insert_products_to_db_callback();
}

// callback function for api endpoint to get products from database add add them to woocommerce
function coaster_add_new_product_from_db_callback( $request ) {
    return insert_new_products_to_woocommerce_callback();
}