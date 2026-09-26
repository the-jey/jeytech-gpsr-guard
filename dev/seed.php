<?php
/**
 * Demo data for `npm run dev` (runs once per Playground site).
 *
 * @package JeyTech\GpsrGuard
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/lib.php';

if ( get_option( 'jeytech_gpsr_dev_seeded' ) ) {
	WP_CLI::log( 'Demo data already there.' );
	return;
}

jeytech_gpsr_dev_setup_store();

$acme = jeytech_gpsr_dev_brand( 'Acme', array(
	'manufacturer'   => 'Acme Corp',
	'address'        => '1 rue de l’Usine, 75011 Paris, France',
	'email'          => 'contact@acme.example',
	'eu_responsible' => 'Acme EU GmbH, Berlin, eu@acme.example',
) );

jeytech_gpsr_dev_simple( 'Boutique Lamp', $acme, array( 'warnings' => 'Use indoors only. Keep away from water.' ) );
jeytech_gpsr_dev_simple( 'Workshop Chair', $acme, array( 'manufacturer' => 'Atelier Nord' ) );
jeytech_gpsr_dev_simple( 'Mystery Mug' );

update_option( 'jeytech_gpsr_dev_seeded', 1 );
WP_CLI::success( 'Demo data created: 1 brand, 2 documented products, 1 without GPSR data.' );
