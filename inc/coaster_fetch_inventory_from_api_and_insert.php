<?php

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
