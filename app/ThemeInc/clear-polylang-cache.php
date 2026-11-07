<?php
/**
 * Clear Polylang cache to force language reload
 * Access: http://q.local/wp-content/themes/generatepress/inc/clear-polylang-cache.php?key=opencode
 */

$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { 
	http_response_code( 403 ); 
	echo 'forbidden'; 
	exit; 
}

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

echo "<h2>Clearing Polylang Cache</h2>";

// Clear WordPress object cache
wp_cache_flush();
echo "<p>✓ WordPress object cache cleared</p>";

// Clear WordPress transients for Polylang
delete_transient( 'pll_languages' );
echo "<p>✓ Polylang language transient cleared</p>";

// Delete any Polylang-related transients
global $wpdb;
$deleted = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%transient%pll%'" );
echo "<p>✓ Deleted $deleted Polylang transient options</p>";

// Reset Polylang options if needed
$pll_options = get_option( 'polylang', array() );
echo "<p>Current Polylang options keys: " . implode( ', ', array_keys( $pll_options ) ) . "</p>";

// Now check if Polylang can find the languages
if ( function_exists( 'PLL' ) && PLL() ) {
	echo "<h3>Language detection after cache clear:</h3>";
	$pll = PLL();
	
	// Try to get languages using different methods
	$zh_found = $pll->model->get_language( 'zh' );
	$zh_tw_found = $pll->model->get_language( 'zh-tw' );
	
	echo "<p>Simplified Chinese (zh): " . ( $zh_found ? "✓ FOUND" : "✗ NOT FOUND" ) . "</p>";
	echo "<p>Traditional Chinese (zh-tw): " . ( $zh_tw_found ? "✓ FOUND" : "✗ NOT FOUND" ) . "</p>";
	
	// Also check using get_terms
	echo "<h3>Languages in database (via get_terms):</h3>";
	$db_langs = get_terms( array(
		'taxonomy'   => 'language',
		'hide_empty' => false,
	) );
	
	echo "<ul>";
	foreach ( $db_langs as $lang ) {
		echo "<li>{$lang->name} (slug: {$lang->slug})</li>";
	}
	echo "</ul>";
}

echo "<hr>";
echo "<p><a href='http://q.local/wp-content/themes/generatepress/inc/cal-create-zh-pages.php?key=opencode'>Try Step 2 again: Create Chinese Pages</a></p>";