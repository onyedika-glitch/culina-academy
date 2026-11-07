<?php
/**
 * WP-CLI eval-file: Translate Polylang strings (Header & Footer options).
 *
 * Usage:
 *   php wp-cli.phar --path=PATH eval-file inc/cal-polylang-translate.php list
 *   php wp-cli.phar --path=PATH eval-file inc/cal-polylang-translate.php
 *
 * Fill $translations below with your translated text, then run.
 */

if ( ! false && defined( 'ABSPATH' ) && ! defined( 'WP_CLI' ) ) {
	exit;
}

if ( ! class_exists( 'PLL_Admin_Strings' ) ) {
	echo "Polylang not active.\n";
	exit;
}

$all_strings = PLL_Admin_Strings::get_strings();

// === LIST MODE ===
if ( isset( $argv[1] ) && 'list' === $argv[1] ) {
	echo "=== Registered Polylang Strings ===\n\n";
	foreach ( $all_strings as $s ) {
		$m = ! empty( $s['multiline'] ) ? '[M]' : '   ';
		printf( "%-50s %-25s %s %s\n", $s['name'], "({$s['context']})", $m, substr( str_replace( "\n", '\\n', $s['string'] ), 0, 80 ) );
	}
	echo "\nTotal: " . count( $all_strings ) . " strings\n";
	exit;
}

// === TRANSLATE MODE ===
// Fill in your translations. Run with "list" first to see all string names.
$translations = array(
	'cal_footer_cta_title' => array(
		'fr' => 'Devenez un chef professionnel',
		'it' => 'Diventa uno chef professionista',
		'es' => 'Conviértete en chef profesional',
	),
	'cal_footer_gallery_heading' => array(
		'fr' => 'Galerie',
		'it' => 'Galleria',
		'es' => 'Galería',
	),
	'cal_footer_about_heading' => array(
		'fr' => 'Culina Academy Your City',
		'it' => 'Culina Academy Your City',
		'es' => 'Culina Academy Your City',
	),
	'cal_footer_about' => array(
		'fr' => "Culina Academy propose une formation professionnelle pratique pour les chefs, les pâtissiers et les directeurs de restaurant. Prenez rendez-vous avec l’un de nos conseillers pour plus d’informations.",
		'it' => 'Culina Academy offre una formazione professionale pratica per chef, pasticceri e direttori di ristorante. Prenota un appuntamento con uno dei nostri consulenti per maggiori informazioni.',
		'es' => 'Culina Academy ofrece formación profesional práctica para chefs, pasteleros y directores de restaurante. Reserve una cita con uno de nuestros asesores para obtener más información.',
	),
	'cal_footer_contact_heading' => array(
		'fr' => 'Contactez-nous',
		'it' => 'Contattaci',
		'es' => 'Contáctenos',
	),
	'cal_footer_policies_heading' => array(
		'fr' => 'Nos Politiques',
		'it' => 'Le Nostre Politiche',
		'es' => 'Nuestras Políticas',
	),
	'cal_footer_bottom_line' => array(
		'fr' => "Culina Academy est une école culinaire professionnelle qui propose une formation pratique de chef à Your City.",
		'it' => "Culina Academy è una scuola di cucina professionale che offre una formazione pratica per chef a Your City.",
		'es' => "Culina Academy es una escuela culinaria profesional que ofrece formación práctica para chefs en Your City.",
	),
);

$string_map = array();
foreach ( $all_strings as $s ) {
	$string_map[ $s['name'] ] = $s;
}

$applied = 0;
$lang_codes = array( 'fr', 'it', 'es' );

foreach ( $translations as $name => $langs ) {
	if ( ! isset( $string_map[ $name ] ) ) {
		echo "  SKIP '$name': not found\n";
		continue;
	}
	$original = $string_map[ $name ]['string'];

	foreach ( $lang_codes as $code ) {
		$translation = isset( $langs[ $code ] ) ? $langs[ $code ] : '';
		if ( '' === $translation ) {
			continue;
		}

		$lang = PLL()->model->get_language( $code );
		if ( ! $lang ) {
			echo "  SKIP '$code': language not found\n";
			continue;
		}

		$mo = new PLL_MO();
		$mo->import_from_db( $lang );
		$mo->add_entry( $mo->make_entry( $original, $translation ) );
		$mo->export_to_db( $lang );
		$applied++;
	}
}

do_action( 'pll_save_strings_translations' );

echo "Applied $applied string translations.\n";
echo "Verify at WP Admin > Polylang > String Translations.\n";
