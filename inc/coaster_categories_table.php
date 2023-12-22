<?php 

// Create wp_sync_category Table When Plugin Activated
function coasteramer_db_category_table_create() {

    global $wpdb;

    $table_name      = $wpdb->prefix . 'sync_category';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT,
        operation_type VARCHAR(255) NOT NULL,
        operation_value VARCHAR(255) NOT NULL,
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

?>