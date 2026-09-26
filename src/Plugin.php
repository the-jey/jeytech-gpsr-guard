<?php
/**
 * Plugin bootstrap: wires every hook.
 *
 * @package JeyTech\GpsrGuard
 */

namespace JeyTech\GpsrGuard;

use JeyTech\GpsrGuard\Admin\AuditPage;
use JeyTech\GpsrGuard\Admin\SettingsPage;
use JeyTech\GpsrGuard\Core\Requirements;
use JeyTech\GpsrGuard\Frontend\Safety;

defined( 'ABSPATH' ) || exit;

/**
 * Entry point, called on `plugins_loaded`.
 */
final class Plugin {

	/**
	 * Registers hooks once WooCommerce is known to be available.
	 */
	public static function boot(): void {
		if ( ! Requirements::met() ) {
			add_action( 'admin_notices', array( Requirements::class, 'notice' ) );
			return;
		}

		BrandFields::register();
		ProductFields::register();
		Safety::register();

		if ( is_admin() ) {
			SettingsPage::register();
			AuditPage::register();
		}
	}
}
