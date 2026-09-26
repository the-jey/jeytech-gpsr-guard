<?php
/**
 * Plugin Name:          JeyTech GPSR Guard for WooCommerce
 * Description:          Add GPSR manufacturer and safety information to every product — set it once per brand, audit what's missing.
 * Version:              1.0.0
 * Requires at least:    6.5
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * Author:               JeyTech
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          jeytech-gpsr-guard
 * WC requires at least: 9.6
 * WC tested up to:      11.1
 *
 * @package JeyTech\GpsrGuard
 */

defined( 'ABSPATH' ) || exit;

define( 'JEYTECH_GPSR_VERSION', '1.0.0' );
define( 'JEYTECH_GPSR_FILE', __FILE__ );
define( 'JEYTECH_GPSR_PATH', plugin_dir_path( __FILE__ ) );

require_once JEYTECH_GPSR_PATH . 'src/Core/Autoloader.php';
\JeyTech\GpsrGuard\Core\Autoloader::register( 'JeyTech\\GpsrGuard\\', JEYTECH_GPSR_PATH . 'src/' );

add_action( 'before_woocommerce_init', array( \JeyTech\GpsrGuard\Core\Compat::class, 'declare_compatibility' ) );

add_action( 'plugins_loaded', array( \JeyTech\GpsrGuard\Plugin::class, 'boot' ) );
