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
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}

	/**
	 * Load the palette on the brand forms that contain our fields.
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || Data::TAXONOMY !== $screen->taxonomy ) {
			return;
		}
		wp_enqueue_style( 'jeytech-sdbb-admin', plugins_url( 'assets/admin.css', JEYTECH_SDBB_FILE ), array(), JEYTECH_SDBB_VERSION );
	}

	/**
	 * Fields on the "add brand" screen.
	 */
	public static function add_fields(): void {
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}
		self::render_fields();
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
		?>
		<tr class="form-field"><td colspan="2"><?php self::render_fields( (int) $term->term_id ); ?></td></tr>
		<?php
	}

	/**
	 * A scoped panel shared by the add and edit forms.
	 */
	private static function render_fields( int $term_id = 0 ): void {
		?>
		<div class="wrap jeytech-admin jeytech-sdbb-brand-fields">
			<h2><?php esc_html_e( 'Safety Data by Brand', 'jeytech-safety-data-by-brand' ); ?></h2>
			<p><?php esc_html_e( 'Set the manufacturer data once per brand: every product inherits it. Overrides and safety warnings live on the product.', 'jeytech-safety-data-by-brand' ); ?></p>
			<div class="jeytech-sdbb-fields">
				<?php foreach ( Data::FIELDS as $field ) : ?>
					<?php $value = $term_id > 0 ? get_term_meta( $term_id, Data::brand_key( $field ), true ) : ''; ?>
					<div class="form-field">
						<label for="jeytech-sdbb-<?php echo esc_attr( $field ); ?>"><?php echo esc_html( Data::labels()[ $field ] ); ?></label>
						<input type="text" id="jeytech-sdbb-<?php echo esc_attr( $field ); ?>" name="jeytech_sdbb_<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" />
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
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
