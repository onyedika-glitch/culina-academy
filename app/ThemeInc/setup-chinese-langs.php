<?php
/**
 * Add Chinese languages to Polylang using proper WordPress term insertion
 * Access: http://q.local/wp-content/themes/generatepress/inc/setup-chinese-langs.php?key=opencode
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

echo "<h2>Setting up Chinese Languages for Polylang</h2>";

// Polylang stores languages as terms in two taxonomies: 'language' and 'term_language'
// Simplified Chinese (zh_CN with slug 'zh')
$zh_term_data = array(
	'name'        => 'Simplified Chinese',
	'slug'        => 'zh',
	'term_group'  => 0,
	'description' => maybe_serialize( array(
		'locale'   => 'zh_CN',
		'flag_code' => 'cn',
		'rtl'      => 0,
		'active'   => 1,
	) ),
);

// Traditional Chinese (zh_TW with slug 'zh-tw')
$zh_tw_term_data = array(
	'name'        => 'Traditional Chinese',
	'slug'        => 'zh-tw',
	'term_group'  => 0,
	'description' => maybe_serialize( array(
		'locale'   => 'zh_TW',
		'flag_code' => 'tw',
		'rtl'      => 0,
		'active'   => 1,
	) ),
);

$results = array();
$errors = array();

// Try to insert Simplified Chinese in 'language' taxonomy
$zh_existing = get_term_by( 'slug', 'zh', 'language', OBJECT, 'edit' );

if ( ! $zh_existing ) {
	$zh_result = wp_insert_term(
		$zh_term_data['name'],
		'language',
		array(
			'slug'        => $zh_term_data['slug'],
			'description' => $zh_term_data['description'],
			'term_group'  => $zh_term_data['term_group'],
		)
	);
	
	if ( is_wp_error( $zh_result ) ) {
		$errors[] = 'Failed to add Simplified Chinese (zh): ' . $zh_result->get_error_message();
	} elseif ( is_array( $zh_result ) && isset( $zh_result['term_id'] ) ) {
		$zh_term_id = $zh_result['term_id'];
		$results[] = 'Simplified Chinese (zh) added successfully with term ID ' . $zh_term_id;
		
		// Also add to 'term_language' taxonomy (this might be needed for consistency)
		$zh_lang_result = wp_insert_term(
			$zh_term_data['name'],
			'term_language',
			array(
				'slug'        => $zh_term_data['slug'],
				'description' => $zh_term_data['description'],
				'term_group'  => $zh_term_data['term_group'],
			)
		);
		
		if ( ! is_wp_error( $zh_lang_result ) && is_array( $zh_lang_result ) ) {
			$results[] = 'Simplified Chinese also added to term_language taxonomy';
		}
	}
} else {
	$results[] = 'Simplified Chinese (zh) already exists';
}

// Try to insert Traditional Chinese in 'language' taxonomy
$zh_tw_existing = get_term_by( 'slug', 'zh-tw', 'language', OBJECT, 'edit' );

if ( ! $zh_tw_existing ) {
	$zh_tw_result = wp_insert_term(
		$zh_tw_term_data['name'],
		'language',
		array(
			'slug'        => $zh_tw_term_data['slug'],
			'description' => $zh_tw_term_data['description'],
			'term_group'  => $zh_tw_term_data['term_group'],
		)
	);
	
	if ( is_wp_error( $zh_tw_result ) ) {
		$errors[] = 'Failed to add Traditional Chinese (zh-tw): ' . $zh_tw_result->get_error_message();
	} elseif ( is_array( $zh_tw_result ) && isset( $zh_tw_result['term_id'] ) ) {
		$zh_tw_term_id = $zh_tw_result['term_id'];
		$results[] = 'Traditional Chinese (zh-tw) added successfully with term ID ' . $zh_tw_term_id;
		
		// Also add to 'term_language' taxonomy
		$zh_tw_lang_result = wp_insert_term(
			$zh_tw_term_data['name'],
			'term_language',
			array(
				'slug'        => $zh_tw_term_data['slug'],
				'description' => $zh_tw_term_data['description'],
				'term_group'  => $zh_tw_term_data['term_group'],
			)
		);
		
		if ( ! is_wp_error( $zh_tw_lang_result ) && is_array( $zh_tw_lang_result ) ) {
			$results[] = 'Traditional Chinese also added to term_language taxonomy';
		}
	}
} else {
	$results[] = 'Traditional Chinese (zh-tw) already exists';
}

// Output results
if ( ! empty( $results ) ) {
	echo "<p><strong>✓ Success:</strong></p>";
	echo "<ul>";
	foreach ( $results as $msg ) {
		echo "<li>" . esc_html( $msg ) . "</li>";
	}
	echo "</ul>";
}

if ( ! empty( $errors ) ) {
	echo "<p><strong style='color:red;'>✗ Errors:</strong></p>";
	echo "<ul>";
	foreach ( $errors as $msg ) {
		echo "<li style='color:red;'>" . esc_html( $msg ) . "</li>";
	}
	echo "</ul>";
}

echo "<hr>";
echo "<p>Now running the next activation step: creating pages...</p>";
echo "<p><a href='http://q.local/wp-content/themes/generatepress/inc/cal-create-zh-pages.php?key=opencode'>Click here to proceed with step 2 (Create Chinese Pages)</a></p>";