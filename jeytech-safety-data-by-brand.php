<?php
/**
 * Plugin Name:          JeyTech Safety Data by Brand for WooCommerce
 * Description:          Add GPSR manufacturer and safety information to every product — set it once per brand, audit what's missing.
 * Version:              1.0.0
 * Requires at least:    6.5
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * Author:               JeyTech
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          jeytech-safety-data-by-brand
 * WC requires at least: 9.6
 * WC tested up to:      11.1
 *
 * @package JeyTech\SafetyDataByBrand
 */

defined( 'ABSPATH' ) || exit;

define( 'JEYTECH_SDBB_VERSION', '1.0.0' );
define( 'JEYTECH_SDBB_FILE', __FILE__ );
define( 'JEYTECH_SDBB_PATH', plugin_dir_path( __FILE__ ) );

require_once JEYTECH_SDBB_PATH . 'src/Core/Autoloader.php';
\JeyTech\SafetyDataByBrand\Core\Autoloader::register( 'JeyTech\\SafetyDataByBrand\\', JEYTECH_SDBB_PATH . 'src/' );

add_action( 'before_woocommerce_init', array( \JeyTech\SafetyDataByBrand\Core\Compat::class, 'declare_compatibility' ) );

add_action( 'plugins_loaded', array( \JeyTech\SafetyDataByBrand\Plugin::class, 'boot' ) );
