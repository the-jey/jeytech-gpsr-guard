<?php
/**
 * GPSR fields on the core Brands taxonomy.
 *
 * @package JeyTech\SafetyDataByBrand
 */

namespace JeyTech\SafetyDataByBrand;

defined( 'ABSPATH' ) || exit;

/**
 * Adds manufacturer fields to brand add/edit screens and saves them as term meta.
 */
final class BrandFields {

	public static function register(): void {
		// No taxonomy_exists() guard here: taxonomies register on init, after
		// this runs on plugins_loaded. These hooks only fire on brand screens.
		add_action( Data::TAXONOMY . '_add_form_fields', array( self::class, 'add_fields' ) );
		add_action( Data::TAXONOMY . '_edit_form_fields', array( self::class, 'edit_fields' ), 10, 2 );
		add_action( 'created_' . Data::TAXONOMY, array( self::class, 'save' ) );
		add_action( 'edited_' . Data::TAXONOMY, array( self::class, 'save' ) );
	}

	/**
	 * Fields on the "add brand" screen.
	 */
	public static function add_fields(): void {
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}
		foreach ( Data::FIELDS as $field ) {
			?>
			<div class="form-field">
				<label for="jeytech-sdbb-<?php echo esc_attr( $field ); ?>"><?php echo esc_html( Data::labels()[ $field ] ); ?></label>
				<input type="text" id="jeytech-sdbb-<?php echo esc_attr( $field ); ?>" name="jeytech_sdbb_<?php echo esc_attr( $field ); ?>" value="" />
			</div>
			<?php
		}
	}

	/**
	 * Fields on the "edit brand" screen.
	 *
	 * @param \WP_Term $term Brand being edited.
	 */
	public static function edit_fields( $term ): void {
		if ( ! current_user_can( 'manage_product_terms' ) || ! $term instanceof \WP_Term ) {
			return;
		}
		foreach ( Data::FIELDS as $field ) {
			$value = get_term_meta( $term->term_id, Data::brand_key( $field ), true );
			?>
			<tr class="form-field">
				<th scope="row"><label for="jeytech-sdbb-<?php echo esc_attr( $field ); ?>"><?php echo esc_html( Data::labels()[ $field ] ); ?></label></th>
				<td><input type="text" id="jeytech-sdbb-<?php echo esc_attr( $field ); ?>" name="jeytech_sdbb_<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" /></td>
			</tr>
			<?php
		}
	}

	/**
	 * Saves the brand fields. Runs after core verified its own form nonce.
	 */
	public static function save( int $term_id ): void {
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}
		foreach ( Data::FIELDS as $field ) {
			$key = 'jeytech_sdbb_' . $field;
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Core verified the term form nonce before this hook.
			$raw = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
			update_term_meta( $term_id, Data::brand_key( $field ), Data::sanitize_field( $field, $raw ) );
		}
	}
}
