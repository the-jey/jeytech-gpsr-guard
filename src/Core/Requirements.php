<?php
/**
 * Runtime requirements check.
 *
 * @package JeyTech\GpsrGuard
 */

namespace JeyTech\GpsrGuard\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Makes sure WooCommerce is loaded and recent enough; never triggers a fatal error.
 */
final class Requirements {

	const MIN_WC_VERSION = '9.6';

	/**
	 * Whether the plugin can run.
	 */
	public static function met(): bool {
		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, self::MIN_WC_VERSION, '>=' );
	}

	/**
	 * Admin notice shown when requirements are not met.
	 */
	public static function notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: minimum WooCommerce version. */
					__( 'JeyTech GPSR Guard for WooCommerce needs WooCommerce %s or later to run.', 'jeytech-gpsr-guard' ),
					self::MIN_WC_VERSION
				)
			)
		);
	}
}
