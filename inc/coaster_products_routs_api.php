<?php

// create an api endpoint for products
add_action( 'rest_api_init', 'coaster_products_api' );

function coaster_products_api() {

    // add new api endpoint to get products from api and add them to database
    register_rest_route( 'coasteramer/v1', '/products', [
        'methods'  => 'GET',
        'callback' => 'coaster_products_api_callback',
    ] );

    // api endpoint for insert inventory to database
    register_rest_route( 'coasteramer/v1', '/inventory', [
        'methods'  => 'GET',
        'callback' => 'coaster_inventory_api_callback',
    ] );

    // api endpoint for insert price to database
    register_rest_route( 'coasteramer/v1', '/price', [
        'methods'  => 'GET',
        'callback' => 'coaster_price_api_callback',
    ] );

}

function coaster_products_api_callback( $request ) {
    return insert_products_to_db_callback();
}

function coaster_inventory_api_callback() {
    return coaster_inventory_shortcode();
}

function coaster_price_api_callback() {
    return insert_price_to_db_callback();
}