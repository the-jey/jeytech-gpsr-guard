<?php
/**
 * Displays the safety section on product pages.
 *
 * @package JeyTech\GpsrGuard\Frontend
 */

namespace JeyTech\GpsrGuard\Frontend;

use JeyTech\GpsrGuard\Data;
use JeyTech\GpsrGuard\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Classic themes get a product tab; block themes get the section appended to
 * the content, since the Single Product block renders no classic tabs.
 */
final class Safety {

	public static function register(): void {
		add_filter( 'woocommerce_product_tabs', array( self::class, 'tab' ) );
		add_filter( 'the_content', array( self::class, 'append' ), 20 );
	}

	/**
	 * Whether the section may be shown for this product.
	 */
	public static function should_display( int $product_id ): bool {
		if ( ! Settings::is_enabled() || ! Data::has_data( $product_id ) ) {
			return false;
		}

		/**
		 * Filters the safety section display. Extensions may exclude products.
		 *
		 * @param bool $display    Whether to display.
		 * @param int  $product_id Canonical (parent) product ID.
		 */
		return (bool) apply_filters( 'jeytech_gpsr_should_display', true, Data::canonical_id( $product_id ) );
	}

	/**
	 * Section title, filterable for extensions.
	 */
	public static function title(): string {
		/**
		 * Filters the safety section title.
		 *
		 * @param string $title Default title.
		 */
		return (string) apply_filters( 'jeytech_gpsr_tab_title', __( 'Product Safety', 'jeytech-gpsr-guard' ) );
	}

	/**
	 * Adds the tab on classic product pages.
	 *
	 * @param array $tabs Product tabs.
	 */
	public static function tab( array $tabs ): array {
		$product_id = get_the_ID();
		if ( ! $product_id || ! self::should_display( (int) $product_id ) ) {
			return $tabs;
		}
		$tabs['jeytech_gpsr'] = array(
			'title'    => self::title(),
			'priority' => 40,
			'callback' => array( self::class, 'render' ),
		);
		return $tabs;
	}

	/**
	 * Tab callback.
	 */
	public static function render(): void {
		echo self::section_html( Data::canonical_id( (int) get_the_ID() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built escaped below.
	}

	/**
	 * Appends the section on block themes, which render no classic tabs.
	 */
	public static function append( string $content ): string {
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
			return $content;
		}
		if ( ! is_product() || ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}
		$product_id = Data::canonical_id( (int) get_the_ID() );
		if ( ! self::should_display( $product_id ) ) {
			return $content;
		}
		return $content . self::section_html( $product_id );
	}

	/**
	 * Builds the safety section HTML, fully escaped.
	 */
	public static function section_html( int $product_id ): string {
		$data   = Data::for_product( $product_id );
		$labels = Data::labels();
		$html   = '<section class="jeytech-gpsr-safety"><h2>' . esc_html( self::title() ) . '</h2><dl>';
		foreach ( Data::FIELDS as $field ) {
			if ( '' === $data[ $field ] ) {
				continue;
			}
			$html .= '<dt>' . esc_html( $labels[ $field ] ) . '</dt><dd>' . esc_html( $data[ $field ] ) . '</dd>';
		}
		$html .= '</dl>';
		if ( '' !== $data['warnings'] ) {
			$html .= '<h3>' . esc_html( $labels['warnings'] ) . '</h3><p>' . nl2br( esc_html( $data['warnings'] ) ) . '</p>';
		}
		return $html . '</section>';
	}
}
