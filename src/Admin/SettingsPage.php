<?php
/**
 * Settings page under WooCommerce.
 *
 * @package JeyTech\GpsrGuard\Admin
 */

namespace JeyTech\GpsrGuard\Admin;

use JeyTech\GpsrGuard\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the settings screen with the JeyTech palette.
 */
final class SettingsPage {

	const SLUG       = 'jeytech-gpsr';
	const CAPABILITY = 'manage_woocommerce';

	private static $page_hook = '';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'admin_init', array( self::class, 'settings' ) );
		add_filter( 'option_page_capability_jeytech_gpsr', array( self::class, 'capability' ) );
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
			__( 'GPSR Guard', 'jeytech-gpsr-guard' ),
			__( 'GPSR safety', 'jeytech-gpsr-guard' ),
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
		wp_enqueue_style( 'jeytech-gpsr-admin', plugins_url( 'assets/admin.css', JEYTECH_GPSR_FILE ), array(), JEYTECH_GPSR_VERSION );
	}

	public static function settings(): void {
		register_setting( 'jeytech_gpsr', Settings::OPTION, array(
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
			<h1><?php esc_html_e( 'GPSR Guard', 'jeytech-gpsr-guard' ); ?></h1>
			<?php settings_errors(); ?>
			<p><?php esc_html_e( 'Set the manufacturer data once per brand: every product inherits it. Overrides and safety warnings live on the product.', 'jeytech-gpsr-guard' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'jeytech_gpsr' ); ?>
				<table class="form-table">
					<tr><th><?php esc_html_e( 'Enable', 'jeytech-gpsr-guard' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[enabled]" value="1" <?php checked( $settings['enabled'] ); ?> /> <?php esc_html_e( 'Show the safety section on product pages', 'jeytech-gpsr-guard' ); ?></label></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=jeytech-gpsr-audit' ) ); ?>"><?php esc_html_e( 'Open the audit: products missing GPSR data', 'jeytech-gpsr-guard' ); ?></a></p>
			<p><em><?php esc_html_e( 'GPSR Guard displays what you enter. It is not legal advice and does not guarantee compliance.', 'jeytech-gpsr-guard' ); ?></em></p>
		</div>
		<?php
	}
}
