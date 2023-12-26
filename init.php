<?php

/*
 * Plugin Name:       coasteramer-api
 * Plugin URI:        #
 * Description:       Coasteramer API
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Imjol
 * Author URI:        https://imjol.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 */

if ( !defined( 'WPINC' ) ) {
    die;
}

// Define plugin path
if ( !defined( 'COASTERAMER_PLUGIN_PATH' ) ) {
    define( 'COASTERAMER_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Define plugin uri
if ( !defined( 'COASTERAMER_PLUGIN_URI' ) ) {
    define( 'COASTERAMER_PLUGIN_URI', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}

// Create wp_sync_products db table when plugin activate
register_activation_hook( __FILE__, 'coasteramer_db_products_table_create' );

// Remove wp_sync_products db table when plugin deactivate
register_deactivation_hook( __FILE__, 'coasteramer_db_products_table_remove' );

// Create wp_sync_category db table when plugin activate
register_activation_hook( __FILE__, 'coasteramer_db_category_table_create' );

// Remove wp_sync_category db table when plugin deactivate
register_deactivation_hook( __FILE__, 'coasteramer_db_category_table_remove' );

// Create wp_sync_price db table when plugin activate
register_activation_hook( __FILE__, 'coasteramer_db_price_table_create' );

// Remove wp_sync_price db table when plugin deactivate
register_deactivation_hook( __FILE__, 'coasteramer_db_price_table_remove' );

// Create wp_sync_inventory db table when plugin activate
register_activation_hook( __FILE__, 'coaster_sync_inventory_table' );

// Remove wp_sync_inventory db table when plugin deactivate
register_deactivation_hook( __FILE__, 'inventory_table_deactivation' );

// Including requirements files
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_products_table.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_categories_table.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_create_price_table.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_fetch_insert_products.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_fetch_insert_categories.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_add_products_to_woocommerce.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_insert_price_to_db.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_fetch_categories_from_db.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_fetch_parent_cat_from_woo.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_create_inventory_table.php';
require_once COASTERAMER_PLUGIN_PATH . '/inc/coaster_fetch_inventory_from_api_and_insert.php';

?>