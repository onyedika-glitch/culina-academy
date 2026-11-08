<?php
/**
 * Analyze Polylang data structure
 * Access: http://q.local/wp-content/themes/generatepress/inc/analyze-polylang-structure.php?key=opencode
 */

$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { 
	http_response_code( 403 ); 
	echo 'forbidden'; 
	exit; 
}

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

echo "<h2>Polylang Data Structure Analysis</h2>";

global $wpdb;

// Check the language terms and their descriptions
echo "<h3>Language Terms Details:</h3>";
$langs = $wpdb->get_results( "
	SELECT t.*, tt.term_taxonomy_id, tt.taxonomy, tt.description
	FROM {$wpdb->terms} t
	LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
	WHERE tt.taxonomy = 'language'
	ORDER BY t.term_id
" );

echo "<pre>";
foreach ( $langs as $lang ) {
	echo "Term: {$lang->name} (ID: {$lang->term_id}, slug: {$lang->slug})\n";
	echo "  Description: {$lang->description}\n";
	echo "  Term Group: {$lang->term_group}\n";
	echo "  Taxonomy: {$lang->taxonomy}\n";
	echo "\n";
}
echo "</pre>";

// Check Polylang options
echo "<h3>Polylang Options:</h3>";
$pll_options = get_option( 'polylang' );
echo "<pre>";
echo json_encode( $pll_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
echo "</pre>";

// Check if there's a 'languages' option
$pll_languages_opt = get_option( 'pll_languages' );
echo "<h3>pll_languages Option:</h3>";
if ( $pll_languages_opt ) {
	echo "<pre>";
	echo json_encode( $pll_languages_opt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	echo "</pre>";
} else {
	echo "<p>Not set</p>";
}

// Check term_meta for language terms
echo "<h3>Language Term Meta:</h3>";
$term_meta = $wpdb->get_results( "
	SELECT tm.*, t.term_id, t.name
	FROM {$wpdb->termmeta} tm
	LEFT JOIN {$wpdb->terms} t ON t.term_id = tm.term_id
	WHERE tm.meta_key LIKE '%lang%' OR t.term_id IN (
		SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'language'
	)
	ORDER BY t.term_id
" );

if ( $term_meta ) {
	echo "<pre>";
	foreach ( $term_meta as $meta ) {
		echo "Term: {$meta->name} (ID: {$meta->term_id})\n";
		echo "  Meta Key: {$meta->meta_key}\n";
		echo "  Meta Value: {$meta->meta_value}\n";
	}
	echo "</pre>";
} else {
	echo "<p>No term meta found</p>";
}

echo "<hr>";
echo "<p><a href='http://q.local/wp-content/themes/generatepress/inc/cal-create-zh-pages.php?key=opencode'>Try Step 2: Create Chinese Pages</a></p>";