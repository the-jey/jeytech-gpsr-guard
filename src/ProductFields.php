<?php
/**
 * GPSR tab on the product edit screen: overrides and warnings.
 *
 * @package JeyTech\GpsrGuard
 */

namespace JeyTech\GpsrGuard;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a product data tab with per-product overrides. Empty fields inherit
 * the brand values; variations always use their parent's data.
 */
final class ProductFields {

	public static function register(): void {
		add_filter( 'woocommerce_product_data_tabs', array( self::class, 'tab' ) );
		add_action( 'woocommerce_product_data_panels', array( self::class, 'panel' ) );
		add_action( 'woocommerce_process_product_meta', array( self::class, 'save' ) );
	}

	/**
	 * Registers the tab.
	 *
	 * @param array $tabs Product data tabs.
	 */
	public static function tab( array $tabs ): array {
		$tabs['jeytech_gpsr'] = array(
			'label'    => __( 'GPSR', 'jeytech-gpsr-guard' ),
			'target'   => 'jeytech_gpsr_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 80,
		);
		return $tabs;
	}

	/**
	 * Renders the fields.
	 */
	public static function panel(): void {
		global $post;
		$product_id = $post instanceof \WP_Post ? (int) $post->ID : 0;
		?>
		<div id="jeytech_gpsr_data" class="panel woocommerce_options_panel hidden">
			<p class="form-field"><?php esc_html_e( 'Leave a field empty to use the brand value. Variations use their parent product.', 'jeytech-gpsr-guard' ); ?></p>
			<?php
			foreach ( Data::FIELDS as $field ) {
				woocommerce_wp_text_input( array(
					'id'          => Data::product_key( $field ),
					'label'       => Data::labels()[ $field ],
					'value'       => $product_id > 0 ? (string) get_post_meta( $product_id, Data::product_key( $field ), true ) : '',
					'desc_tip'    => true,
					'description' => __( 'Empty: inherit from the brand.', 'jeytech-gpsr-guard' ),
				) );
			}
			woocommerce_wp_textarea_input( array(
				'id'    => Data::product_key( 'warnings' ),
				'label' => Data::labels()['warnings'],
				'value' => $product_id > 0 ? (string) get_post_meta( $product_id, Data::product_key( 'warnings' ), true ) : '',
			) );
			?>
		</div>
		<?php
	}

	/**
	 * Saves the fields. Runs after WooCommerce verified its own product nonce.
	 */
	public static function save( int $product_id ): void {
		if ( ! current_user_can( 'edit_product', $product_id ) ) {
			return;
		}
		foreach ( array_merge( Data::FIELDS, array( 'warnings' ) ) as $field ) {
			$raw = wp_unslash( $_POST[ Data::product_key( $field ) ] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verified the product form nonce before this hook.
			update_post_meta( $product_id, Data::product_key( $field ), Data::sanitize_field( $field, $raw ) );
		}
	}
}
