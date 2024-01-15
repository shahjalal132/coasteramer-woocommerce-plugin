<?php

/**
 * Retrieves the name of a product category based on category code.
 *
 * @param int $category_code The code of the category.
 * @return string|null The name of the category, or null if not found.
 */
function getCategoryByCode( $category_code ) {
	// Get all product categories
	$categories = get_terms( [
		'taxonomy'   => 'product_cat', // The taxonomy of the terms to retrieve
		'hide_empty' => false, // Whether to hide categories with no products
	] );

	// Filter out only parent categories
	$parent_categories = array_filter( $categories, function ($category) {
		return $category->parent == 0; // Parent categories have a parent ID of 0
	} );

	// Loop through parent categories
	foreach ( $parent_categories as $category ) {

		// Check if category code matches
		if ( $category instanceof WP_Term && $category->description === $category_code ) {
			$category_name = $category->name; // Return the category name if code matches
			$category_id   = $category->term_id;

			// Return an array containing both the category name and ID
			return [
				'category_name' => $category_name,
				'category_id'   => $category_id,
			];
		}
	}

	return null; // Return null if category not found
}

function get_subcategory_by_code_callback() {
	// Replace 'your_parent_category_id' with the ID of the parent category you are interested in.
	$parent_category_id = '3378';

	// Get subcategories of the specified parent category
	$subcategories = get_terms( [
		'taxonomy'   => 'product_cat', // WooCommerce product category taxonomy
		'parent'     => $parent_category_id,
		'hide_empty' => false, // Set to true to hide empty categories
	] );

	// Loop through the subcategories
	foreach ( $subcategories as $subcategory ) {
		// Access subcategory properties
		$subcategory_id   = $subcategory->term_id;
		$subcategory_name = $subcategory->name;
		$subcategory_slug = $subcategory->slug;

		// Output or use subcategory information as needed
		echo "Subcategory ID: $subcategory_id, Name: $subcategory_name, Slug: $subcategory_slug <br>";
	}

}

add_shortcode( 'get_subcategory_by_code', 'get_subcategory_by_code_callback' );

function get_subcategory_by_parent_category_code( $parent_category_code ) {

	// Get subcategories of the specified parent category
	$subcategories = get_terms( [
		'taxonomy'   => 'product_cat', // WooCommerce product category taxonomy
		'parent'     => $parent_category_code,
		'hide_empty' => false, // Set to true to hide empty categories
	] );

	return $subcategories;
}

/**
 * Fetches all categories from the database and adds them to WooCommerce product categories.
 *
 * @return string Output buffer containing any error messages.
 */
function fetch_all_categories_from_db() {
	ob_start();

	global $wpdb;
	$table_name = $wpdb->prefix . 'sync_category';

	// Retrieve category data from the database
	$query      = "SELECT operation_value FROM $table_name";
	$categories = $wpdb->get_results( $query, ARRAY_A );

	foreach ( $categories as $category ) {
		$category_json  = $category['operation_value'];
		$category_array = json_decode( $category_json, true );

		$category_code = $category_array['CategoryCode'];
		$category_name = $category_array['CategoryName'];
		$category_slug = sanitize_title( $category_name );

		// Insert category into WooCommerce product categories
		$new_category = wp_insert_term( $category_name, 'product_cat', [
			'slug'        => $category_slug,
			'description' => $category_code,
		] );

		if ( !is_wp_error( $new_category ) ) {
			// Category added successfully
			$category_id = $new_category['term_id'];

			// Handle subcategories and pieces insertion here
			if ( isset( $category_array['SubCategoryList'] ) && is_array( $category_array['SubCategoryList'] ) ) {

				foreach ( $category_array['SubCategoryList'] as $subcategory ) {

					$subcategory_name = $subcategory['SubCategoryName'];
					$subcategory_code = $subcategory['SubCategoryCode'];
					$subcategory_slug = sanitize_title( $subcategory_name );

					// Insert subcategory into WooCommerce under its parent category
					$new_subcategory = wp_insert_term( $subcategory_name, 'product_cat', [
						'slug'        => $subcategory_slug,
						'parent'      => $category_id, // Assign parent category ID
						'description' => $subcategory_code, // Use $piece_code as subcategory description
					] );

					if ( !is_wp_error( $new_subcategory ) ) {
						// Subcategory added successfully
						$subcategory_id = $new_subcategory['term_id'];

						// Loop through pieces and add them as sub-subcategories under the subcategory
						if ( isset( $subcategory['PieceList'] ) && is_array( $subcategory['PieceList'] ) ) {

							foreach ( $subcategory['PieceList'] as $piece ) {

								$piece_name = $piece['PieceName'];
								$piece_slug = sanitize_title( $piece_name );
								$piece_code = $piece['PieceCode'];

								// Insert piece as a sub-subcategory (nested under subcategory)
								$new_piece = wp_insert_term( $piece_name, 'product_cat', [
									'slug'        => $piece_slug,
									'parent'      => $subcategory_id, // Assign parent subcategory ID
									'description' => $piece_code, // Use $piece_code as sub-subcategory description
								] );
							}
						}
					}
				}
				return '<h3>Categories and subcategories have been added successfully!</h3>';
			}
		}
	}

	return ob_get_clean();
}

