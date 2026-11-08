<?php
/**
 * Fix Chinese language terms to match Polylang structure
 * Access: http://q.local/wp-content/themes/generatepress/inc/fix-chinese-lang-structure.php?key=opencode
 */

$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { 
	http_response_code( 403 ); 
	echo 'forbidden'; 
	exit; 
}

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

global $wpdb;

echo "<h2>Fixing Chinese Language Term Structure</h2>";

// Get the Chinese language terms
$zh_term = get_term_by( 'slug', 'zh', 'language' );
$zh_tw_term = get_term_by( 'slug', 'zh-tw', 'language' );

// Update descriptions with proper structure including 'fallbacks'
$zh_description = array(
	'fallbacks' => array(), // Add fallbacks key
	'locale'    => 'zh_CN',
	'flag_code' => 'cn',
	'rtl'       => 0,
	'active'    => 1,
);

$zh_tw_description = array(
	'fallbacks' => array(), // Add fallbacks key
	'locale'    => 'zh_TW',
	'flag_code' => 'tw',
	'rtl'       => 0,
	'active'    => 1,
);

// Update the terms
$zh_update = wp_update_term(
	$zh_term->term_id,
	'language',
	array(
		'description' => maybe_serialize( $zh_description ),
	)
);

$zh_tw_update = wp_update_term(
	$zh_tw_term->term_id,
	'language',
	array(
		'description' => maybe_serialize( $zh_tw_description ),
	)
);

if ( is_wp_error( $zh_update ) ) {
	echo "<p>✗ Error updating Simplified Chinese: " . $zh_update->get_error_message() . "</p>";
} else {
	echo "<p>✓ Simplified Chinese term updated</p>";
}

if ( is_wp_error( $zh_tw_update ) ) {
	echo "<p>✗ Error updating Traditional Chinese: " . $zh_tw_update->get_error_message() . "</p>";
} else {
	echo "<p>✓ Traditional Chinese term updated</p>";
}

// Also update in term_language taxonomy
$zh_lang_term = get_term_by( 'slug', 'zh', 'term_language' );
$zh_tw_lang_term = get_term_by( 'slug', 'zh-tw', 'term_language' );

if ( $zh_lang_term ) {
	wp_update_term(
		$zh_lang_term->term_id,
		'term_language',
		array(
			'description' => maybe_serialize( $zh_description ),
		)
	);
	echo "<p>✓ Simplified Chinese term_language updated</p>";
}

if ( $zh_tw_lang_term ) {
	wp_update_term(
		$zh_tw_lang_term->term_id,
		'term_language',
		array(
			'description' => maybe_serialize( $zh_tw_description ),
		)
	);
	echo "<p>✓ Traditional Chinese term_language updated</p>";
}

// Clear Polylang cache again
wp_cache_flush();
delete_transient( 'pll_languages' );
echo "<p>✓ Cache cleared</p>";

// Now test if Polylang can find the languages
if ( function_exists( 'PLL' ) && PLL() ) {
	echo "<h3>Testing Polylang language detection after fix:</h3>";
	$pll = PLL();
	
	// Need to reload the Polylang model
	$zh_test = $pll->model->get_language( 'zh' );
	$zh_tw_test = $pll->model->get_language( 'zh-tw' );
	
	echo "<p>Simplified Chinese (zh): " . ( $zh_test ? "✓ FOUND" : "✗ NOT FOUND" ) . "</p>";
	echo "<p>Traditional Chinese (zh-tw): " . ( $zh_tw_test ? "✓ FOUND" : "✗ NOT FOUND" ) . "</p>";
}

echo "<hr>";
echo "<p><a href='http://q.local/wp-content/themes/generatepress/inc/cal-create-zh-pages.php?key=opencode'>Try Step 2: Create Chinese Pages</a></p>";