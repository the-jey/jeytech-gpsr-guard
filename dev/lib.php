<?php
/**
 * Dev helpers shared by seed.php and scenario-test.php (never shipped in the ZIP).
 *
 * @package JeyTech\GpsrGuard
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo store: EUR, France.
 */
function jeytech_gpsr_dev_setup_store(): void {
	update_option( 'woocommerce_currency', 'EUR' );
	update_option( 'woocommerce_default_country', 'FR' );
}

/**
 * Brand term with GPSR meta.
 *
 * @param string $name Brand name.
 * @param array  $meta Field => value (manufacturer, address, email, eu_responsible).
 */
function jeytech_gpsr_dev_brand( string $name, array $meta = array() ): int {
	$term = wp_insert_term( $name, 'product_brand' );
	if ( is_wp_error( $term ) ) {
		return 0;
	}
	$term_id = (int) $term['term_id'];
	foreach ( $meta as $field => $value ) {
		update_term_meta( $term_id, 'jeytech_gpsr_' . $field, $value );
	}
	return $term_id;
}

/**
 * Simple published product, optionally attached to a brand with product meta.
 *
 * @param string $name     Name.
 * @param int    $brand_id Brand term ID, 0 for none.
 * @param array  $meta     Field => value (product overrides and warnings).
 */
function jeytech_gpsr_dev_simple( string $name, int $brand_id = 0, array $meta = array() ): int {
	$product = new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_status( 'publish' );
	$product->set_regular_price( '20' );
	$product->save();
	$id = $product->get_id();
	if ( $brand_id > 0 ) {
		wp_set_object_terms( $id, array( $brand_id ), 'product_brand' );
	}
	foreach ( $meta as $field => $value ) {
		update_post_meta( $id, '_jeytech_gpsr_' . $field, $value );
	}
	return $id;
}
