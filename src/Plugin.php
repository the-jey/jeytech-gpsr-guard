<?php
/**
 * Plugin bootstrap: wires every hook.
 *
 * @package JeyTech\SafetyDataByBrand
 */

namespace JeyTech\SafetyDataByBrand;

use JeyTech\SafetyDataByBrand\Admin\AuditPage;
use JeyTech\SafetyDataByBrand\Admin\SettingsPage;
use JeyTech\SafetyDataByBrand\Core\Requirements;
use JeyTech\SafetyDataByBrand\Frontend\Safety;

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
