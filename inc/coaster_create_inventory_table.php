<?php
/**
 * Plugin activation hook to create the sync_inventory table.
 */
function coaster_sync_inventory_table() {
    global $wpdb;

    // Set the table name with the WordPress prefix
    $table_name = $wpdb->prefix . 'sync_inventory';

    // Define charset and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Check if the table already exists before creating it
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        // SQL query to create the sync_inventory table
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            operation_type varchar(255) NOT NULL,
            product_number VARCHAR(255) NOT NULL,
            qty_avail VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Include WordPress upgrade file for dbDelta function
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Execute the SQL query using dbDelta to create the table
        dbDelta( $sql );
    }
}

/**
 * Plugin deactivation hook to drop the sync_inventory table.
 */
function inventory_table_deactivation() {
    global $wpdb;

    // Set the table name with the WordPress prefix
    $table_name = $wpdb->prefix . 'sync_inventory';

    // Check if the table exists before attempting to drop it
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
        // SQL query to drop the sync_inventory table
        $sql = "DROP TABLE $table_name;";

        // Execute the SQL query to drop the table
        $wpdb->query( $sql );
    }
}