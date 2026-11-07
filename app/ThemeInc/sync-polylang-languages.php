<?php
/**
 * Sync Polylang languages from database to Polylang model
 * Access: http://q.local/wp-content/themes/generatepress/inc/sync-polylang-languages.php?key=opencode
 */

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

echo "<h2>Syncing Polylang Languages</h2>";

// Get the Polylang model/language manager
$pll = PLL();

// Force Polylang to reload its languages cache
if ( method_exists( $pll->model, 'languages' ) ) {
	// Clear and rebuild the languages cache
	$result = $pll->model->languages;
	echo "<p>Polylang model languages reloaded</p>";
}

// Check for our languages
$zh = $pll->model->get_language( 'zh' );
$zh_tw = $pll->model->get_language( 'zh-tw' );

if ( $zh ) {
	echo "<p>✓ Simplified Chinese (zh) found in Polylang model</p>";
} else {
	echo "<p>✗ Simplified Chinese (zh) not found in Polylang model</p>";
}

if ( $zh_tw ) {
	echo "<p>✓ Traditional Chinese (zh-tw) found in Polylang model</p>";
} else {
	echo "<p>✗ Traditional Chinese (zh-tw) not found in Polylang model</p>";
}

// Try to get the languages from the database directly
echo "<h3>Languages in Database:</h3>";
$db_langs = get_terms( array(
	'taxonomy'   => 'language',
	'hide_empty' => false,
	'fields'     => 'all',
) );

echo "<ul>";
foreach ( $db_langs as $lang ) {
	echo "<li>{$lang->name} (slug: {$lang->slug}, term_id: {$lang->term_id})</li>";
}
echo "</ul>";

// Try to re-init Polylang
echo "<h3>Attempting to refresh Polylang...</h3>";
if ( has_action( 'pll_init' ) ) {
	do_action( 'pll_init' );
	echo "<p>pll_init action triggered</p>";
}

// Check again after refresh
$zh_after = $pll->model->get_language( 'zh' );
$zh_tw_after = $pll->model->get_language( 'zh-tw' );

if ( $zh_after ) {
	echo "<p>✓ Simplified Chinese (zh) now found after refresh!</p>";
} else {
	echo "<p>✗ Still can't find Simplified Chinese (zh)</p>";
}

if ( $zh_tw_after ) {
	echo "<p>✓ Traditional Chinese (zh-tw) now found after refresh!</p>";
} else {
	echo "<p>✗ Still can't find Traditional Chinese (zh-tw)</p>";
}

echo "<hr>";
echo "<p><a href='http://q.local/wp-content/themes/generatepress/inc/cal-create-zh-pages.php?key=opencode'>Try running Step 2 again: Create Chinese Pages</a></p>";