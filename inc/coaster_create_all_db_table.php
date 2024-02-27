<?php

// Create wp_sync_category Table When Plugin Activated
function coasteramer_db_category_table_create() {

    global $wpdb;

    $table_name      = $wpdb->prefix . 'sync_category';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT,
        operation_type VARCHAR(255) NOT NULL,
        operation_value TEXT NOT NULL,
        status VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

// Remove wp_sync_category Table when plugin deactivated
function coasteramer_db_category_table_remove() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sync_category';
    $sql        = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query( $sql );
}

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

// Create wp_sync_products Table When Plugin Activated
function coasteramer_db_price_table_create() {

    global $wpdb;

    $table_name      = $wpdb->prefix . 'sync_price';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT,
        operation_type VARCHAR(255) NOT NULL,
        product_number VARCHAR(255) NOT NULL,
        price VARCHAR(255) NOT NULL,
        map VARCHAR(255) NOT NULL,
        status VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

// Remove wp_sync_products Table when plugin deactivated
function coasteramer_db_price_table_remove() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sync_price';
    $sql        = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query( $sql );
}

// Create wp_sync_products Table When Plugin Activated
function coasteramer_db_products_table_create() {

    global $wpdb;

    $table_name      = $wpdb->prefix . 'sync_products';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT,
        operation_type VARCHAR(255) NOT NULL,
        operation_value TEXT NOT NULL,
        status VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

// Remove wp_sync_products Table when plugin deactivated
function coasteramer_db_products_table_remove() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sync_products';
    $sql        = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query( $sql );
}

// Create wp_sync_products Table When Plugin Activated
function coasteramer_db_collection_table_create() {

    global $wpdb;

    $table_name      = $wpdb->prefix . 'sync_collections';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT,
        collection_code VARCHAR(255),
        collection_name VARCHAR(255),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

// Remove wp_sync_products Table when plugin deactivated
function coasteramer_db_collection_table_remove() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sync_collections';
    $sql        = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query( $sql );
}