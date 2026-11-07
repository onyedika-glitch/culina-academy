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
		'fr' => "Culina Academy Your City a été fondée en 2007 et depuis, elle mène la formation professionnelle pour chefs, pâtissiers et directeurs de restaurant.",
		'it' => 'Culina Academy è stata fondata nel 2007 e da allora guida la formazione professionale per chef, pasticceri e direttori di ristorante.',
		'es' => 'Culina Academy fue fundada en 2007 y desde entonces lidera la formación profesional para chefs, pasteleros y directores de restaurantes.',
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
		'fr' => "La Culina Academy est largement considérée comme la meilleure école de cuisine au monde.",
		'it' => "La Culina Academy è ampiamente considerata la migliore scuola di cucina al mondo.",
		'es' => "La Culina Academy es ampliamente considerada la mejor escuela de cocina del mundo.",
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
