<?php
/**
 * Uninstall: removes the settings. Merchant content (brand and product GPSR
 * fields) is kept: it belongs to the catalog, like descriptions.
 *
 * @package JeyTech\SafetyDataByBrand
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'jeytech_sdbb_settings' );
delete_option( 'jeytech_sdbb_dev_seeded' );
