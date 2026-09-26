<?php
use JeyTech\GpsrGuard\Admin\AuditPage;
use JeyTech\GpsrGuard\BrandFields;
use JeyTech\GpsrGuard\Data;
use JeyTech\GpsrGuard\ProductFields;
use JeyTech\GpsrGuard\Frontend\Safety;
use JeyTech\GpsrGuard\Settings;

defined( 'ABSPATH' ) || exit;
require_once WP_PLUGIN_DIR . '/jeytech-gpsr-guard/dev/lib.php';

$lines    = array();
$failures = 0;
$check    = static function ( string $name, bool $passed ) use ( &$lines, &$failures ): void {
	$lines[] = ( $passed ? 'PASS  ' : 'FAIL  ' ) . $name;
	if ( ! $passed ) {
		++$failures;
	}
};

jeytech_gpsr_dev_setup_store();

$check( 'Brands taxonomy registered by WooCommerce', taxonomy_exists( 'product_brand' ) );

$brand = jeytech_gpsr_dev_brand( 'Acme', array(
	'manufacturer'   => 'Acme Corp',
	'address'        => '1 rue de l’Usine, Paris',
	'email'          => 'contact@acme.example',
	'eu_responsible' => 'Acme EU, Berlin',
) );
$check( 'Brand meta roundtrip', 'Acme Corp' === get_term_meta( $brand, 'jeytech_gpsr_manufacturer', true ) );

$p1 = jeytech_gpsr_dev_simple( 'Inherited Lamp', $brand );
$data = Data::for_product( $p1 );
$check( 'Product inherits brand data', 'Acme Corp' === $data['manufacturer'] && 'contact@acme.example' === $data['email'] && $brand === $data['inherited_from'] );

$p2 = jeytech_gpsr_dev_simple( 'Override Chair', $brand, array( 'manufacturer' => 'Atelier Nord' ) );
$data2 = Data::for_product( $p2 );
$check( 'Product override wins over brand', 'Atelier Nord' === $data2['manufacturer'] && '1 rue de l’Usine, Paris' === $data2['address'] );

$p3 = jeytech_gpsr_dev_simple( 'Warned Kettle', $brand, array( 'warnings' => "Hot surface.\nKeep away from children." ) );
$check( 'Warnings live on the product', "Hot surface.\nKeep away from children." === Data::for_product( $p3 )['warnings'] );

$p4 = jeytech_gpsr_dev_simple( 'Lonely Mug' );
$check( 'Missing fields listed without brand', array( 'manufacturer', 'address', 'email', 'eu_responsible' ) === Data::missing_for( $p4 ) );
$check( 'Complete product has no missing field', array() === Data::missing_for( $p1 ) );

$check( 'Email sanitized, text capped',
	'' === Data::sanitize_field( 'email', 'not-an-email' )
	&& 'a@b.co' === Data::sanitize_field( 'email', 'a@b.co' )
	&& 200 === strlen( Data::sanitize_field( 'manufacturer', str_repeat( 'x', 500 ) ) ) );

$with_post = static function ( int $product_id, callable $run ) {
	global $post;
	$post = get_post( $product_id );
	setup_postdata( $post );
	try {
		return $run();
	} finally {
		wp_reset_postdata();
	}
};
$check( 'Tab present with data', $with_post( $p1, static function (): bool {
	$tabs = apply_filters( 'woocommerce_product_tabs', array() );
	return isset( $tabs['jeytech_gpsr'] ) && 'Product Safety' === $tabs['jeytech_gpsr']['title'];
} ) );
$check( 'Tab absent without data', $with_post( $p4, static function (): bool {
	return ! isset( apply_filters( 'woocommerce_product_tabs', array() )['jeytech_gpsr'] );
} ) );

update_option( Settings::OPTION, array( 'enabled' => false ), false );
$check( 'Disabled hides the tab', $with_post( $p1, static function (): bool {
	return ! isset( apply_filters( 'woocommerce_product_tabs', array() )['jeytech_gpsr'] );
} ) );
update_option( Settings::OPTION, array( 'enabled' => true ), false );

update_post_meta( $p3, '_jeytech_gpsr_warnings', 'Careful <b>now</b>' );
$html = Safety::section_html( $p3 );
$check( 'Section renders data escaped', false !== strpos( $html, 'Acme Corp' ) && false !== strpos( $html, 'Careful &lt;b&gt;now&lt;/b&gt;' ) && false === strpos( $html, '<b>now</b>' ) );

add_filter( 'jeytech_gpsr_should_display', '__return_false' );
$check( 'Display filter hides the tab (Pro contract)', $with_post( $p1, static function (): bool {
	return ! isset( apply_filters( 'woocommerce_product_tabs', array() )['jeytech_gpsr'] );
} ) );
remove_filter( 'jeytech_gpsr_should_display', '__return_false' );

add_filter( 'jeytech_gpsr_data', static function ( array $data ): array {
	$data['manufacturer'] = 'Filtered Inc';
	return $data;
} );
$check( 'Data filter overrides fields (Pro contract)', 'Filtered Inc' === Data::for_product( $p1 )['manufacturer'] );
remove_all_filters( 'jeytech_gpsr_data' );

$rows = array();
foreach ( AuditPage::rows()['rows'] as $row ) {
	$rows[ $row['product_id'] ] = $row;
}
$check( 'Audit flags missing and complete products',
	isset( $rows[ $p4 ] ) && 4 === count( $rows[ $p4 ]['missing'] )
	&& isset( $rows[ $p1 ] ) && array() === $rows[ $p1 ]['missing'] );

wp_set_current_user( 1 );
$_POST['jeytech_gpsr_manufacturer'] = 'Saved Corp';
BrandFields::save( $brand );
$check( 'Brand save handler stores sanitized fields', 'Saved Corp' === get_term_meta( $brand, 'jeytech_gpsr_manufacturer', true ) );
update_term_meta( $brand, 'jeytech_gpsr_manufacturer', 'Acme Corp' );
$_POST = array();

$p5 = jeytech_gpsr_dev_simple( 'Saved Table' );
$_POST = array( '_jeytech_gpsr_warnings' => 'Keep <i>dry</i>' );
ProductFields::save( $p5 );
$check( 'Product save handler stores sanitized fields', 'Keep dry' === get_post_meta( $p5, '_jeytech_gpsr_warnings', true ) );
$_POST = array();

$store  = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? 'hpos' : 'posts';
$report = sprintf( "GPSR Guard — scénario (%s, PHP %s, WP %s, WC %s)\n", strtoupper( $store ), PHP_VERSION, get_bloginfo( 'version' ), WC_VERSION )
	. implode( "\n", $lines ) . "\n"
	. ( $failures ? "ÉCHEC : $failures vérification(s)" : 'OK : ' . count( $lines ) . ' vérifications' ) . "\n";

file_put_contents( __DIR__ . '/.test-output-' . $store . '.txt', $report ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
WP_CLI::log( $report );

if ( $failures ) {
	WP_CLI::error( 'Scénario en échec.' );
}
WP_CLI::success( 'Scénario OK.' );
