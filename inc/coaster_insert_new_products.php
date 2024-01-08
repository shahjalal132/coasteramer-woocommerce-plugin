<?php
require_once COASTERAMER_PLUGIN_PATH . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

function add_new_product_to_woocommerce_callback() {
    ob_start();

    

    return ob_get_clean();
}

add_shortcode( 'add_new_product_to_woocommerce', 'add_new_product_to_woocommerce_callback' );

?>