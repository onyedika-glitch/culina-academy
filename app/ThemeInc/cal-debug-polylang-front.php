<?php

$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) {
	http_response_code( 403 );
	echo 'forbidden';
	exit;
}

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! function_exists( 'PLL' ) || ! PLL() ) {
	die( 'Polylang not active' );
}

$front_id = (int) get_option( 'page_on_front' );
$show_on_front = (string) get_option( 'show_on_front' );

echo '<pre style="font-family:monospace;padding:20px;font-size:13px;">';
echo "show_on_front: {$show_on_front}\n";
echo "page_on_front: {$front_id}\n";

if ( $front_id ) {
	$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $front_id ) : '';
	echo "front page lang: {$lang}\n";
	$trans = function_exists( 'pll_get_post_translations' ) ? pll_get_post_translations( $front_id ) : array();
	echo "front page translations:\n";
	foreach ( $trans as $l => $id ) {
		echo "  {$l} => {$id} (" . get_permalink( $id ) . ")\n";
	}
}

echo "\nPolylang languages:\n";
foreach ( PLL()->model->get_languages_list() as $l ) {
	echo "  {$l->slug} (term_id {$l->term_id}, locale {$l->locale})\n";
}
echo '</pre>';

