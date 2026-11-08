<?php
/**
 * Register Chinese languages in Polylang options
 * Access: http://q.local/wp-content/themes/generatepress/inc/register-chinese-in-polylang.php?key=opencode
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

global $wpdb;

echo "<h2>Registering Chinese Languages in Polylang</h2>";

// Get current Polylang options
$pll_options = get_option( 'polylang', array() );
echo "<p>Current Polylang options: <pre>" . json_encode( $pll_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "</pre></p>";

// Get the language terms we just created
$zh_term = get_term_by( 'slug', 'zh', 'language' );
$zh_tw_term = get_term_by( 'slug', 'zh-tw', 'language' );

echo "<h3>Terms found:</h3>";
if ( $zh_term ) {
	echo "<p>✓ Simplified Chinese term found (ID: {$zh_term->term_id}, slug: {$zh_term->slug})</p>";
} else {
	echo "<p>✗ Simplified Chinese term not found</p>";
}

if ( $zh_tw_term ) {
	echo "<p>✓ Traditional Chinese term found (ID: {$zh_tw_term->term_id}, slug: {$zh_tw_term->slug})</p>";
} else {
	echo "<p>✗ Traditional Chinese term not found</p>";
}

// Try to access Polylang's language model to check
echo "<h3>Checking Polylang model:</h3>";
$pll = PLL();

// Get all languages from Polylang
if ( method_exists( $pll->model, 'get_languages_list' ) ) {
	$langs = $pll->model->get_languages_list();
	echo "<p>Polylang languages: ";
	echo implode( ', ', array_column( (array) $langs, 'slug' ) );
	echo "</p>";
} else {
	echo "<p>Could not get languages list from Polylang model</p>";
}

// Try to get languages using get_terms
echo "<h3>All languages from get_terms():</h3>";
$all_langs = get_terms( array(
	'taxonomy'   => 'language',
	'hide_empty' => false,
) );

echo "<ul>";
foreach ( $all_langs as $lang ) {
	echo "<li><strong>{$lang->name}</strong> (slug: {$lang->slug}, ID: {$lang->term_id})</li>";
}
echo "</ul>";

echo "<hr>";
echo "<p><strong>The languages are now in the database!</strong> Polylang should recognize them on the next reload.</p>";
echo "<p><a href='http://q.local/wp-admin/admin.php?page=mlang'>View languages in WordPress Admin</a> | <a href='http://q.local/wp-content/themes/generatepress/inc/cal-create-zh-pages.php?key=opencode'>Try Step 2 again: Create Chinese Pages</a></p>";