<?php
/**
 * GPSR data model: brand term meta, product overrides, warnings.
 *
 * @package JeyTech\SafetyDataByBrand
 */

namespace JeyTech\SafetyDataByBrand;

use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions, sanitization and brand-to-product inheritance.
 * Variations resolve through their parent: GPSR fields only exist on parents.
 */
final class Data {

	const TAXONOMY = 'product_brand';

	const FIELDS = array( 'manufacturer', 'address', 'email', 'eu_responsible' );

	const MAX_LENGTHS = array(
		'manufacturer'    => 200,
		'address'         => 300,
		'email'           => 100,
		'eu_responsible'  => 300,
		'warnings'        => 2000,
	);

	/**
	 * Human labels, translated at display time.
	 *
	 * @return array<string, string>
	 */
	public static function labels(): array {
		return array(
			'manufacturer'   => __( 'Manufacturer', 'jeytech-safety-data-by-brand' ),
			'address'        => __( 'Manufacturer address', 'jeytech-safety-data-by-brand' ),
			'email'          => __( 'Manufacturer email', 'jeytech-safety-data-by-brand' ),
			'eu_responsible' => __( 'EU responsible person', 'jeytech-safety-data-by-brand' ),
			'warnings'       => __( 'Safety warnings', 'jeytech-safety-data-by-brand' ),
		);
	}

	/**
	 * Term meta key for a brand field.
	 */
	public static function brand_key( string $field ): string {
		return 'jeytech_sdbb_' . $field;
	}

	/**
	 * Post meta key for a product field. Leading underscore keeps it out of
	 * the Custom Fields box.
	 */
	public static function product_key( string $field ): string {
		return '_jeytech_sdbb_' . $field;
	}

	/**
	 * Sanitizes one field value.
	 *
	 * @param mixed $value Raw value.
	 */
	public static function sanitize_field( string $field, $value ): string {
		$text = is_string( $value ) ? $value : '';
		if ( 'email' === $field ) {
			return sanitize_email( $text );
		}
		$max = self::MAX_LENGTHS[ $field ] ?? 200;
		$text = 'warnings' === $field ? sanitize_textarea_field( $text ) : sanitize_text_field( $text );
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $text, 0, $max );
		}
		return substr( $text, 0, $max );
	}

	/**
	 * Parent ID for variations, the ID itself otherwise.
	 */
	public static function canonical_id( int $product_id ): int {
		$product = wc_get_product( $product_id );
		if ( $product instanceof WC_Product && $product->get_parent_id() > 0 ) {
			return (int) $product->get_parent_id();
		}
		return $product_id;
	}

	/**
	 * First brand term ID of a product, 0 when none. Documented: when several
	 * brands are set, the first one wins.
	 */
	public static function brand_for( int $product_id ): int {
		$product_id = self::canonical_id( $product_id );
		if ( $product_id <= 0 || ! taxonomy_exists( self::TAXONOMY ) ) {
			return 0;
		}
		$terms = wp_get_object_terms( $product_id, self::TAXONOMY, array( 'fields' => 'ids', 'number' => 1 ) );
		if ( is_wp_error( $terms ) || ! is_array( $terms ) || ! $terms ) {
			return 0;
		}
		return (int) $terms[0];
	}

	/**
	 * Resolves the GPSR data of a product: product overrides first, then the
	 * brand values. Warnings only exist at product level.
	 *
	 * @return array{manufacturer: string, address: string, email: string, eu_responsible: string, warnings: string, inherited_from: int}
	 */
	public static function for_product( int $product_id ): array {
		$product_id = self::canonical_id( $product_id );
		$brand_id   = self::brand_for( $product_id );
		$data       = array( 'inherited_from' => 0 );
		foreach ( self::FIELDS as $field ) {
			$value = $product_id > 0 ? (string) get_post_meta( $product_id, self::product_key( $field ), true ) : '';
			if ( '' === $value && $brand_id > 0 ) {
				$value = (string) get_term_meta( $brand_id, self::brand_key( $field ), true );
				if ( '' !== $value ) {
					$data['inherited_from'] = $brand_id;
				}
			}
			$data[ $field ] = $value;
		}
		$data['warnings'] = $product_id > 0 ? (string) get_post_meta( $product_id, self::product_key( 'warnings' ), true ) : '';

		/**
		 * Filters the resolved GPSR data of a product. Extensions may complete it.
		 *
		 * @param array $data       Resolved fields.
		 * @param int   $product_id Canonical (parent) product ID.
		 */
		return (array) apply_filters( 'jeytech_sdbb_data', $data, $product_id );
	}

	/**
	 * Whether any GPSR content exists for this product.
	 */
	public static function has_data( int $product_id ): bool {
		$data = self::for_product( $product_id );
		foreach ( array_merge( self::FIELDS, array( 'warnings' ) ) as $field ) {
			if ( '' !== $data[ $field ] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Core fields still empty for this product. Warnings are informational:
	 * they only apply to products that need them.
	 *
	 * @return string[]
	 */
	public static function missing_for( int $product_id ): array {
		$data    = self::for_product( $product_id );
		$missing = array();
		foreach ( self::FIELDS as $field ) {
			if ( '' === $data[ $field ] ) {
				$missing[] = $field;
			}
		}
		return $missing;
	}
}
