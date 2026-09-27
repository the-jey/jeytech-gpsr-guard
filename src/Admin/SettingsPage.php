<?php
/**
 * Settings page under WooCommerce.
 *
 * @package JeyTech\SafetyDataByBrand\Admin
 */

namespace JeyTech\SafetyDataByBrand\Admin;

use JeyTech\SafetyDataByBrand\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the settings screen with the JeyTech palette.
 */
final class SettingsPage {

	const SLUG       = 'jeytech-sdbb';
	const CAPABILITY = 'manage_woocommerce';

	private static $page_hook = '';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'admin_init', array( self::class, 'settings' ) );
		add_filter( 'option_page_capability_jeytech_sdbb', array( self::class, 'capability' ) );
	}

	public static function capability(): string {
		return self::CAPABILITY;
	}

	/**
	 * Submenu under WooCommerce.
	 */
	public static function add_menu(): void {
		self::$page_hook = (string) add_submenu_page(
			'woocommerce',
			__( 'Safety Data by Brand', 'jeytech-safety-data-by-brand' ),
			__( 'GPSR safety', 'jeytech-safety-data-by-brand' ),
			self::CAPABILITY,
			self::SLUG,
			array( self::class, 'render' )
		);
	}

	/**
	 * Load the JeyTech palette only on this plugin's settings page.
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( $hook !== self::$page_hook ) {
			return;
		}
		wp_enqueue_style( 'jeytech-sdbb-admin', plugins_url( 'assets/admin.css', JEYTECH_SDBB_FILE ), array(), JEYTECH_SDBB_VERSION );
	}

	public static function settings(): void {
		register_setting( 'jeytech_sdbb', Settings::OPTION, array(
			'type'              => 'array',
			'sanitize_callback' => array( Settings::class, 'sanitize' ),
			'default'           => Settings::defaults(),
		) );
	}

	public static function render(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		$settings = Settings::get();
		?>
		<div class="wrap jeytech-admin">
			<h1><?php esc_html_e( 'Safety Data by Brand', 'jeytech-safety-data-by-brand' ); ?></h1>
			<?php settings_errors(); ?>
			<p><?php esc_html_e( 'Set the manufacturer data once per brand: every product inherits it. Overrides and safety warnings live on the product.', 'jeytech-safety-data-by-brand' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'jeytech_sdbb' ); ?>
				<table class="form-table">
					<tr><th><?php esc_html_e( 'Enable', 'jeytech-safety-data-by-brand' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[enabled]" value="1" <?php checked( $settings['enabled'] ); ?> /> <?php esc_html_e( 'Show the safety section on product pages', 'jeytech-safety-data-by-brand' ); ?></label></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=jeytech-sdbb-audit' ) ); ?>"><?php esc_html_e( 'Open the audit: products missing GPSR data', 'jeytech-safety-data-by-brand' ); ?></a></p>
			<p><em><?php esc_html_e( 'Safety Data by Brand displays what you enter. It is not legal advice and does not guarantee compliance.', 'jeytech-safety-data-by-brand' ); ?></em></p>
		</div>
		<?php
	}
}
