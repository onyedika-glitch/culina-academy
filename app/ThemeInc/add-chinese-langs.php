<?php
/**
 * Quick script to add Simplified and Traditional Chinese languages to Polylang
 * Access: http://q.local/wp-content/themes/generatepress/inc/add-chinese-langs.php?key=opencode
 */

$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { 
	http_response_code( 403 ); 
	echo 'forbidden'; 
	exit; 
}

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

global $wpdb;

if ( ! function_exists( 'PLL' ) || ! PLL() ) {
	die( 'Polylang not active' );
}

// Check if languages already exist
$zh_exists = $wpdb->get_row( 
	"SELECT * FROM {$wpdb->prefix}pll_languages WHERE slug = 'zh'" 
);
$zh_tw_exists = $wpdb->get_row( 
	"SELECT * FROM {$wpdb->prefix}pll_languages WHERE slug = 'zh-tw'" 
);

$errors = array();
$results = array();

// Add Simplified Chinese if it doesn't exist
if ( ! $zh_exists ) {
	$inserted = $wpdb->insert(
		"{$wpdb->prefix}pll_languages",
		array(
			'name'   => 'Simplified Chinese',
			'slug'   => 'zh',
			'locale' => 'zh_CN',
			'is_default' => 0,
			'term_group' => 0,
		),
		array( '%s', '%s', '%s', '%d', '%d' )
	);
	
	if ( $inserted ) {
		$results[] = 'Simplified Chinese (zh) added successfully';
	} else {
		$errors[] = 'Failed to add Simplified Chinese: ' . $wpdb->last_error;
	}
} else {
	$results[] = 'Simplified Chinese (zh) already exists';
}

// Add Traditional Chinese if it doesn't exist
if ( ! $zh_tw_exists ) {
	$inserted = $wpdb->insert(
		"{$wpdb->prefix}pll_languages",
		array(
			'name'   => 'Traditional Chinese',
			'slug'   => 'zh-tw',
			'locale' => 'zh_TW',
			'is_default' => 0,
			'term_group' => 0,
		),
		array( '%s', '%s', '%s', '%d', '%d' )
	);
	
	if ( $inserted ) {
		$results[] = 'Traditional Chinese (zh-tw) added successfully';
	} else {
		$errors[] = 'Failed to add Traditional Chinese: ' . $wpdb->last_error;
	}
} else {
	$results[] = 'Traditional Chinese (zh-tw) already exists';
}

// Output results
echo "<h2>Language Setup Results</h2>";
if ( ! empty( $results ) ) {
	echo "<p><strong>Success:</strong></p>";
	echo "<ul>";
	foreach ( $results as $msg ) {
		echo "<li>" . esc_html( $msg ) . "</li>";
	}
	echo "</ul>";
}

if ( ! empty( $errors ) ) {
	echo "<p><strong style='color:red;'>Errors:</strong></p>";
	echo "<ul>";
	foreach ( $errors as $msg ) {
		echo "<li style='color:red;'>" . esc_html( $msg ) . "</li>";
	}
	echo "</ul>";
}

echo "<p><a href='http://q.local/wp-admin/admin.php?page=mlang'>Back to Languages page</a></p>";
