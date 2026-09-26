<?php
/**
 * Audit page: products missing GPSR data.
 *
 * @package JeyTech\GpsrGuard\Admin
 */

namespace JeyTech\GpsrGuard\Admin;

use JeyTech\GpsrGuard\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Lists published parent products with their GPSR status.
 */
final class AuditPage {

	const SLUG       = 'jeytech-gpsr-audit';
	const CAPABILITY = 'manage_woocommerce';
	const MAX_ROWS   = 1000;

	private static $page_hook = '';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}

	/**
	 * Submenu under WooCommerce.
	 */
	public static function add_menu(): void {
		self::$page_hook = (string) add_submenu_page(
			'woocommerce',
			__( 'GPSR Audit', 'jeytech-gpsr-guard' ),
			__( 'GPSR audit', 'jeytech-gpsr-guard' ),
			self::CAPABILITY,
			self::SLUG,
			array( self::class, 'render' )
		);
	}

	/**
	 * Load the JeyTech palette only on this plugin's audit page.
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( $hook !== self::$page_hook ) {
			return;
		}
		wp_enqueue_style( 'jeytech-gpsr-admin', plugins_url( 'assets/admin.css', JEYTECH_GPSR_FILE ), array(), JEYTECH_GPSR_VERSION );
	}

	/**
	 * Published parent products with their missing fields. Capped for very
	 * large catalogs; the cap is disclosed on the page.
	 *
	 * @return array{rows: array<int, array<string, mixed>>, capped: bool}
	 */
	public static function rows(): array {
		$ids = get_posts( array(
			'post_type'     => 'product',
			'post_status'   => 'publish',
			'post_parent'   => 0,
			'fields'        => 'ids',
			'numberposts'   => self::MAX_ROWS,
			'no_found_rows' => true,
		) );
		$rows = array();
		foreach ( is_array( $ids ) ? $ids : array() as $product_id ) {
			$product_id = (int) $product_id;
			$product    = wc_get_product( $product_id );
			$rows[]     = array(
				'product_id' => $product_id,
				'name'       => $product ? $product->get_name() : '#' . $product_id,
				'missing'    => Data::missing_for( $product_id ),
				'warnings'   => '' !== Data::for_product( $product_id )['warnings'],
			);
		}
		return array( 'rows' => $rows, 'capped' => count( $rows ) >= self::MAX_ROWS );
	}

	public static function render(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filter, sanitized, no state change.
		$filter = isset( $_GET['gpsr_status'] ) ? sanitize_key( (string) $_GET['gpsr_status'] ) : 'missing';
		if ( ! in_array( $filter, array( 'missing', 'all' ), true ) ) {
			$filter = 'missing';
		}
		$result = self::rows();
		$rows   = $result['rows'];
		if ( 'missing' === $filter ) {
			$rows = array_values( array_filter( $rows, static function ( array $row ): bool {
				return (bool) $row['missing'];
			} ) );
		}
		$labels = Data::labels();
		$base   = admin_url( 'admin.php?page=' . self::SLUG );
		?>
		<div class="wrap jeytech-admin">
			<h1><?php esc_html_e( 'GPSR Audit', 'jeytech-gpsr-guard' ); ?></h1>
			<p>
				<a href="<?php echo esc_url( add_query_arg( 'gpsr_status', 'missing', $base ) ); ?>"><?php esc_html_e( 'Missing data', 'jeytech-gpsr-guard' ); ?></a>
				| <a href="<?php echo esc_url( add_query_arg( 'gpsr_status', 'all', $base ) ); ?>"><?php esc_html_e( 'All products', 'jeytech-gpsr-guard' ); ?></a>
			</p>
			<?php if ( $result['capped'] ) : ?>
				<p><em><?php esc_html_e( 'Large catalog: only the first 1,000 products are listed.', 'jeytech-gpsr-guard' ); ?></em></p>
			<?php endif; ?>
			<table class="widefat striped"><thead><tr>
				<th><?php esc_html_e( 'Product', 'jeytech-gpsr-guard' ); ?></th>
				<th><?php esc_html_e( 'Missing fields', 'jeytech-gpsr-guard' ); ?></th>
				<th><?php esc_html_e( 'Warnings', 'jeytech-gpsr-guard' ); ?></th>
			</tr></thead><tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="3"><?php esc_html_e( 'Nothing to show: every listed product has its GPSR data.', 'jeytech-gpsr-guard' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( get_edit_post_link( $row['product_id'] ) ?: '#' ); ?>"><?php echo esc_html( $row['name'] ); ?></a></td>
					<td>
						<?php
						if ( ! $row['missing'] ) {
							esc_html_e( 'Complete', 'jeytech-gpsr-guard' );
						} else {
							echo esc_html( implode( ', ', array_map( static function ( string $field ) use ( $labels ): string {
								return $labels[ $field ] ?? $field;
							}, $row['missing'] ) ) );
						}
						?>
					</td>
					<td><?php echo $row['warnings'] ? esc_html__( 'Set', 'jeytech-gpsr-guard' ) : '—'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody></table>
		</div>
		<?php
	}
}
