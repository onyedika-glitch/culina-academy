<?php
/**
 * Complaints Policy page template functionality.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_cp_template_assets' );
function cal_cp_template_assets() {
	if ( ! is_page_template( 'template-complaints-policy.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-cp-template',
		get_template_directory_uri() . '/assets/css/cal-policy-pages.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_cp_template_body_class', 20 );
function cal_cp_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-complaints-policy.php' ) ) {
		return $classes;
	}

	$remove = array(
		'right-sidebar',
		'left-sidebar',
		'no-sidebar',
		'separate-containers',
	);

	foreach ( $remove as $class ) {
		$key = array_search( $class, $classes, true );
		if ( false !== $key ) {
			unset( $classes[ $key ] );
		}
	}

	$classes[] = 'cal-policy-page';
	$classes[] = 'no-sidebar';

	return $classes;
}

add_action( 'dynamic_sidebar_before', 'cal_cp_remove_sidebar', 0 );
function cal_cp_remove_sidebar() {
	if ( is_page_template( 'template-complaints-policy.php' ) ) {
		remove_all_actions( 'generate_sidebars' );
	}
}
