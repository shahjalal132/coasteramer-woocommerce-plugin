<?php

add_action( 'rest_api_init', function () {
	register_rest_route( 'coasteramer/v1', '/sync-product', array(
		'methods'  => 'GET',
		'callback' => 'insert_new_products_to_woocommerce_callback',
	) );
} );

/**
 * Inserts new products to WooCommerce.
 * 
 * !this is old code. 
 * *use add_new_product_to_woocommerce_callback() instead. here are update code
 *
 * @return string The success message after importing the products.
 */
function insert_new_products_to_woocommerce_callback() {
	// Start output buffering
	ob_start();

	// Get global $wpdb object
	global $wpdb;

	// Define table names
	$table_name_products  = $wpdb->prefix . 'sync_products';
	$table_name_prices    = $wpdb->prefix . 'sync_price';
	$table_name_inventory = $wpdb->prefix . 'sync_inventory';

	// Retrieve pending products from the database
	$products = $wpdb->get_results( "SELECT * FROM $table_name_products WHERE status = 'pending' LIMIT 1" );

	// Loop through each pending product
	foreach ( $products as $product ) {

		// Decode the JSON data stored in the database
		$product_data = json_decode( $product->operation_value, true );

		// Extract product details from the decoded data
		$title       = isset( $product_data['Name'] ) ? $product_data['Name'] : '';
		$description = isset( $product_data['Description'] ) ? $product_data['Description'] : '';
		$sku         = isset( $product_data['ProductNumber'] ) ? $product_data['ProductNumber'] : '';
		$pictures    = isset( $product_data['PictureFullURLs'] ) ? $product_data['PictureFullURLs'] : '';
		$image_urls  = explode( ',', $pictures );
		// limit images_urls to first 5 images
		$image_urls       = array_slice( $image_urls, 0, 5 );
		$measurementList  = isset( $product_data['MeasurementList'] ) ? $product_data['MeasurementList'] : '';
		$boxSize          = isset( $product_data['BoxSize'] ) ? $product_data['BoxSize'] : '';
		$category_code    = isset( $product_data['CategoryCode'] ) ? $product_data['CategoryCode'] : '';
		$subcategory_code = isset( $product_data['SubCategoryCode'] ) ? $product_data['SubCategoryCode'] : '';
		$piece_code       = isset( $product_data['PieceCode'] ) ? $product_data['PieceCode'] : '';

		// extract category name
		$categories_infos     = getCategoryByCode( $category_code );
		$parent_category_name = $categories_infos['category_name'] ?? '';
		$parent_category_id   = $categories_infos['category_id'] ?? '';

		// extract subcategory information
		$subcategories = get_subcategory_by_parent_category_code( $parent_category_id );

		// Brand name
		$brand_name = 'Coaster';

		// Check if the subcategory exists, and if not, insert it
		$subcategory_name = '';

		foreach ( $subcategories as $subcategory ) {

			if ( $subcategory instanceof WP_Term && $subcategory->description === $subcategory_code ) {
				$subcategory_name = $subcategory->name; // Return the category name if code matches
				$subcategory_id   = $subcategory->term_id;
			}
		}

		// return "parent category: " . $parent_category_name . " and subcategory name: " . $subcategory_name;

		// Retrieve the price from the separate table based on product_number
		$price_row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name_prices WHERE product_number = %s LIMIT 1", $sku )
		);

		// Check if a price exists for the product
		if ( $price_row ) {

			// Extract price details from the database record
			$base_regular_price = $price_row->map;

			// Calculate the new regular price and sale price with specified percentages
			$regular_price = round( $base_regular_price * 1.12 ); // Increase by 12%
			$sale_price    = round( $base_regular_price * 1.024 ); // Increase by 2.4%

			// Check if the product already exists
			$existing_product_id = wc_get_product_id_by_sku( $sku );

			if ( $existing_product_id ) {

				// Update the status of the processed product in your database
				$wpdb->update(
					$table_name_products,
					[ 'status' => 'completed' ],
					[ 'id' => $product->id ]
				);
				// Update existing product
				wp_update_post( [
					'ID'           => $existing_product_id,
					'post_title'   => $title,
					'post_content' => $description,
					'post_status'  => 'publish',
					'post_type'    => 'product',
				] );

				if ( $parent_category_name ) {
					// Update product categories
					wp_set_object_terms( $existing_product_id, $parent_category_name, 'product_cat' );
				}

				if ( $subcategory_name ) {
					// Update product categories
					wp_set_object_terms( $existing_product_id, $subcategory_name, 'product_cat' );
				}

				// Set Brand name to products
				wp_set_object_terms( $existing_product_id, $brand_name, 'brand' );

				// Update the custom field
				update_post_meta( $existing_product_id, '_brand', $brand_name );

			} else {

				// Update the status of the processed product in your database
				$wpdb->update(
					$table_name_products,
					[ 'status' => 'completed' ],
					[ 'id' => $product->id ]
				);
				
				// Insert new product
				$product_id = wp_insert_post( [
					'post_title'   => $title,
					'post_content' => $description,
					'post_status'  => 'publish',
					'post_type'    => 'product',
				] );

				if ( $product_id ) {
					// Set product details
					wp_set_object_terms( $product_id, 'simple', 'product_type' );
					update_post_meta( $product_id, '_visibility', 'visible' );
					update_post_meta( $product_id, '_stock_status', 'instock' );
					update_post_meta( $product_id, '_regular_price', $regular_price );
					update_post_meta( $product_id, '_sale_price', $sale_price );
					update_post_meta( $product_id, '_price', $sale_price );
					update_post_meta( $product_id, '_sku', $sku );

					// Set product categories
					wp_set_object_terms( $product_id, $parent_category_name, 'product_cat' );

					// Set subcategory to products
					wp_set_object_terms( $product_id, $subcategory_name, 'product_cat', true );

					// Set Brand name to products
					wp_set_object_terms( $product_id, $brand_name, 'brand' );

					// Update the custom field
					update_post_meta( $product_id, '_brand', $brand_name );

					// Set product dimensions
					foreach ( $measurementList as $measurement ) {
						$length   = isset( $measurement['Length'] ) ? $measurement['Length'] : '';
						$width    = isset( $measurement['Width'] ) ? $measurement['Width'] : '';
						$height   = isset( $measurement['Height'] ) ? $measurement['Height'] : '';
						$diameter = isset( $measurement['Diameter'] ) ? $measurement['Diameter'] : '';
						$weight   = isset( $measurement['Weight'] ) ? $measurement['Weight'] : '';

						update_post_meta( $product_id, '_length', $length );
						update_post_meta( $product_id, '_width', $width );
						update_post_meta( $product_id, '_height', $height );
						update_post_meta( $product_id, '_diameter', $diameter );
						update_post_meta( $product_id, '_weight', $weight );
					}

					// Set product images
					$specific_image_attached = false; // Flag to track the attachment of the specific image

					foreach ( $image_urls as $image_url ) {
						// Extract image name
						$image_name = basename( $image_url );
						// Get WordPress upload directory
						$upload_dir = wp_upload_dir();

						// Download the image from URL and save it to the upload directory
						$image_data = file_get_contents( $image_url );

						if ( $image_data !== false ) {
							$image_file = $upload_dir['path'] . '/' . $image_name;
							file_put_contents( $image_file, $image_data );

							// Prepare image data to be attached to the product
							$file_path = $upload_dir['path'] . '/' . $image_name;
							$file_name = basename( $file_path );

							// Insert the image as an attachment
							$attachment = [
								'post_mime_type' => mime_content_type( $file_path ),
								'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
								'post_content'   => '',
								'post_status'    => 'inherit',
							];

							$attach_id = wp_insert_attachment( $attachment, $file_path, $product_id );

							// Set specific image as product thumbnail
							if ( strpos( $image_url, '_01x900.jpg' ) !== false && !$specific_image_attached && $attach_id && !is_wp_error( $attach_id ) ) {
								set_post_thumbnail( $product_id, $attach_id );
								$specific_image_attached = true; // Flag the attachment of specific image as product thumbnail
							}

							// Add all images to the product gallery except the specific image
							if ( strpos( $image_url, '_01x900.jpg' ) === false && $attach_id && !is_wp_error( $attach_id ) ) {
								$gallery_ids   = get_post_meta( $product_id, '_product_image_gallery', true );
								$gallery_ids   = explode( ',', $gallery_ids );
								$gallery_ids[] = $attach_id;
								update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery_ids ) );
							}
						}
					}

					// Fetch inventory information from the database
					$total_inventory = $wpdb->get_results( "SELECT * FROM $table_name_inventory WHERE product_number = '$sku'" );

					foreach ( $total_inventory as $inventory ) {
						// Extract relevant information from the database result
						$Product_num   = isset( $inventory->product_number ) ? $inventory->product_number : '';
						$inventory_qty = isset( $inventory->qty_avail ) ? $inventory->qty_avail : '';

						// Update product meta data in WordPress
						update_post_meta( $product_id, '_stock', $inventory_qty );
						update_post_meta( $product_id, '_stock_status', 'instock' );
						update_post_meta( $product_id, '_manage_stock', 'yes' );
					}

					// Mark the inventory update as completed in the database
					$wpdb->update(
						$table_name_inventory,
						[ 'status' => 'completed' ],
						[ 'product_number' => $Product_num ]
					);

				}

				// Flush WooCommerce transients
				delete_transient( 'wc_products_onsale' );
				delete_transient( 'wc_var_prices_' . md5( implode( ',', array_keys( $products ) ) ) );
				wc_delete_product_transients();

				// Clear WordPress object cache
				// wp_cache_clear();

				if ( function_exists( 'w3tc_flush_all' ) ) {
					w3tc_flush_all();
				}
			}
		}
	}

	// Output success message
	echo '<h4>Products imported successfully to WooCommerce</h4>';

	// Return buffered content
	return ob_get_clean();
}

// Product insert to WooCommerce shortcode
add_shortcode( 'coaster_product_insert_to_woocommerce', 'insert_new_products_to_woocommerce_callback' );

?>