// Shortcode to trigger the category fetch process
add_shortcode( 'fetch_all_categories', 'fetch_all_categories_from_db' );

/**
 * Deletes all WooCommerce product categories.
 */
function delete_woocommerce_category_callback() {
	// Load WooCommerce functions
	if ( class_exists( 'WooCommerce' ) ) {
		$woocommerce = WooCommerce::instance();
	}

	// Get all product categories
	$categories = get_terms( 'product_cat', [ 'hide_empty' => false ] );

	// Loop through each category and delete it
	foreach ( $categories as $category ) {
		wp_delete_term( $category->term_id, 'product_cat' );
	}

	echo 'All product categories have been deleted.';
}

// Shortcode to trigger the category deletion process
add_shortcode( 'delete_woocommerce_category', 'delete_woocommerce_category_callback' );


// Display brand on single product page
function display_brand_on_product_page() {

	$brand = get_post_meta( get_the_ID(), '_brand', true );

	if ( $brand ) {
		echo '<p class="coaster-brand">Brand: ' . esc_html( $brand ) . '</p>';
	}
}

add_action( 'woocommerce_product_meta_end', 'display_brand_on_product_page' );


function delete_all_woocommerce_products() {
	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
	);

	$products = get_posts( $args );

	foreach ( $products as $product ) {
		wp_delete_post( $product->ID, true ); // Set the second parameter to true to bypass the trash and delete permanently
	}

	echo '<p>All WooCommerce products have been deleted.</p>';
}

add_shortcode( 'delete_all_products', 'delete_all_woocommerce_products' );


function delete_all_trashed_woocommerce_products() {
	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'post_status'    => 'trash',
	);

	$trashed_products = get_posts( $args );

	foreach ( $trashed_products as $product ) {
		wp_delete_post( $product->ID, true ); // Set the second parameter to true to bypass the trash and delete permanently
	}

	echo '<p>All trashed WooCommerce products have been permanently deleted.</p>';
}

add_shortcode( 'delete_products_from_trash', 'delete_all_trashed_woocommerce_products' );


// Display additional information in single product page
function display_custom_information_in_additional_tab( $product_attributes ) {
	global $product;

	// Get your custom product information (replace '_custom_field' with your actual meta key)
	$upc          = get_post_meta( $product->get_id(), '_upc', true );
	$mainColor    = get_post_meta( $product->get_id(), '_maincolor', true );
	$mainMaterial = get_post_meta( $product->get_id(), '_mainmaterial', true );
	$mainFinish   = get_post_meta( $product->get_id(), '_mainfinish', true );
	$boxWeight    = get_post_meta( $product->get_id(), '_boxweight', true );
	$cubes        = get_post_meta( $product->get_id(), '_cubes', true );
	$boxSize      = get_post_meta( $product->get_id(), '_jalalboxsize', true );
	$boxWidth     = get_post_meta( $product->get_id(), '_jalalboxwidth', true );
	$boxHeight    = get_post_meta( $product->get_id(), '_jalalboxheight', true );

	// Add your custom information to the attributes array
	$product_attributes['jalal-addition-information1'] = array(
		'label' => __( 'UPC' ),
		'value' => esc_html( $upc ),
	);

	$product_attributes['jalal-addition-information2'] = array(
		'label' => __( 'Main Color' ),
		'value' => esc_html( $mainColor ),
	);

	$product_attributes['jalal-addition-information3'] = array(
		'label' => __( 'Main Material' ),
		'value' => esc_html( $mainMaterial ),
	);

	$product_attributes['jalal-addition-information4'] = array(
		'label' => __( 'Main Finish' ),
		'value' => esc_html( $mainFinish ),
	);

	$product_attributes['jalal-addition-information5'] = array(
		'label' => __( 'Box Weight' ),
		'value' => esc_html( $boxWeight ),
	);

	$product_attributes['jalal-addition-information6'] = array(
		'label' => __( 'Cubes' ),
		'value' => esc_html( $cubes ),
	);

	$product_attributes['jalal-addition-information7'] = array(
		'label' => __( 'Box Size' ),
		'value' => esc_html( $boxSize ),
	);

	$product_attributes['jalal-addition-information8'] = array(
		'label' => __( 'Box Width' ),
		'value' => esc_html( $boxWidth ),
	);

	$product_attributes['jalal-addition-information9'] = array(
		'label' => __( 'Box Height' ),
		'value' => esc_html( $boxHeight ),
	);


	return $product_attributes;
}

add_filter( 'woocommerce_display_product_attributes', 'display_custom_information_in_additional_tab' );

